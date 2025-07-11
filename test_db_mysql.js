// test_db_mysql.js
// Quick test for additive MySQL session persistence

const { saveSessionToMySQL, loadSessionFromMySQL, fetchAllSessionsFromMySQL } = require('./db_mysql');

async function test() {
  const sessionId = 'test_session_' + Math.floor(Math.random() * 1000000);
  const state = { foo: 'bar', time: new Date().toISOString() };

  console.log('Saving session:', sessionId, state);
  await saveSessionToMySQL(sessionId, state);

  console.log('Loading session:', sessionId);
  const loaded = await loadSessionFromMySQL(sessionId);
  console.log('Loaded:', loaded);

  console.log('Fetching all sessions (showing up to 2):');
  const all = await fetchAllSessionsFromMySQL();
  console.log(all.slice(0, 2));
}

test().catch(console.error);
