// config.js - Configuration settings for the vocational test application
const config = {
  // API endpoints
  api: {
    // Local development with separate PHP server
    local: {
      testResults: 'http://localhost:8080/api/mysql_sessions_fetch.php'
    },
    // AWS LAMP production environment
    production: {
      testResults: '/api/mysql_sessions_fetch.php'
    }
  },
  
  // Set the current environment
  // Change this to 'production' when deploying to AWS
  environment: 'local'
};

// Export the configuration for the current environment
const currentConfig = {
  api: config.api[config.environment]
};

// Make configuration available globally
window.APP_CONFIG = currentConfig;
