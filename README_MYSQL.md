# MySQL Additive Integration for Vocational Test (SAFE/DO_NOT_BREAK.md)

## Setup Steps

1. **Create MySQL Database & User**
   - Log into MySQL shell:
     ```sh
     mysql -u root -p
     ```
   - Then run:
     ```sql
     CREATE DATABASE test_vocacional CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
     CREATE USER 'vocacional_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';
     GRANT ALL PRIVILEGES ON test_vocacional.* TO 'vocacional_user'@'localhost';
     FLUSH PRIVILEGES;
     ```

2. **Create Sessions Table**
   - Run the contents of `sessions.sql` in your MySQL shell.

3. **Configure Local Connection**
   - Copy `mysql.config.example.js` to `mysql.config.js` and fill in your credentials.

4. **Additive Integration**
   - Use only the new `db_mysql.js` for MySQL session persistence. Legacy logic is untouched.
   - Dual-write and dual-read logic should be implemented in new, isolated functions.

5. **Deployment**
   - Upload all new files to your production server via FTP.
   - On production, create the same database, user, and table as above.
   - Update `mysql.config.js` on the server with production credentials.

## DO_NOT_BREAK.md Compliance
- No changes to existing test logic or UI flow.
- All new features are additive and backward compatible.
- Legacy storage (localStorage/sessionStorage/SQLite) is untouched.
- All changes are isolated and safe for end-to-end testing.
