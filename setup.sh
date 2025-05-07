#!/bin/bash
echo "Setting up SWAT Monitoring System..."

# Install dependencies
echo "Installing PHP dependencies..."
composer update

# Create public folders
echo "Creating public folders..."
mkdir -p public/css
mkdir -p public/js
mkdir -p public/images

# Copy files
echo "Copying CSS and JS files..."
cp -f resources/css/styles.css public/css/
cp -f resources/js/script.js public/js/
# Tambahkan logo jika ada
# cp -f resources/images/pup2.png public/images/

# Check server time
echo "Checking server time..."
SERVER_TIME=$(date)
echo "Server time: $SERVER_TIME"

# Retrieve Google time
echo "Retrieving Google time..."
GOOGLE_TIME=$(curl -I google.com 2>/dev/null | grep -i "date:" | sed 's/[Dd]ate: //g')
echo "Google time: $GOOGLE_TIME"

# Check for service-account.json
echo "Checking for Firebase credentials..."
if [ -f "service-account.json" ]; then
    echo "Firebase credentials found."
else
    echo "Warning: Firebase credentials (service-account.json) not found in root directory!"
    echo "Please place your Firebase service account JSON file in the root directory."
fi

# Set file permissions
echo "Setting file permissions..."
chmod -R 755 public/
chmod -R 755 writable/

echo "Setup complete!"
echo "To start the server, run: php spark serve"