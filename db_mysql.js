// db_mysql.js
// Additive MySQL session persistence for Vocational Test (DO_NOT_BREAK.md compliant)

const mysql = require('mysql2/promise');
const config = require('./mysql.config.js');

const pool = mysql.createPool(config);

async function saveSessionToMySQL(sessionId, state) {
  const jsonState = JSON.stringify(state);
  console.log('Saving to MySQL:', jsonState); // <-- Add this line
  const sql = 'INSERT INTO sessions (session_id, state, created_at) VALUES (?, ?, NOW())';
  await pool.execute(sql, [sessionId, jsonState]);
}

async function loadSessionFromMySQL(sessionId) {
  const [rows] = await pool.execute('SELECT state FROM sessions WHERE session_id = ? ORDER BY created_at DESC LIMIT 1', [sessionId]);
  if (!rows.length) return null;
  const state = rows[0].state;
  return typeof state === 'string' ? JSON.parse(state) : state;
}

async function fetchAllSessionsFromMySQL() {
  const [rows] = await pool.execute('SELECT * FROM sessions ORDER BY created_at DESC LIMIT 100');
  return rows.map(row => ({
    ...row,
    state: typeof row.state === 'string' ? JSON.parse(row.state) : row.state
  }));
}

module.exports = {
  saveSessionToMySQL,
  loadSessionFromMySQL,
  fetchAllSessionsFromMySQL
};
