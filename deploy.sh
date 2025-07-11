#!/bin/bash

# Create a clean deployment directory
echo "🚀 Preparing deployment..."
rm -rf deploy
mkdir -p deploy

# Copy all files except those in .ftpignore
echo "📦 Copying files..."
rsync -a --exclude-from=.ftpignore --exclude='.git/' --exclude='deploy/' . deploy/

# Create a zip archive for easy transfer
echo "📦 Creating archive..."
cd deploy
zip -r ../deploy.zip .
cd ..

echo "✅ Deployment package ready at: $(pwd)/deploy/"
echo "📦 Or use the zip file: $(pwd)/deploy.zip"
echo "\nYou can now upload the contents of the 'deploy' directory or the 'deploy.zip' file via FTP."

echo "\n📝 Files to be deployed (first 10):"
find deploy -type f | head -n 10
echo "... and more"

echo "\n📊 Deployment size:"
du -sh deploy
du -sh deploy.zip
