#![cfg_attr(not(debug_assertions), windows_subsystem = "windows")]

use tauri_plugin_shell::ShellExt;
use tauri_plugin_shell::process::CommandEvent;
use tauri::Manager;
use tauri::{WebviewWindowBuilder, WebviewUrl};
use std::path::PathBuf;

fn main() {
    tauri::Builder::default()
        .plugin(tauri_plugin_shell::init())
        .plugin(
            tauri_plugin_updater::Builder::new()
            .pubkey("RWQGoyosV2pIpN1nsc7pzzMMJLEj5gqkZF5yNBlktv7wfduk6yWX/J/o")
            .build()
        )
        .setup(|app| {
            // 0. CREATE MAIN WINDOW
            let url = if cfg!(debug_assertions) {
                "http://127.0.0.1:8001"
            } else {
                "http://127.0.0.1:8001"
            };
            
            WebviewWindowBuilder::new(app, "main", WebviewUrl::External(url.parse().unwrap()))
                .title("rysgally-hasap-market")
                .inner_size(1200.0, 800.0)
                .resizable(true)
                .additional_browser_args("--kiosk-printing")
                .build()
                .expect("Failed to build main window");

            let handle = app.handle().clone();
            let curr_dir = std::env::current_dir().unwrap_or_else(|_| PathBuf::from("."));

            let base_path = if cfg!(debug_assertions) {
                if curr_dir.ends_with("src-tauri") { curr_dir.clone() } else { curr_dir.join("src-tauri") }
            } else {
                match handle.path().resource_dir() {
                    Ok(p) => p,
                    Err(e) => { eprintln!("Resource dir error: {}", e); return Ok(()); }
                }
            };

            let project_dir = base_path.join("resources").join("rysgally-hasap-market");
            let php_ini    = base_path.join("binaries").join("php.ini");
            let db_path    = project_dir.join("database").join("database.sqlite");

            println!("=== DEBUG ===");
            println!("project_dir: {:?}", project_dir);
            println!("project_dir exists: {}", project_dir.exists());
            println!("public exists: {}", project_dir.join("public").exists());
            println!("server.php exists: {}", project_dir.join("server.php").exists());
            println!("php.ini exists: {}", php_ini.exists());
            println!("db exists: {}", db_path.exists());
            println!("=============");

            tauri::async_runtime::spawn(async move {
                // 1. LICENSE ACTIVATION
                println!("Activating license...");
                if let Ok(license) = handle.shell().sidecar("php") {
                    let license = license
                        .args([
                            "-c", php_ini.to_str().unwrap_or_default(),
                            "artisan", "tinker",
                            "--execute", "if (Schema::hasTable('licenses')) { App\\Models\\License::updateOrCreate(['key' => 'RYSGALLY-HASAP-BUILD'], ['is_activated' => true, 'activated_at' => now()]); echo 'License activated'; }",
                        ])
                        .current_dir(&project_dir)
                        .env("DB_CONNECTION", "sqlite")
                        .env("DB_DATABASE", db_path.to_str().unwrap_or_default())
                        .env("APP_KEY", "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=")
                        .env("APP_ENV", "production")
                        .env("APP_DEBUG", "false");

                    match license.spawn() {
                        Ok((mut rx, _)) => {
                            while let Some(event) = rx.recv().await {
                                match event {
                                    CommandEvent::Stdout(line) => println!("LICENSE: {}", String::from_utf8_lossy(&line)),
                                    CommandEvent::Stderr(line) => eprintln!("LICENSE ERR: {}", String::from_utf8_lossy(&line)),
                                    CommandEvent::Terminated(_) => break,
                                    _ => (),
                                }
                            }
                            println!("License activation finished.");
                        }
                        Err(e) => eprintln!("LICENSE SPAWN ERROR: {}", e),
                    }
                } else {
                    eprintln!("ERROR: Could not get php sidecar for license activation");
                }

                // 2. MIGRATE
                println!("Starting migrate...");
                if let Ok(migrate) = handle.shell().sidecar("php") {
                    let migrate = migrate
                        .args([
                            "-c", php_ini.to_str().unwrap_or_default(),
                            "artisan", "migrate", "--force",
                        ])
                        .current_dir(&project_dir)
                        .env("DB_CONNECTION", "sqlite")
                        .env("DB_DATABASE", db_path.to_str().unwrap_or_default())
                        .env("APP_KEY", "base64:mL3/J3Jxsg7yS1WgaxI3mCXuB0iZTeKA5aVRSh9WMxg=")
                        .env("APP_ENV", "production")
                        .env("APP_DEBUG", "false");

                    match migrate.spawn() {
                        Ok((mut rx, _)) => {
                            while let Some(event) = rx.recv().await {
                                match event {
                                    CommandEvent::Stdout(line) => println!("MIGRATE: {}", String::from_utf8_lossy(&line)),
                                    CommandEvent::Stderr(line) => eprintln!("MIGRATE ERR: {}", String::from_utf8_lossy(&line)),
                                    CommandEvent::Terminated(_) => break,
                                    _ => (),
                                }
                            }
                            println!("Migrate finished.");
                        }
                        Err(e) => eprintln!("MIGRATE SPAWN ERROR: {}", e),
                    }
                } else {
                    eprintln!("ERROR: Could not get php sidecar for migrate");
                }

                // 2. PHP SERVER — starts immediately, no delays
                println!("Starting PHP server on 0.0.0.0:8001...");
                if let Ok(sidecar) = handle.shell().sidecar("php") {
                    let sidecar = sidecar
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
                        .env("APP_DEBUG", "false");

                    match sidecar.spawn() {
                        Ok((mut rx, _)) => {
                            // Keep reading PHP output forever
                            while let Some(event) = rx.recv().await {
                                match event {
                                    CommandEvent::Stdout(line) => println!("PHP: {}", String::from_utf8_lossy(&line)),
                                    CommandEvent::Stderr(line) => eprintln!("PHP ERR: {}", String::from_utf8_lossy(&line)),
                                    _ => (),
                                }
                            }
                        }
                        Err(e) => eprintln!("PHP SPAWN ERROR: {}", e),
                    }
                } else {
                    eprintln!("ERROR: Could not get php sidecar for server");
                }
            });

            // 3. UPDATER
            let handle_upd = app.handle().clone();
            tauri::async_runtime::spawn(async move {
                use tauri_plugin_updater::UpdaterExt;
                
                if let Ok(updater) = handle_upd.updater() {
                    if let Ok(Some(update)) = updater.check().await {
                        println!("Обновление: {}", update.version);
                        let _ = update.download_and_install(|_, _| {}, || {}).await;
                    }
                }
            });

            Ok(())
        })
        .run(tauri::generate_context!())
        .expect("error while running tauri application");
}