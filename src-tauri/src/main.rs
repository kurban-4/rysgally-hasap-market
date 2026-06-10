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
const SERVER_ADDR: &str = "127.0.0.1:8001";

#[derive(Clone)]
struct AppPaths {
    project_dir: PathBuf,
    php_ini_arg: Option<String>,
    db_path_arg: String,
    public_dir_native: String,
    server_php_native: String,
}

fn path_for_php(path: &Path) -> String {
    path.to_string_lossy().replace('\\', "/")
}

fn path_for_php_config(path: &Path) -> String {
    #[cfg(target_os = "windows")]
    {
        path.to_string_lossy().to_string()
    }
    #[cfg(not(target_os = "windows"))]
    {
        path_for_php(path)
    }
}

fn path_for_server_arg(path: &Path) -> String {
    #[cfg(target_os = "windows")]
    {
        path.to_string_lossy().to_string()
    }
    #[cfg(not(target_os = "windows"))]
    {
        path_for_php(path)
    }
}

fn server_url() -> String {
    format!("http://{SERVER_ADDR}")
}

#[cfg(target_os = "windows")]
fn append_startup_log(handle: &tauri::AppHandle, message: &str) {
    if let Ok(dir) = handle.path().app_data_dir() {
        let log_path = dir.join("rysgally-hasap-market").join("startup.log");
        let _ = fs::create_dir_all(log_path.parent().unwrap_or(&dir));
        use std::io::Write;
        if let Ok(mut file) = fs::OpenOptions::new()
            .create(true)
            .append(true)
            .open(log_path)
        {
            let _ = writeln!(file, "{message}");
        }
    }
    eprintln!("{message}");
}

#[cfg(not(target_os = "windows"))]
fn append_startup_log(_handle: &tauri::AppHandle, message: &str) {
    eprintln!("{message}");
}

fn resolve_php_command(
    handle: &tauri::AppHandle,
) -> Result<tauri_plugin_shell::process::Command, String> {
    #[cfg(target_os = "windows")]
    {
        for name in ["binaries/php", "php"] {
            if let Ok(cmd) = handle.shell().sidecar(name) {
                append_startup_log(handle, &format!("Using PHP sidecar: {name}"));
                return Ok(cmd);
            }
        }
        append_startup_log(handle, "PHP sidecar lookup failed for binaries/php and php");
        return Err("PHP sidecar not found".to_string());
    }

    #[cfg(not(target_os = "windows"))]
    {
        handle.shell().sidecar("php").map_err(|e| e.to_string())
    }
}

fn open_main_window(handle: &tauri::AppHandle, url: &str) {
    let parsed = match url.parse() {
        Ok(parsed) => parsed,
        Err(e) => {
            append_startup_log(handle, &format!("Invalid window URL {url}: {e}"));
            return;
        }
    };

    if let Some(window) = handle.get_webview_window("main") {
        if let Err(e) = window.navigate(parsed) {
            append_startup_log(handle, &format!("Failed to navigate main window: {e}"));
        }
        return;
    }

    if let Err(e) = WebviewWindowBuilder::new(handle, "main", WebviewUrl::External(parsed))
        .title("rysgally-hasap-market")
        .inner_size(1200.0, 800.0)
        .resizable(true)
        .additional_browser_args("--kiosk-printing")
        .build()
    {
        append_startup_log(handle, &format!("Failed to open main window: {e}"));
    }
}

#[cfg(target_os = "windows")]
fn open_loading_window(handle: &tauri::AppHandle) {
    let url = "data:text/html;charset=utf-8,\
        <html><head><meta charset=utf-8><title>rysgally-hasap-market</title></head>\
        <body style=font-family:Segoe UI,sans-serif;padding:2em>\
        <h2>Starting application...</h2><p>Please wait while the local server starts.</p>\
        </body></html>";
    open_main_window(handle, url);
}

#[cfg(target_os = "windows")]
fn open_error_window(handle: &tauri::AppHandle, message: &str) {
    let escaped = message
        .replace('&', "&amp;")
        .replace('<', "&lt;")
        .replace('>', "&gt;");
    let url = format!(
        "data:text/html;charset=utf-8,\
        <html><head><meta charset=utf-8><title>rysgally-hasap-market</title></head>\
        <body style=font-family:Segoe UI,sans-serif;padding:2em>\
        <h2>Startup error</h2><pre>{escaped}</pre>\
        </body></html>"
    );
    open_main_window(handle, &url);
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

        let public_dir_path = project_dir.join("public");
        let server_php_path = project_dir.join("server.php");
        let php_ini_arg = php_ini.exists().then(|| path_for_php_config(&php_ini));

        Ok((
            Self {
                php_ini_arg,
                db_path_arg: path_for_php(&db_path),
                public_dir_native: path_for_server_arg(&public_dir_path),
                server_php_native: path_for_server_arg(&server_php_path),
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
        .env("APP_URL", server_url())
}

fn build_php_args(php_ini_arg: Option<&str>, args: &[&str]) -> Vec<String> {
    let mut out = Vec::new();
    if let Some(ini) = php_ini_arg {
        out.push("-c".to_string());
        out.push(ini.to_string());
    }
    out.extend(args.iter().map(|arg| (*arg).to_string()));
    out
}

async fn run_artisan(
    handle: &tauri::AppHandle,
    paths: &AppPaths,
    storage_path: &Path,
    bootstrap_path: &Path,
    args: &[&str],
) {
    let label = args.join(" ");
    let sidecar = match resolve_php_command(handle) {
        Ok(cmd) => cmd,
        Err(e) => {
            append_startup_log(handle, &format!("PHP sidecar unavailable for {label}: {e}"));
            return;
        }
    };

    let php_args = build_php_args(paths.php_ini_arg.as_deref(), args);
    let cmd = apply_php_env(sidecar.args(php_args), paths, storage_path, bootstrap_path);

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

fn kill_server_port() {
    #[cfg(target_os = "macos")]
    {
        let _ = std::process::Command::new("sh")
            .arg("-c")
            .arg("lsof -ti:8001 | xargs kill -9 2>/dev/null || true")
            .output();
    }

    #[cfg(target_os = "windows")]
    {
        let _ = std::process::Command::new("powershell")
            .args([
                "-Command",
                "Get-NetTCPConnection -LocalPort 8001 -ErrorAction SilentlyContinue | \
                 ForEach-Object { Stop-Process -Id $_.OwningProcess -Force -ErrorAction SilentlyContinue }",
            ])
            .output();
    }
}

async fn wait_for_php_server(
    handle: &tauri::AppHandle,
    event_rx: &mut tauri::async_runtime::Receiver<CommandEvent>,
) -> bool {
    #[cfg(target_os = "windows")]
    let max_attempts = 120;
    #[cfg(not(target_os = "windows"))]
    let max_attempts = 40;

    for attempt in 0..max_attempts {
        while let Ok(event) = event_rx.try_recv() {
            match event {
                CommandEvent::Stderr(line) => {
                    let msg = format!("PHP: {}", String::from_utf8_lossy(&line));
                    append_startup_log(handle, &msg);
                }
                CommandEvent::Stdout(line) => {
                    append_startup_log(
                        handle,
                        &format!("PHP stdout: {}", String::from_utf8_lossy(&line)),
                    );
                }
                CommandEvent::Terminated(payload) => {
                    append_startup_log(
                        handle,
                        &format!("PHP server exited before ready: {:?}", payload.code),
                    );
                    return false;
                }
                _ => {}
            }
        }

        if TcpStream::connect(SERVER_ADDR).is_ok() {
            append_startup_log(
                handle,
                &format!("PHP server ready after {} ms", attempt * 250),
            );
            return true;
        }

        tokio::time::sleep(std::time::Duration::from_millis(250)).await;
    }

    append_startup_log(
        handle,
        &format!(
            "PHP server failed to start within {} seconds",
            max_attempts * 250 / 1000
        ),
    );
    false
}

async fn run_setup_commands(
    handle: &tauri::AppHandle,
    paths: &AppPaths,
    storage_path: &Path,
    bootstrap_path: &Path,
    is_first_run: bool,
) {
    run_artisan(
        handle,
        paths,
        storage_path,
        bootstrap_path,
        &["artisan", "migrate", "--force"],
    )
    .await;

    if !is_first_run {
        return;
    }

    run_artisan(
        handle,
        paths,
        storage_path,
        bootstrap_path,
        &["artisan", "db:seed", "--class=UserSeeder", "--force"],
    )
    .await;

    run_artisan(
        handle,
        paths,
        storage_path,
        bootstrap_path,
        &[
            "artisan",
            "tinker",
            "--execute",
            "if (Schema::hasTable('licenses')) { App\\Models\\License::updateOrCreate(['key' => 'RYSGALLY-HASAP-BUILD'], ['is_activated' => true, 'activated_at' => now()]); }",
        ],
    )
    .await;

    run_artisan(
        handle,
        paths,
        storage_path,
        bootstrap_path,
        &["artisan", "config:cache"],
    )
    .await;

    run_artisan(
        handle,
        paths,
        storage_path,
        bootstrap_path,
        &["artisan", "route:cache"],
    )
    .await;

    run_artisan(
        handle,
        paths,
        storage_path,
        bootstrap_path,
        &["artisan", "view:cache"],
    )
    .await;
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

            #[cfg(target_os = "windows")]
            open_loading_window(&handle);

            let base_path = match handle.path().resource_dir() {
                Ok(p) => p,
                Err(e) => {
                    let message = format!("Resource dir error: {e}");
                    append_startup_log(&handle, &message);
                    #[cfg(target_os = "windows")]
                    open_error_window(&handle, &message);
                    return Ok(());
                }
            };

            let (paths, is_first_run) = match AppPaths::from_bundle(&handle, base_path) {
                Ok(p) => p,
                Err(e) => {
                    append_startup_log(&handle, &e);
                    #[cfg(target_os = "windows")]
                    open_error_window(&handle, &e);
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
                kill_server_port();
                tokio::time::sleep(std::time::Duration::from_millis(300)).await;

                run_setup_commands(
                    &handle,
                    &paths,
                    &storage_path,
                    &bootstrap_path,
                    is_first_run,
                )
                .await;

                let sidecar = match resolve_php_command(&handle) {
                    Ok(cmd) => cmd,
                    Err(e) => {
                        let message = format!("PHP sidecar unavailable for built-in server: {e}");
                        append_startup_log(&handle, &message);
                        #[cfg(target_os = "windows")]
                        open_error_window(&handle, &message);
                        open_main_window(&handle, &server_url());
                        return;
                    }
                };

                append_startup_log(&handle, &format!("Starting PHP server on {SERVER_ADDR}"));
                append_startup_log(&handle, &format!("  Public dir: {}", paths.public_dir_native));
                append_startup_log(
                    &handle,
                    &format!("  Server script: {}", paths.server_php_native),
                );
                append_startup_log(
                    &handle,
                    &format!("  PHP ini: {}", paths.php_ini_arg.as_deref().unwrap_or("(none)")),
                );
                append_startup_log(
                    &handle,
                    &format!(
                        "  Public dir exists: {}",
                        paths.project_dir.join("public").exists()
                    ),
                );
                append_startup_log(
                    &handle,
                    &format!(
                        "  Server script exists: {}",
                        paths.project_dir.join("server.php").exists()
                    ),
                );

                let server_args = build_php_args(
                    paths.php_ini_arg.as_deref(),
                    &["-S", SERVER_ADDR, "-t", paths.public_dir_native.as_str()],
                );
                let server_cmd = apply_php_env(
                    sidecar
                        .args(server_args)
                        .arg(paths.server_php_native.as_str()),
                    &paths,
                    &storage_path,
                    &bootstrap_path,
                );

                match server_cmd.spawn() {
    Ok((mut rx, _)) => {
        let server_started = wait_for_php_server(&handle, &mut rx).await;

        #[cfg(target_os = "windows")]
        if !server_started {
            let log_path = handle
                .path()
                .app_data_dir()
                .map(|d| {
                    d.join("rysgally-hasap-market")
                        .join("startup.log")
                        .to_string_lossy()
                        .to_string()
                })
                .unwrap_or_else(|_| "unknown".to_string());

            let message = format!(
                "PHP server failed to start on {SERVER_ADDR}\n\
                 PHP binary: {}\n\
                 Public dir exists: {}\n\
                 server.php exists: {}\n\
                 \nPlease send this file to developer:\n{}",
                paths.public_dir_native,
                paths.project_dir.join("public").exists(),
                paths.project_dir.join("server.php").exists(),
                log_path
            );
            open_error_window(&handle, &message);
            return;
        }

        open_main_window(&handle, &server_url());

        let cleanup_handle = handle.clone();
        let cleanup_paths = paths.clone();
        let cleanup_storage = storage_path.clone();
        let cleanup_bootstrap = bootstrap_path.clone();

        tauri::async_runtime::spawn(async move {
            loop {
                tokio::time::sleep(std::time::Duration::from_secs(3600)).await;
                if !app_running.load(Ordering::Relaxed) {
                    break;
                }
                run_artisan(
                    &cleanup_handle,
                    &cleanup_paths,
                    &cleanup_storage,
                    &cleanup_bootstrap,
                    &["artisan", "session:prune"],
                )
                .await;
                run_artisan(
                    &cleanup_handle,
                    &cleanup_paths,
                    &cleanup_storage,
                    &cleanup_bootstrap,
                    &["artisan", "db:optimize"],
                )
                .await;
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
    Err(e) => {
        let message = format!("Failed to start PHP built-in server: {e}");
        append_startup_log(&handle, &message);
        #[cfg(target_os = "windows")]
        open_error_window(&handle, &message);
        open_main_window(&handle, &server_url());
    }
}

                app_running_clone.store(false, Ordering::Relaxed);
                kill_server_port();

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
                kill_server_port();
            }
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}
