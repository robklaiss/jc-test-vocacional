const express = require('express');
const bodyParser = require('body-parser');
const cookieParser = require('cookie-parser');
const path = require('path');
const cors = require('cors');

// Import database functions
const db = require('./database');

// Import MySQL database functions (if available)
let mysqlDb;
try {
  mysqlDb = require('./db_mysql');
  console.log('MySQL database module loaded successfully');
} catch (err) {
  console.warn('MySQL database module not available:', err.message);
  mysqlDb = null;
}

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(cors());
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, '/')));

// Store connected clients for SSE
const clients = new Set();

// Middleware to track connected clients
app.use((req, res, next) => {
  res.on('finish', () => {
    if (req.path === '/api/updates' && res.statusCode === 200) {
      clients.add(res);
      req.on('close', () => clients.delete(res));
    }
  });
  next();
});

// Function to broadcast updates to all connected clients
function broadcastUpdate() {
  const update = { type: 'refresh' };
  const message = `data: ${JSON.stringify(update)}\n\n`;
  
  clients.forEach(client => {
    try {
      client.write(message);
    } catch (err) {
      console.error('Error sending update to client:', err);
      clients.delete(client);
    }
  });
}

// Routes for static HTML files
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'jc_test_vocacional.html'));
});

// Dashboard routes
app.get('/dashboard', (req, res) => {
  res.sendFile(path.join(__dirname, 'dashboard.html'));
});

// Analytics dashboard route
app.get('/analytics-dashboard', (req, res) => {
  res.sendFile(path.join(__dirname, 'analytics-dashboard.html'), {
    headers: {
      'Content-Type': 'text/html; charset=UTF-8'
    }
  });
});

// API endpoint to save test results
app.post('/api/results', async (req, res) => {
  try {
    const {
      user_id,
      answer_pattern,
      scores,
      primary_route,
      secondary_route,
      answerBreakdown,
      formulaDetails
    } = req.body;

    try {
      // Add additional data
      const result = await db.saveTestResult({
        user_id,
        answer_pattern,
        scores: JSON.stringify(scores),
        primary_route,
        secondary_route,
        answer_breakdown: JSON.stringify(answerBreakdown),
        formula_details: JSON.stringify(formulaDetails),
        ip_address: req.ip,
        user_agent: req.headers['user-agent'],
        referrer: req.headers.referer
      });

      // Notify all connected clients about the new result
      broadcastUpdate();

      res.json({ 
        success: true, 
        result_id: result.lastInsertRowid || 'unknown'
      });
    } catch (error) {
      console.error('Error in saveTestResult:', error);
      throw error; // This will be caught by the outer try-catch
    }
  } catch (error) {
    console.error('Error saving test result:', error);
    res.status(500).json({ 
      success: false, 
      error: 'Failed to save test result',
      details: error.message 
    });
  }
});

// Helper function to format date as YYYY-MM-DD
function formatDate(date) {
  const d = new Date(date);
  return d.toISOString().split('T')[0];
}

// API endpoint to get metrics for dashboard
app.get('/api/metrics', async (req, res) => {
  try {
    const allResults = await db.getAllResults();
    
    // Calculate metrics
    const now = new Date();
    const today = formatDate(now);
    const yesterday = formatDate(new Date(now.setDate(now.getDate() - 1)));
    
    // Initialize metrics with default values
    const metrics = {
      total_tests: allResults.length,
      today: 0,
      yesterday: 0,
      this_week: 0,
      last_week: 0,
      this_month: 0,
      last_month: 0,
      result_distribution: {},
      daily_data: [],
      weekly_data: [],
      monthly_data: []
    };

    // Process all results
    allResults.forEach(result => {
      const resultDate = new Date(result.date_created);
      const resultDateStr = formatDate(resultDate);
      
      // Count tests by day
      if (resultDateStr === today) metrics.today++;
      if (resultDateStr === yesterday) metrics.yesterday++;
      
      // Count tests by week/month
      const weekAgo = new Date();
      weekAgo.setDate(weekAgo.getDate() - 7);
      const monthAgo = new Date();
      monthAgo.setMonth(monthAgo.getMonth() - 1);
      
      if (resultDate > weekAgo) metrics.this_week++;
      if (resultDate > monthAgo) metrics.this_month++;
      
      // Count result distribution
      const route = result.primary_route || 'unknown';
      metrics.result_distribution[route] = (metrics.result_distribution[route] || 0) + 1;
      
      // Prepare daily data
      const existingDay = metrics.daily_data.find(d => d.date === resultDateStr);
      if (existingDay) {
        existingDay.count++;
      } else {
        metrics.daily_data.push({
          date: resultDateStr,
          count: 1
        });
      }
    });
    
    // Sort daily data by date
    metrics.daily_data.sort((a, b) => new Date(a.date) - new Date(b.date));
    
    // Calculate weekly and monthly data from daily data
    const weeklyData = {};
    const monthlyData = {};
    
    metrics.daily_data.forEach(day => {
      const date = new Date(day.date);
      const week = `${date.getFullYear()}-W${Math.ceil((((date - new Date(date.getFullYear(), 0, 1)) / 86400000) + 1) / 7)}`;
      const month = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}`;
      
      weeklyData[week] = (weeklyData[week] || 0) + day.count;
      monthlyData[month] = (monthlyData[month] || 0) + day.count;
    });
    
    // Format weekly and monthly data
    metrics.weekly_data = Object.entries(weeklyData).map(([week, count]) => ({
      week,
      count
    }));
    
    metrics.monthly_data = Object.entries(monthlyData).map(([month, count]) => ({
      month,
      count
    }));
    
    res.json(metrics);
  } catch (error) {
    console.error('Error getting metrics:', error);
    res.status(500).json({ 
      success: false, 
      error: 'Failed to load metrics',
      details: error.message 
    });
  }
});

// API endpoint to get results for a user
app.get('/api/results/:userId', async (req, res) => {
  try {
    const userId = req.params.userId;
    const results = await db.getResultsByUserId(userId);
    res.json(results);
  } catch (error) {
    console.error('Error getting results for user:', error);
    res.status(500).json({ error: error.message });
  }
});

// API endpoint to get all results (for dashboard)
app.get('/api/results/all', async (req, res) => {
  try {
    const results = await db.getAllResults();
    res.json({
      success: true,
      data: results
    });
  } catch (error) {
    console.error('Error getting all results:', error);
    res.status(500).json({ 
      success: false,
      error: error.message 
    });
  }
});

// API endpoint to get MySQL sessions
app.get('/api/mysql_sessions_fetch.php', async (req, res) => {
  try {
    if (!mysqlDb) {
      throw new Error('MySQL database module not available');
    }
    
    const sessions = await mysqlDb.fetchAllSessionsFromMySQL();
    res.json({
      sessions: sessions
    });
  } catch (error) {
    console.error('Error getting MySQL sessions:', error);
    res.status(500).json({ 
      error: error.message 
    });
  }
});

// SSE endpoint for real-time updates
app.get('/api/updates', (req, res) => {
  try {
    // Set headers for SSE
    res.writeHead(200, {
      'Content-Type': 'text/event-stream',
      'Cache-Control': 'no-cache',
      'Connection': 'keep-alive',
      'X-Accel-Buffering': 'no' // Disable buffering for nginx
    });

    // Send a comment to keep the connection open
    res.write(':ok\n\n');

    // Handle client disconnect
    req.on('close', () => {
      clients.delete(res);
      res.end();
    });
  } catch (err) {
    console.error('SSE connection error:', err);
    res.status(500).end();
  }
});

// Start the server
app.listen(PORT, () => {
  console.log(`Server is running on port ${PORT}`);
  console.log(`Analytics dashboard: http://localhost:${PORT}/analytics-dashboard`);
  console.log(`Dashboard available at http://localhost:${PORT}/dashboard`);
  console.log(`Test available at http://localhost:${PORT}/`);
});

// Close the server gracefully on SIGINT
process.on('SIGINT', () => {
  console.log('Shutting down server...');
  if (server) {
    server.close(() => {
      console.log('Server closed');
      process.exit(0);
    });
  } else {
    process.exit(0);
  }
});
