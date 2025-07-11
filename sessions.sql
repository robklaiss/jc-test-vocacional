-- sessions.sql
-- Run this in your MySQL shell to create the sessions table
USE test_vocacional;
CREATE TABLE IF NOT EXISTS sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id VARCHAR(255) NOT NULL,
  state JSON NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
