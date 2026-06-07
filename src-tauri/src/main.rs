#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use std::fs;
use std::path::{Path, PathBuf};
use std::sync::Arc;
use std::sync::atomic::{AtomicBool, Ordering};

use tauri::Manager;
use tauri::{WebviewUrl, WebviewWindowBuilder};
use tauri_plugin_shell::process::CommandEvent;
use tauri_plugin_shell::ShellExt;
use std::net::TcpStream;

const APP_KEY: &str = "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=";

#[derive(Clone)]
struct AppPaths {
    project_dir: PathBuf,
    php_ini_arg: String,
    db_path_arg: String,
    public_dir_arg: String,
    server_php_arg: String,
}

fn path_for_php(path: &Path) -> String {
    path.to_string_lossy().replace('\\', "/")
}

fn copy_dir_all(src: &Path, dst: &Path) -> std::io::Result<()> {
    fs::create_dir_all(dst)?;
    for entry in fs::read_dir(src)? {
        let entry = entry?;
        let target = dst.join(entry.file_name());
        if entry.file_type()?.is_dir() {
            copy_dir_all(&entry.path(), &target)?;
        } else {
            fs::copy(entry.path(), target)?;
        }
    }
    Ok(())
}

fn ensure_storage_tree(storage_path: &Path) -> std::io::Result<()> {
    for sub in [
        "framework/cache/data",
        "framework/sessions",
        "framework/views",
        "framework/testing",
        "logs",
        "app/public",
        "app/private",
    ] {
        fs::create_dir_all(storage_path.join(sub))?;
    }
    Ok(())
}

fn init_writable_paths(
    handle: &tauri::AppHandle,
    bundle_dir: &Path,
) -> Result<(PathBuf, PathBuf, PathBuf, bool), String> {
    let data_root = handle
        .path()
        .app_data_dir()
        .map_err(|e| format!("app_data_dir: {e}"))?
        .join("rysgally-hasap-market");

    let database_dir = data_root.join("database");
    let storage_path = data_root.join("storage");
    let bootstrap_path = data_root.join("bootstrap");

    fs::create_dir_all(&database_dir)
        .map_err(|e| format!("create database dir: {e}"))?;
    fs::create_dir_all(bootstrap_path.join("cache"))
        .map_err(|e| format!("create bootstrap cache: {e}"))?;

    let db_path = database_dir.join("database.sqlite");
    let is_first_run = !db_path.exists();

    if is_first_run {
        let bundled_db = bundle_dir.join("database").join("database.sqlite");
        if bundled_db.exists() {
            fs::copy(&bundled_db, &db_path)
                .map_err(|e| format!("copy database: {e}"))?;
        } else {
            fs::File::create(&db_path)
                .map_err(|e| format!("create database: {e}"))?;
        }
    }

    if !storage_path.exists() {
        let bundled_storage = bundle_dir.join("storage");
        if bundled_storage.exists() {
            copy_dir_all(&bundled_storage, &storage_path)
                .map_err(|e| format!("copy storage: {e}"))?;
        } else {
            ensure_storage_tree(&storage_path)
                .map_err(|e| format!("init storage: {e}"))?;
        }
    }

    Ok((db_path, storage_path, bootstrap_path, is_first_run))
}

impl AppPaths {
    fn from_bundle(
        handle: &tauri::AppHandle,
        base_path: PathBuf,
    ) -> Result<(Self, bool), String> {
        let project_dir = base_path.join("resources").join("rysgally-hasap-market");
        let php_ini = base_path.join("binaries").join("php.ini");

        if !project_dir.join("server.php").exists() {
            return Err(format!(
                "Laravel bundle not found at {}",
                project_dir.display()
            ));
        }

        let (db_path, _storage_path, _bootstrap_path, is_first_run) =
            init_writable_paths(handle, &project_dir)?;

        Ok((
            Self {
                public_dir_arg: path_for_php(&project_dir.join("public")),
                server_php_arg: path_for_php(&project_dir.join("server.php")),
                php_ini_arg: path_for_php(&php_ini),
                db_path_arg: path_for_php(&db_path),
                project_dir,
            },
            is_first_run,
        ))
    }
}

fn apply_php_env(
    cmd: tauri_plugin_shell::process::Command,
    paths: &AppPaths,
    storage_path: &Path,
    bootstrap_path: &Path,
) -> tauri_plugin_shell::process::Command {
    cmd.current_dir(&paths.project_dir)
        .env("DB_CONNECTION", "sqlite")
        .env("DB_DATABASE", &paths.db_path_arg)
        .env("TAURI_STORAGE_PATH", path_for_php(storage_path))
        .env("TAURI_BOOTSTRAP_PATH", path_for_php(bootstrap_path))
        .env("APP_KEY", APP_KEY)
        .env("APP_ENV", "production")
        .env("APP_DEBUG", "false")
        .env("SESSION_SECURE_COOKIE", "false")
        .env("SESSION_DRIVER", "file")
        .env("APP_URL", "http://127.0.0.1:8001")
}

async fn run_artisan(
    handle: &tauri::AppHandle,
    paths: &AppPaths,
    storage_path: &Path,
    bootstrap_path: &Path,
    args: &[&str],
) {
    let label = args.join(" ");
    let sidecar = match handle.shell().sidecar("php") {
        Ok(cmd) => cmd,
        Err(e) => {
            eprintln!("PHP sidecar unavailable for {label}: {e}");
            return;
        }
    };

    let cmd = apply_php_env(
        sidecar.args(
            std::iter::once("-c")
                .chain(std::iter::once(paths.php_ini_arg.as_str()))
                .chain(args.iter().copied()),
        ),
        paths,
        storage_path,
        bootstrap_path,
    );

    match cmd.spawn() {
        Ok((mut rx, _)) => {
            while let Some(event) = rx.recv().await {
                match &event {
                    CommandEvent::Stderr(line) => {
                        eprintln!("artisan ({label}) err: {}", String::from_utf8_lossy(line))
                    }
                    CommandEvent::Terminated(payload) => {
                        if payload.code != Some(0) {
                            eprintln!("artisan ({label}) exited with {:?}", payload.code);
                        }
                        break;
                    }
                    _ => {}
                }
            }
        }
        Err(e) => eprintln!("Failed to spawn artisan ({label}): {e}"),
    }
}

fn kill_port_8001() {
    #[cfg(target_os = "macos")]
    {
        let _ = std::process::Command::new("sh")
            .arg("-c")
            .arg("lsof -ti:8001 | xargs kill -9 2>/dev/null || true")
            .output();
    }

    #[cfg(target_os = "windows")]
    {
        let _ = std::process::Command::new("cmd")
            .args([
                "/C",
                "for /f \"tokens=5\" %a in ('netstat -aon ^| findstr :8001') do taskkill /F /PID %a 2>nul",
            ])
            .output();
    }
}

fn main() {
    tauri::Builder::default()
        .plugin(tauri_plugin_shell::init())
        .plugin(
            tauri_plugin_updater::Builder::new()
                .pubkey("RWQGoyosV2pIpN1nsc7pzzMMJLEj5gqkZF5yNBlktv7wfduk6yWX/J/o")
                .build(),
        )
        .setup(|app| {
            let handle = app.handle().clone();

            let base_path = match handle.path().resource_dir() {
                Ok(p) => p,
                Err(e) => {
                    eprintln!("Resource dir error: {e}");
                    return Ok(());
                }
            };

            let (paths, is_first_run) = match AppPaths::from_bundle(&handle, base_path) {
                Ok(p) => p,
                Err(e) => {
                    eprintln!("{e}");
                    return Ok(());
                }
            };

            let storage_path = handle
                .path()
                .app_data_dir()
                .map_err(|e| eprintln!("app_data_dir: {e}"))
                .ok()
                .map(|d| d.join("rysgally-hasap-market").join("storage"))
                .unwrap_or_else(|| paths.project_dir.join("storage"));

            let bootstrap_path = handle
                .path()
                .app_data_dir()
                .ok()
                .map(|d| d.join("rysgally-hasap-market").join("bootstrap"))
                .unwrap_or_else(|| paths.project_dir.join("bootstrap"));

            let app_running = Arc::new(AtomicBool::new(true));
            let app_running_clone = app_running.clone();

            tauri::async_runtime::spawn(async move {
                // Убиваем старый PHP процесс на порту 8001
                kill_port_8001();
                tokio::time::sleep(std::time::Duration::from_millis(300)).await;

                // Всё ниже — ТОЛЬКО при первом запуске
                run_artisan(
                    &handle, &paths, &storage_path, &bootstrap_path,
                    &["artisan", "migrate", "--force"],
                ).await;
                if is_first_run {

                    run_artisan(
                        &handle, &paths, &storage_path, &bootstrap_path,
                        &["artisan", "db:seed", "--class=UserSeeder", "--force"],
                    ).await;

                    run_artisan(
                        &handle, &paths, &storage_path, &bootstrap_path,
                        &[
                            "artisan", "tinker", "--execute",
                            "if (Schema::hasTable('licenses')) { App\\Models\\License::updateOrCreate(['key' => 'RYSGALLY-HASAP-BUILD'], ['is_activated' => true, 'activated_at' => now()]); }",
                        ],
                    ).await;

                    // Кэшируем конфиги, роуты и вьюхи — страницы будут быстрыми
                    run_artisan(
                        &handle, &paths, &storage_path, &bootstrap_path,
                        &["artisan", "config:cache"],
                    ).await;

                    run_artisan(
                        &handle, &paths, &storage_path, &bootstrap_path,
                        &["artisan", "route:cache"],
                    ).await;

                    run_artisan(
                        &handle, &paths, &storage_path, &bootstrap_path,
                        &["artisan", "view:cache"],
                    ).await;
                }

                // Запускаем PHP встроенный сервер
                let sidecar = match handle.shell().sidecar("php") {
                    Ok(cmd) => cmd,
                    Err(e) => {
                        eprintln!("PHP sidecar unavailable for built-in server: {e}");
                        return;
                    }
                };

                let server_cmd = apply_php_env(
                    sidecar.args([
                        "-c",
                        paths.php_ini_arg.as_str(),
                        "-S",
                        "127.0.0.1:8001",
                        "-t",
                        paths.public_dir_arg.as_str(),
                        paths.server_php_arg.as_str(),
                    ]),
                    &paths,
                    &storage_path,
                    &bootstrap_path,
                );

                match server_cmd.spawn() {
                    Ok((mut rx, _)) => {
                        // Ждём пока сервер поднимется (до 10 секунд)
                        let mut attempts = 0;
                        while attempts < 40 {
                            if TcpStream::connect("127.0.0.1:8001").is_ok() {
                                break;
                            }
                            tokio::time::sleep(std::time::Duration::from_millis(250)).await;
                            attempts += 1;
                        }

                        if let Err(e) = WebviewWindowBuilder::new(
                            &handle,
                            "main",
                            WebviewUrl::External("http://127.0.0.1:8001".parse().unwrap()),
                        )
                        .title("rysgally-hasap-market")
                        .inner_size(1200.0, 800.0)
                        .resizable(true)
                        .additional_browser_args("--kiosk-printing")
                        .build()
                        {
                            eprintln!("Failed to open main window: {e}");
                        }

                        // Periodically clean up sessions and optimize database
                        let cleanup_handle = handle.clone();
                        let cleanup_paths = paths.clone();
                        let cleanup_storage = storage_path.clone();
                        let cleanup_bootstrap = bootstrap_path.clone();
                        
                        tauri::async_runtime::spawn(async move {
                            loop {
                                tokio::time::sleep(std::time::Duration::from_secs(3600)).await; // Every hour
                                if !app_running.load(Ordering::Relaxed) {
                                    break;
                                }
                                
                                // Clear old sessions
                                run_artisan(
                                    &cleanup_handle, &cleanup_paths, &cleanup_storage, &cleanup_bootstrap,
                                    &["artisan", "session:prune"],
                                ).await;
                                
                                // Optimize database
                                run_artisan(
                                    &cleanup_handle, &cleanup_paths, &cleanup_storage, &cleanup_bootstrap,
                                    &["artisan", "db:optimize"],
                                ).await;
                            }
                        });

                        while let Some(event) = rx.recv().await {
                            match event {
                                CommandEvent::Stderr(line) => {
                                    eprintln!("PHP: {}", String::from_utf8_lossy(&line))
                                }
                                CommandEvent::Terminated(payload) => {
                                    eprintln!("PHP server exited: {:?}", payload.code);
                                    break;
                                }
                                _ => {}
                            }
                        }
                    }
                    Err(e) => eprintln!("Failed to start PHP built-in server: {e}"),
                }

                app_running_clone.store(false, Ordering::Relaxed);
                kill_port_8001();

                use tauri_plugin_updater::UpdaterExt;
                if let Ok(updater) = handle.updater() {
                    if let Ok(Some(update)) = updater.check().await {
                        let _ = update.download_and_install(|_, _| {}, || {}).await;
                    }
                }
            });

            Ok(())
        })
        .on_window_event(|_window, event| {
            if let tauri::WindowEvent::CloseRequested { .. } = event {
                kill_port_8001();
            }
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}