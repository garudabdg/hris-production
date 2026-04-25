#!/bin/bash

# Fix permissions untuk folder uploads dan subfoldernya
chmod -R 755 /var/www/hris.didimax.online/storage/app/public/uploads/
chmod -R 644 /var/www/hris.didimax.online/storage/app/public/uploads/*/
chmod -R 755 /var/www/hris.didimax.online/storage/app/public/uploads/*/

# Pastikan www-data adalah owner
chown -R www-data:www-data /var/www/hris.didimax.online/storage/app/public/uploads/

echo "Permissions fixed!"
ls -la /var/www/hris.didimax.online/storage/app/public/uploads/sid/
