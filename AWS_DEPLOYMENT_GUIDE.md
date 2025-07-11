# AWS LAMP Deployment Guide for Vocational Test Dashboard

This guide explains how to deploy the Vocational Test Dashboard on an AWS LAMP (Linux, Apache, MySQL, PHP) server.

## Prerequisites

1. An AWS EC2 instance with LAMP stack installed
2. SSH access to your EC2 instance
3. MySQL database credentials
4. Domain name (optional)

## Step 1: Set Up MySQL Database

1. Connect to your MySQL server:
   ```
   mysql -u root -p
   ```

2. Create a database and user:
   ```sql
   CREATE DATABASE test_vocacional;
   CREATE USER 'vocacional_user'@'localhost' IDENTIFIED BY 'your_secure_password';
   GRANT ALL PRIVILEGES ON test_vocacional.* TO 'vocacional_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. Create the sessions table:
   ```sql
   USE test_vocacional;
   CREATE TABLE sessions (
     id INT AUTO_INCREMENT PRIMARY KEY,
     session_id VARCHAR(255) NOT NULL,
     state JSON,
     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
   );
   ```

## Step 2: Configure MySQL Connection

1. Create or update the `mysql.config.js` file with your AWS MySQL credentials:
   ```javascript
   module.exports = {
     host: 'localhost',
     user: 'vocacional_user',
     password: 'your_secure_password',
     database: 'test_vocacional',
     waitForConnections: true,
     connectionLimit: 10,
     queueLimit: 0
   };
   ```

## Step 3: Deploy Files to AWS

1. Upload all project files to your Apache document root (typically `/var/www/html/` or a subdirectory):
   ```
   scp -r /path/to/test-vocacional/* user@your-ec2-instance:/var/www/html/
   ```

2. Ensure proper permissions:
   ```
   ssh user@your-ec2-instance "chmod -R 755 /var/www/html/"
   ```

## Step 4: Configure Apache

1. Make sure Apache is configured to serve PHP files and has the necessary modules enabled:
   ```
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. Create or update the Apache virtual host configuration (if needed):
   ```
   sudo nano /etc/apache2/sites-available/000-default.conf
   ```

   Add or modify:
   ```
   <VirtualHost *:80>
       ServerName yourdomain.com
       DocumentRoot /var/www/html
       
       <Directory /var/www/html>
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog ${APACHE_LOG_DIR}/error.log
       CustomLog ${APACHE_LOG_DIR}/access.log combined
   </VirtualHost>
   ```

3. Restart Apache:
   ```
   sudo systemctl restart apache2
   ```

## Step 5: Test the Deployment

1. Access your dashboard at:
   ```
   http://your-ec2-instance-ip/dashboard.html
   ```
   or if you have a domain:
   ```
   http://yourdomain.com/dashboard.html
   ```

## Troubleshooting

1. **MySQL Connection Issues**:
   - Check your MySQL credentials in `mysql.config.js`
   - Verify MySQL is running: `sudo systemctl status mysql`
   - Check MySQL logs: `sudo tail -f /var/log/mysql/error.log`

2. **PHP Errors**:
   - Check Apache error logs: `sudo tail -f /var/log/apache2/error.log`
   - Enable PHP error display in `php.ini` for debugging

3. **Dashboard Not Loading Data**:
   - Open browser developer tools and check the console for errors
   - Verify the API endpoint `/api/mysql_sessions_fetch.php` is accessible
   - Check that CORS headers are properly set in your PHP files

## Security Considerations

1. **Protect MySQL Credentials**:
   - Ensure `mysql.config.js` is not directly accessible via web
   - Add to `.htaccess`: 
     ```
     <Files "mysql.config.js">
       Order allow,deny
       Deny from all
     </Files>
     ```

2. **Use HTTPS**:
   - Install SSL certificate using Let's Encrypt
   - Configure Apache to redirect HTTP to HTTPS

3. **Secure API Endpoints**:
   - Consider adding authentication to sensitive API endpoints
