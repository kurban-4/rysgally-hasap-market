#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use tauri_plugin_shell::ShellExt;
use tauri_plugin_shell::process::CommandEvent;
use tauri::Manager;
use tauri::{WebviewWindowBuilder, WebviewUrl};

fn main() {
    tauri::Builder::default()
        .plugin(tauri_plugin_shell::init())
        .plugin(
            tauri_plugin_updater::Builder::new()
            .pubkey("RWQGoyosV2pIpN1nsc7pzzMMJLEj5gqkZF5yNBlktv7wfduk6yWX/J/o")
            .build()
        )
        .setup(|app| {
            let handle = app.handle().clone();

            let base_path = match handle.path().resource_dir() {
                Ok(p) => p,
                Err(e) => { eprintln!("Resource dir error: {}", e); return Ok(()); }
            };

            let project_dir = base_path.join("resources").join("rysgally-hasap-market");
            let php_ini     = base_path.join("binaries").join("php.ini");
            let db_path     = project_dir.join("database").join("database.sqlite");

            println!("project_dir: {:?}", project_dir);
            println!("server.php exists: {}", project_dir.join("server.php").exists());

            tauri::async_runtime::spawn(async move {
                // 1. LICENSE
                if let Ok(cmd) = handle.shell().sidecar("php") {
                    if let Ok((mut rx, _)) = cmd
                        .args(["-c", php_ini.to_str().unwrap_or_default(), "artisan", "tinker", "--execute",
                               "if (Schema::hasTable('licenses')) { App\\Models\\License::updateOrCreate(['key' => 'RYSGALLY-HASAP-BUILD'], ['is_activated' => true, 'activated_at' => now()]); }"])
                        .current_dir(&project_dir)
                        .env("DB_CONNECTION", "sqlite")
                        .env("DB_DATABASE", db_path.to_str().unwrap_or_default())
                        .env("APP_KEY", "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=")
                        .env("APP_ENV", "production")
                        .env("APP_DEBUG", "false")
                        .spawn() {
                        while let Some(event) = rx.recv().await {
                            if let CommandEvent::Terminated(_) = event { break; }
                        }
                    }
                }

                // 2. MIGRATE
                if let Ok(cmd) = handle.shell().sidecar("php") {
                    if let Ok((mut rx, _)) = cmd
                        .args(["-c", php_ini.to_str().unwrap_or_default(), "artisan", "migrate", "--force"])
                        .current_dir(&project_dir)
                        .env("DB_CONNECTION", "sqlite")
                        .env("DB_DATABASE", db_path.to_str().unwrap_or_default())
                        .env("APP_KEY", "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=")
                        .env("APP_ENV", "production")
                        .env("APP_DEBUG", "false")
                        .spawn() {
                        while let Some(event) = rx.recv().await {
                            if let CommandEvent::Terminated(_) = event { break; }
                        }
                    }
                }

                // 3. PHP SERVER
                if let Ok(sidecar) = handle.shell().sidecar("php") {
                    if let Ok((mut rx, _)) = sidecar
                        .args([
                            "-c", php_ini.to_str().unwrap_or_default(),
                            "-S", "0.0.0.0:8001",
                            "-t", project_dir.join("public").to_str().unwrap_or_default(),
                            project_dir.join("server.php").to_str().unwrap_or_default(),
                        ])
                        .current_dir(&project_dir)
                        .env("DB_CONNECTION", "sqlite")
                        .env("DB_DATABASE", db_path.to_str().unwrap_or_default())
                        .env("APP_KEY", "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=")
                        .env("APP_ENV", "production")
                        .env("APP_DEBUG", "false")
                        .spawn() {

                        // Wait 2 sec for PHP to start
                        tokio::time::sleep(std::time::Duration::from_secs(2)).await;

                        // 4. CREATE WINDOW — only after PHP is ready
                        let _ = WebviewWindowBuilder::new(&handle, "main", WebviewUrl::External("http://127.0.0.1:8001".parse().unwrap()))
                            .title("rysgally-hasap-market")
                            .inner_size(1200.0, 800.0)
                            .resizable(true)
                            .additional_browser_args("--kiosk-printing")
                            .build();

                        while let Some(event) = rx.recv().await {
                            match event {
                                CommandEvent::Stdout(line) => println!("PHP: {}", String::from_utf8_lossy(&line)),
                                CommandEvent::Stderr(line) => eprintln!("PHP ERR: {}", String::from_utf8_lossy(&line)),
                                _ => (),
                            }
                        }
                    }
                }

                // 5. UPDATER
                use tauri_plugin_updater::UpdaterExt;
                if let Ok(updater) = handle.updater() {
                    if let Ok(Some(update)) = updater.check().await {
                        let _ = update.download_and_install(|_, _| {}, || {}).await;
                    }
                }
            });

            Ok(())
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}