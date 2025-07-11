// mysql.config.example.js
// Copy this file to mysql.config.js and fill in your credentials
module.exports = {
  host: 'localhost',
  user: 'vocacional_user',
  password: 'your_strong_password_here',
  database: 'test_vocacional',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
};
