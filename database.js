const duckdb = require('duckdb');
const path = require('path');

// Database file path
const dbPath = path.join(__dirname, 'test_vocacional.duckdb');

// Initialize DuckDB database
const db = new duckdb.Database(dbPath, (err) => {
  if (err) {
    console.error('Error connecting to DuckDB database:', err);
    process.exit(1);
  }
  console.log(`Connected to DuckDB database at ${dbPath}`);
  
  // Initialize the database schema
  initializeDatabase();
});

// Initialize the database schema
function initializeDatabase() {
  // Create test_results table if it doesn't exist
  db.run(`
    CREATE TABLE IF NOT EXISTS test_results (
      id INTEGER PRIMARY KEY,
      user_id TEXT,
      answer_pattern TEXT,
      scores TEXT,
      primary_route TEXT,
      secondary_route TEXT,
      answer_breakdown TEXT,
      formula_details TEXT,
      date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      ip_address TEXT,
      user_agent TEXT,
      referrer TEXT
    )
  `, (err) => {
    if (err) {
      console.error('Error creating test_results table:', err);
    } else {
      console.log('Test results table created or already exists');
      // Add any missing columns
      const columnsToAdd = [
        { name: 'answer_breakdown', type: 'TEXT' },
        { name: 'formula_details', type: 'TEXT' },
        { name: 'ip_address', type: 'TEXT' },
        { name: 'user_agent', type: 'TEXT' },
        { name: 'referrer', type: 'TEXT' }
      ];
      columnsToAdd.forEach(({ name, type }) => {
        db.run(`ALTER TABLE test_results ADD COLUMN IF NOT EXISTS ${name} ${type}`, (err) => {
          if (err) {
            console.error(`Error adding column ${name}:`, err.message);
          } else {
            console.log(`Column ${name} exists or was added successfully`);
          }
        });
      });
    }
  });
}

// Save test result to database
function saveTestResult(result) {
  const { user_id, answer_pattern, scores, primary_route, secondary_route, answer_breakdown, formula_details, ip_address, user_agent, referrer } = result;
  
  // Ensure answer_pattern and scores are properly stringified if they're objects
  const answer_pattern_json = typeof answer_pattern === 'string' ? answer_pattern : JSON.stringify(answer_pattern || []);
  const scores_json = typeof scores === 'string' ? scores : JSON.stringify(scores || {});

  return new Promise((resolve, reject) => {
    // First, insert the data
    db.run(
      `INSERT INTO test_results (
        user_id, answer_pattern, scores, primary_route, secondary_route, 
        answer_breakdown, formula_details, ip_address, user_agent, referrer
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
      [
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
      ],
      function (err) {
        if (err) {
          console.error('Error saving test result:', err);
          return reject(err);
        }
        
        // Get the last inserted row ID
        db.all('SELECT last_insert_rowid() as id', [], (err, rows) => {
          if (err) {
            console.error('Error getting last insert ID:', err);
            // Still resolve since the insert was successful
            console.log('Test result saved, but could not get last insert ID');
            return resolve({ success: true });
          }
          
          const lastId = rows && rows[0] ? rows[0].id : null;
          console.log(`Test result saved with ID: ${lastId}`);
          resolve({ success: true, lastInsertRowid: lastId });
        });
      }
    );
  });
}

// Get test results for a specific user
function getResultsByUserId(userId) {
  return new Promise((resolve, reject) => {
    // DuckDB uses $1, $2, etc. for parameter binding instead of ?
    db.all('SELECT * FROM test_results WHERE user_id = $1 ORDER BY date_created DESC', [userId], (err, rows) => {
      if (err) {
        console.error('Error getting test results:', err);
        return reject(err);
      }
      
      // Ensure we always return an array, even if no results
      resolve(rows || []);
    });
  });
}

// Helper function to get a single value from a query
function getSingleValue(sql, params = []) {
  return new Promise((resolve, reject) => {
    db.all(sql, params, (err, rows) => {
      if (err) {
        console.error('Error in getSingleValue:', err);
        resolve(0);
      } else {
        if (rows && rows.length > 0) {
          resolve(Object.values(rows[0])[0]);
        } else {
          resolve(0);
        }
      }
    });
  });
}

// Get metrics: tests completed today
async function getTestsCompletedToday() {
  const today = new Date().toISOString().split('T')[0]; // Get current date in YYYY-MM-DD format
  return await getSingleValue(
    "SELECT COUNT(*) as count FROM test_results WHERE CAST(date_created AS DATE) = ?",
    [today]
  );
}

// Get metrics: tests completed yesterday
async function getTestsCompletedYesterday() {
  const yesterday = new Date();
  yesterday.setDate(yesterday.getDate() - 1);
  const yesterdayStr = yesterday.toISOString().split('T')[0];
  return await getSingleValue(
    "SELECT COUNT(*) as count FROM test_results WHERE CAST(date_created AS DATE) = ?",
    [yesterdayStr]
  );
}

// Get metrics: tests completed in the last 7 days
async function getTestsCompletedThisWeek() {
  const weekAgo = new Date();
  weekAgo.setDate(weekAgo.getDate() - 7);
  const weekAgoStr = weekAgo.toISOString();
  return await getSingleValue(
    "SELECT COUNT(*) as count FROM test_results WHERE date_created >= ?",
    [weekAgoStr]
  );
}

// Get metrics: tests completed in the last 30 days
async function getTestsCompletedThisMonth() {
  const monthAgo = new Date();
  monthAgo.setDate(monthAgo.getDate() - 30);
  const monthAgoStr = monthAgo.toISOString();
  return await getSingleValue(
    "SELECT COUNT(*) as count FROM test_results WHERE date_created >= ?",
    [monthAgoStr]
  );
}

// Get all metrics in a single call
async function getAllMetrics() {
  const [testsToday, testsYesterday, testsThisWeek, testsThisMonth] = await Promise.all([
    getTestsCompletedToday(),
    getTestsCompletedYesterday(),
    getTestsCompletedThisWeek(),
    getTestsCompletedThisMonth()
  ]);
  return {
    testsToday,
    testsYesterday,
    testsThisWeek,
    testsThisMonth
  };
}

// Get all test results, ordered by date descending (most recent first)
function getAllResults() {
  return new Promise((resolve, reject) => {
    db.all('SELECT * FROM test_results ORDER BY date_created DESC', (err, rows) => {
      if (err) {
        console.error('Error getting all results:', err);
        return reject(err);
      }
      
      // Ensure we always return an array, even if no results
      resolve(rows || []);
    });
  });
}

// Close the database connection when the application exits
process.on('SIGINT', () => {
  db.close((err) => {
    if (err) {
      console.error('Error closing database connection:', err);
    } else {
      console.log('Database connection closed');
    }
    process.exit(0);
  });
});

// Export all database functions
module.exports = {
  saveTestResult,
  getResultsByUserId,
  getSingleValue,
  getTestsCompletedToday,
  getTestsCompletedYesterday,
  getTestsCompletedThisWeek,
  getTestsCompletedThisMonth,
  getAllMetrics,
  getAllResults
};
