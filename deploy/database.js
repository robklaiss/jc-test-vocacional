const sqlite3 = require('sqlite3').verbose();
const path = require('path');

// Create/connect to the SQLite database
const dbPath = path.join(__dirname, 'test_vocacional.db');
const db = new sqlite3.Database(dbPath, (err) => {
  if (err) {
    console.error('Error connecting to SQLite database:', err.message);
  } else {
    console.log('Connected to SQLite database');
    
    // Initialize the database schema if it doesn't exist
    initializeDatabase();
  }
});

// Initialize the database schema
function initializeDatabase() {
  // Create test_results table if it doesn't exist
  db.run(`CREATE TABLE IF NOT EXISTS test_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id TEXT,
    answer_pattern TEXT,
    scores TEXT,
    primary_route TEXT,
    secondary_route TEXT,
    answer_breakdown TEXT,
    formula_details TEXT,
    date_created DATETIME DEFAULT CURRENT_TIMESTAMP,
    ip_address TEXT,
    user_agent TEXT,
    referrer TEXT
  )`, (err) => {
    if (err) {
      console.error('Error creating test_results table:', err.message);
    } else {
      console.log('Test results table created or already exists');

      // Ensure newer columns exist for backward compatibility
      const additionalColumns = [
        { name: 'answer_breakdown', type: 'TEXT' },
        { name: 'formula_details', type: 'TEXT' }
      ];
      additionalColumns.forEach(({ name, type }) => {
        db.run(`ALTER TABLE test_results ADD COLUMN ${name} ${type}`, (alterErr) => {
          if (alterErr && !/duplicate column|already exists/i.test(alterErr.message)) {
            console.error(`Error adding column ${name}:`, alterErr.message);
          }
        });
      });
    }
  });
}

// Save test result to database
function saveTestResult(result) {
  return new Promise((resolve, reject) => {
    const { user_id, answer_pattern, scores, primary_route, secondary_route, answer_breakdown, formula_details, ip_address, user_agent, referrer } = result;
    
    // Convert objects to JSON strings for storage
    const answer_pattern_json = JSON.stringify(answer_pattern || []);
    const scores_json = JSON.stringify(scores || {});
    
    const sql = `INSERT INTO test_results (
      user_id, answer_pattern, scores, primary_route, secondary_route, 
      answer_breakdown, formula_details, ip_address, user_agent, referrer
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`;
    
    db.run(sql, [
      user_id,
      answer_pattern_json,
      scores_json,
      primary_route || '',
      secondary_route || '',
      answer_breakdown || '',
      formula_details || '',
      ip_address || '',
      user_agent || '',
      referrer || ''
    ], function(err) {
      if (err) {
        console.error('Error saving test result:', err.message);
        reject(err);
      } else {
        console.log(`Test result saved with ID: ${this.lastID}`);
        resolve(this.lastID);
      }
    });
  });
}

// Get test results for a specific user
function getResultsByUserId(userId) {
  return new Promise((resolve, reject) => {
    db.all('SELECT * FROM test_results WHERE user_id = ? ORDER BY date_created DESC', [userId], (err, rows) => {
      if (err) {
        console.error('Error getting test results:', err.message);
        reject(err);
      } else {
        resolve(rows);
      }
    });
  });
}

// Get metrics: tests completed today
function getTestsCompletedToday() {
  return new Promise((resolve, reject) => {
    const today = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format
    
    db.get(`SELECT COUNT(*) as count FROM test_results 
            WHERE date(date_created) = ?`, [today], (err, row) => {
      if (err) {
        console.error('Error getting daily metrics:', err.message);
        reject(err);
      } else {
        resolve(row.count);
      }
    });
  });
}

// Get metrics: tests completed yesterday
function getTestsCompletedYesterday() {
  return new Promise((resolve, reject) => {
    const yesterday = new Date();
    yesterday.setDate(yesterday.getDate() - 1);
    const yesterdayStr = yesterday.toISOString().split('T')[0];
    
    db.get(`SELECT COUNT(*) as count FROM test_results 
            WHERE date(date_created) = ?`, [yesterdayStr], (err, row) => {
      if (err) {
        console.error('Error getting yesterday metrics:', err.message);
        reject(err);
      } else {
        resolve(row.count);
      }
    });
  });
}

// Get metrics: tests completed in the last 7 days
function getTestsCompletedThisWeek() {
  return new Promise((resolve, reject) => {
    const weekAgo = new Date();
    weekAgo.setDate(weekAgo.getDate() - 7);
    const weekAgoStr = weekAgo.toISOString();
    
    db.get(`SELECT COUNT(*) as count FROM test_results 
            WHERE date_created >= ?`, [weekAgoStr], (err, row) => {
      if (err) {
        console.error('Error getting weekly metrics:', err.message);
        reject(err);
      } else {
        resolve(row.count);
      }
    });
  });
}

// Get metrics: tests completed in the last 30 days
function getTestsCompletedThisMonth() {
  return new Promise((resolve, reject) => {
    const monthAgo = new Date();
    monthAgo.setDate(monthAgo.getDate() - 30);
    const monthAgoStr = monthAgo.toISOString();
    
    db.get(`SELECT COUNT(*) as count FROM test_results 
            WHERE date_created >= ?`, [monthAgoStr], (err, row) => {
      if (err) {
        console.error('Error getting monthly metrics:', err.message);
        reject(err);
      } else {
        resolve(row.count);
      }
    });
  });
}

// Get all metrics in a single call
function getAllMetrics() {
  return new Promise(async (resolve, reject) => {
    try {
      const testsToday = await getTestsCompletedToday();
      const testsYesterday = await getTestsCompletedYesterday();
      const testsThisWeek = await getTestsCompletedThisWeek();
      const testsThisMonth = await getTestsCompletedThisMonth();
      
      resolve({
        testsToday,
        testsYesterday,
        testsThisWeek,
        testsThisMonth
      });
    } catch (error) {
      reject(error);
    }
  });
}

// Get all test results, ordered by date descending (most recent first)
function getAllResults() {
  return new Promise((resolve, reject) => {
    const sql = `SELECT * FROM test_results ORDER BY date_created DESC`;
    
    db.all(sql, [], (err, rows) => {
      if (err) {
        reject(err);
      } else {
        resolve(rows);
      }
    });
  });
}

// Close the database connection when the application exits
process.on('SIGINT', () => {
  db.close((err) => {
    if (err) {
      console.error('Error closing database:', err.message);
    } else {
      console.log('Database connection closed');
    }
    process.exit(0);
  });
});

module.exports = {
  saveTestResult,
  getResultsByUserId,
  getTestsCompletedToday,
  getTestsCompletedYesterday,
  getTestsCompletedThisWeek,
  getTestsCompletedThisMonth,
  getAllMetrics,
  getAllResults
};
