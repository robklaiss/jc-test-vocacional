const express = require('express');
const path = require('path');
const bodyParser = require('body-parser');
const cookieParser = require('cookie-parser');
const db = require('./database');

const app = express();
const PORT = process.env.PORT || 3001;

// Middleware
app.use(bodyParser.json());
app.use(bodyParser.urlencoded({ extended: true }));
app.use(cookieParser());
app.use(express.static(path.join(__dirname, '/')));

// Routes for static HTML files
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'jc_test_vocacional.html'));
});

app.get('/dashboard', (req, res) => {
  res.sendFile(path.join(__dirname, 'dashboard.html'));
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

    // Add additional data
    const resultData = {
      user_id,
      answer_pattern,
      scores,
      primary_route,
      secondary_route,
      answer_breakdown: answerBreakdown || '',
      formula_details: formulaDetails || '',
      ip_address: req.ip,
      user_agent: req.headers['user-agent'],
      referrer: req.headers.referer || req.headers.referrer || ''
    };

    const resultId = await db.saveTestResult(resultData);
    res.status(201).json({ success: true, resultId });
  } catch (error) {
    console.error('Error saving result:', error);
    res.status(500).json({ success: false, error: error.message });
  }
});

// API endpoint to get metrics for dashboard
app.get('/api/metrics', async (req, res) => {
  try {
    const metrics = await db.getAllMetrics();
    res.json(metrics);
  } catch (error) {
    console.error('Error getting metrics:', error);
    res.status(500).json({ error: error.message });
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

// Start the server
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
  console.log(`Dashboard available at http://localhost:${PORT}/dashboard`);
  console.log(`Test available at http://localhost:${PORT}/`);
});
