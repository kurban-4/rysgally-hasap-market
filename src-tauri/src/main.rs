#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]
 
use std::fs;
use std::net::TcpStream;
use std::path::{Path, PathBuf};
use std::sync::Mutex;
 
use tauri::Manager;
use tauri::{WebviewUrl, WebviewWindowBuilder};
use tauri_plugin_shell::process::{CommandChild, CommandEvent};
use tauri_plugin_shell::ShellExt;
 
const APP_KEY: &str = "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=";
 
static PHP_SERVER: Mutex<Option<CommandChild>> = Mutex::new(None);
 
#[derive(Clone)]
struct AppPaths {
    project_dir: PathBuf,
    php_ini_arg: String,
    db_path_arg: String,
    public_dir_native: String,
    server_php_native: String,
}
 
fn path_for_php(path: &Path) -> String {
    path.to_string_lossy().replace('\\', "/")
}
 
fn path_for_server_arg(path: &Path) -> String {
    path.to_string_lossy().to_string()
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
 
        Ok((
            Self {
                php_ini_arg: path_for_php(&php_ini),
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
            log::error!("PHP sidecar unavailable for {label}: {e}");
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
                        log::warn!("artisan ({label}) err: {}", String::from_utf8_lossy(line))
                    }
                    CommandEvent::Terminated(payload) => {
                        if payload.code != Some(0) {
                            log::error!("artisan ({label}) exited with {:?}", payload.code);
                        }
                        break;
                    }
                    _ => {}
                }
            }
        }
        Err(e) => log::error!("Failed to spawn artisan ({label}): {e}"),
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
        use std::os::windows::process::CommandExt;
        const CREATE_NO_WINDOW: u32 = 0x0800_0000;
        let _ = std::process::Command::new("cmd")
            .args([
                "/C",
                "for /f \"tokens=5\" %a in ('netstat -aon ^| findstr :8001 ^| findstr LISTENING') do taskkill /F /PID %a 2>nul",
            ])
            .creation_flags(CREATE_NO_WINDOW)
            .output();
    }
}
 
fn stop_php_server() {
    if let Ok(mut guard) = PHP_SERVER.lock() {
        if let Some(child) = guard.take() {
            let _ = child.kill();
        }
    }
    kill_port_8001();
}
 
fn main() {
    tauri::Builder::default()
        .plugin(
            tauri_plugin_log::Builder::default()
                .level(log::LevelFilter::Info)
                .build(),
        )
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
                    log::error!("Resource dir error: {e}");
                    return Ok(());
                }
            };
 
            let (paths, is_first_run) = match AppPaths::from_bundle(&handle, base_path) {
                Ok(p) => p,
                Err(e) => {
                    log::error!("{e}");
                    return Ok(());
                }
            };
 
            let storage_path = handle
                .path()
                .app_data_dir()
                .map_err(|e| log::error!("app_data_dir: {e}"))
                .ok()
                .map(|d| d.join("rysgally-hasap-market").join("storage"))
                .unwrap_or_else(|| paths.project_dir.join("storage"));
 
            let bootstrap_path = handle
                .path()
                .app_data_dir()
                .ok()
                .map(|d| d.join("rysgally-hasap-market").join("bootstrap"))
                .unwrap_or_else(|| paths.project_dir.join("bootstrap"));
 
            // ── Показываем загрузочный экран СРАЗУ, до старта PHP ──
            let tmp = std::env::temp_dir();
            let loading_file = tmp.join("rysgally_loading.html");
            let _ = fs::write(&loading_file, r#"<!DOCTYPE html>
<html lang="ru"><head><meta charset="UTF-8"><title>Загрузка...</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:#0f172a; display:flex; align-items:center;
         justify-content:center; min-height:100vh; font-family:system-ui,sans-serif; }
  .spinner { width:60px; height:60px; border:4px solid #1e293b;
             border-top:4px solid #3b82f6; border-radius:50%;
             animation:spin 1s linear infinite; margin-bottom:24px; }
  @keyframes spin { to { transform:rotate(360deg); } }
  p { color:#94a3b8; font-size:16px; }
</style></head>
<body><div style="text-align:center">
  <div class="spinner"></div>
  <p>Запуск приложения, подождите...</p>
</div></body></html>"#);
 
            #[cfg(target_os = "windows")]
            let loading_url_str = format!("file:///{}", loading_file.to_string_lossy().replace('\\', "/"));
            #[cfg(not(target_os = "windows"))]
            let loading_url_str = format!("file://{}", loading_file.to_string_lossy());
 
            if let Ok(u) = loading_url_str.parse() {
                let _ = WebviewWindowBuilder::new(app, "main", WebviewUrl::External(u))
                    .title("rysgally-hasap-market")
                    .inner_size(1200.0, 800.0)
                    .resizable(true)
                    .additional_browser_args(
                        "--disable-features=msWebOOUI,msPdfOOUI,msSmartScreenProtection --kiosk-printing",
                    )
                    .build();
            }
            // ── конец загрузочного экрана ──
 
            tauri::async_runtime::spawn(async move {
                // Очистка осиротевшего PHP процесса на порту 8001
                kill_port_8001();
                tokio::time::sleep(std::time::Duration::from_millis(300)).await;
 
                // Миграции — на каждом запуске (идемпотентные)
                run_artisan(
                    &handle, &paths, &storage_path, &bootstrap_path,
                    &["artisan", "migrate", "--force"],
                ).await;
 
                if is_first_run {
                    run_artisan(
                        &handle, &paths, &storage_path, &bootstrap_path,
                        &["artisan", "db:seed", "--class=UserSeeder", "--force"],
                    ).await;
                }
 
                // config:cache / route:cache / view:cache убраны —
                // они запускают отдельный PHP процесс на каждый вызов
                // и суммарно добавляют ~3-4 минуты к старту на Windows.
 
                // Запускаем PHP встроенный сервер
                let sidecar = match handle.shell().sidecar("php") {
                    Ok(cmd) => cmd,
                    Err(e) => {
                        log::error!("PHP sidecar unavailable for built-in server: {e}");
                        return;
                    }
                };
 
                log::info!("Starting PHP server with:");
                log::info!("  Public dir: {}", paths.public_dir_native);
                log::info!("  Server script: {}", paths.server_php_native);
                log::info!("  PHP ini: {}", paths.php_ini_arg);
                log::info!("  Public dir exists: {}", paths.project_dir.join("public").exists());
                log::info!("  Server script exists: {}", paths.project_dir.join("server.php").exists());
 
                let server_cmd = {
                    let sidecar_base = apply_php_env(
                        sidecar.args([
                            "-c",
                            paths.php_ini_arg.as_str(),
                            "-S",
                            "127.0.0.1:8001",
                            "-t",
                            paths.public_dir_native.as_str(),
                        ]),
                        &paths,
                        &storage_path,
                        &bootstrap_path,
                    );
                    sidecar_base.arg(paths.server_php_native.as_str())
                };
 
                match server_cmd.spawn() {
                    Ok((mut rx, child)) => {
                        if let Ok(mut guard) = PHP_SERVER.lock() {
                            *guard = Some(child);
                        }
 
                        #[cfg(target_os = "windows")]
                        let max_attempts = 100; // 25 секунд на Windows
                        #[cfg(not(target_os = "windows"))]
                        let max_attempts = 40;  // 10 секунд на других ОС
 
                        let mut attempts = 0;
                        let mut server_started = false;
                        while attempts < max_attempts {
                            if TcpStream::connect("127.0.0.1:8001").is_ok() {
                                log::info!("PHP server ready after {} ms", attempts * 250);
                                server_started = true;
                                break;
                            }
                            tokio::time::sleep(std::time::Duration::from_millis(250)).await;
                            attempts += 1;
                        }
 
                        if !server_started {
                            log::error!("PHP server failed to start within {} seconds", max_attempts * 250 / 1000);
                        }
 
                        // Переходим на нужный URL в уже открытом загрузочном окне
                        let final_url: url::Url = if server_started {
                            "http://127.0.0.1:8001".parse().unwrap()
                        } else {
                            let error_file = std::env::temp_dir().join("rysgally_error.html");
                            let _ = fs::write(&error_file, r#"<!DOCTYPE html>
<html lang="ru"><head><meta charset="UTF-8"><title>Ошибка</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { background:#0f172a; display:flex; align-items:center;
         justify-content:center; min-height:100vh; font-family:system-ui,sans-serif; }
  .card { background:#1e293b; border-radius:12px; padding:40px;
          max-width:500px; text-align:center; box-shadow:0 20px 60px rgba(0,0,0,.5); }
  h2 { color:#ef4444; margin-bottom:16px; font-size:1.5rem; }
  p { color:#94a3b8; line-height:1.8; }
</style></head>
<body><div class="card">
  <div style="font-size:3.5rem;margin-bottom:16px">⚠️</div>
  <h2>Приложение не запустилось</h2>
  <p>PHP сервер не смог запуститься.<br><br>
  Возможные причины:<br>
  — Антивирус блокирует приложение<br>
  — Недостаточно прав<br><br>
  Попробуйте переустановить приложение<br>
  или сообщите разработчику.</p>
</div></body></html>"#);
                            #[cfg(target_os = "windows")]
                            let url_str = format!("file:///{}", error_file.to_string_lossy().replace('\\', "/"));
                            #[cfg(not(target_os = "windows"))]
                            let url_str = format!("file://{}", error_file.to_string_lossy());
                            url_str.parse().unwrap_or_else(|_| "http://127.0.0.1:8001".parse().unwrap())
                        };
 
                        if let Some(window) = handle.get_webview_window("main") {
                            let _ = window.navigate(final_url);
                        }
 
                        while let Some(event) = rx.recv().await {
                            match event {
                                CommandEvent::Stderr(line) => {
                                    log::info!("PHP: {}", String::from_utf8_lossy(&line))
                                }
                                CommandEvent::Terminated(payload) => {
                                    log::error!("PHP server exited: {:?}", payload.code);
                                    break;
                                }
                                _ => {}
                            }
                        }
                    }
                    Err(e) => log::error!("Failed to start PHP built-in server: {e}"),
                }
 
                stop_php_server();
            });
 
            // Проверяем обновления в фоне
            let updater_handle = app.handle().clone();
            tauri::async_runtime::spawn(async move {
                tokio::time::sleep(std::time::Duration::from_secs(20)).await;
                use tauri_plugin_updater::UpdaterExt;
                let updater = match updater_handle.updater() {
                    Ok(u) => u,
                    Err(e) => {
                        log::error!("Updater unavailable: {e}");
                        return;
                    }
                };
                match updater.check().await {
                    Ok(Some(update)) => {
                        log::info!("Update {} found, downloading", update.version);
                        match update.download(|_, _| {}, || {}).await {
                            Ok(bytes) => {
                                log::info!("Update downloaded, stopping PHP server and installing");
                                stop_php_server();
                                if let Err(e) = update.install(bytes) {
                                    log::error!("Failed to install update: {e}");
                                }
                            }
                            Err(e) => log::error!("Failed to download update: {e}"),
                        }
                    }
                    Ok(None) => log::info!("No update available"),
                    Err(e) => log::error!("Update check failed: {e}"),
                }
            });
 
            Ok(())
        })
        .on_window_event(|_window, event| {
            if let tauri::WindowEvent::CloseRequested { .. } = event {
                stop_php_server();
            }
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}