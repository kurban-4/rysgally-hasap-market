#!/bin/bash

echo "Building Tauri app..."
cd src-tauri

# Build the app
cargo tauri build

# Check if build was successful
if [ $? -eq 0 ]; then
    echo "Build successful! Opening app..."
    # Open the app bundle
    open target/release/bundle/macos/rysgally-hasap-market.app
else
    echo "Build failed!"
    exit 1
fi
