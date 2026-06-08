# Tauri PHP sidecar binaries

The desktop app embeds a **static PHP** binary as a Tauri sidecar. These binaries are **not** the official Windows PHP zip (which needs `php8.dll`).

## Release builds (GitHub Actions)

CI builds static PHP with [static-php-cli](https://github.com/crazywhalecc/static-php-cli) and places:

| Platform | File |
|----------|------|
| macOS (Apple Silicon) | `php-aarch64-apple-darwin` |
| Windows (x64) | `php-x86_64-pc-windows-msvc.exe` |

Tauri `externalBin` is configured as `binaries/php`, so the sidecar must be named with the `-$TARGET_TRIPLE` suffix above.

## Local development

- **macOS**: run the release workflow locally or build static PHP with static-php-cli and copy the output to `php-aarch64-apple-darwin`.
- **Windows**: do not use the official PHP `php.exe` distribution here. Build with static-php-cli in CI or locally, then copy `buildroot/bin/php.exe` to `php-x86_64-pc-windows-msvc.exe`.

`php.ini` in this folder is bundled as a resource and passed to every PHP invocation.
