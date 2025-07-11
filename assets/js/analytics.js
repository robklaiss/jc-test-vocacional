// analytics.js
// Analytics tracking for the vocational test

const ANALYTICS_ENDPOINT = 'api/analytics.php';

/**
 * Track a test session event
 * @param {string} eventType - 'started', 'progress', 'completed'
 * @param {object} data - Additional event data
 */
export async function trackTestEvent(eventType, data = {}) {
    try {
        // Get or create session ID
        let sessionId = sessionStorage.getItem('test_session_id');
        if (!sessionId) {
            sessionId = 'test_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('test_session_id', sessionId);
        }

        // Prepare event data
        const eventData = {
            session_id: sessionId,
            event_type: eventType,
            timestamp: new Date().toISOString(),
            ...data
        };

        // Send to analytics endpoint
        const response = await fetch(ANALYTICS_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(eventData)
        });

        return await response.json();
    } catch (error) {
        console.error('Analytics error:', error);
        return { success: false, error: error.message };
    }
}

/**
 * Track test start
 * @param {number} totalQuestions - Total number of questions in the test
 */
export async function trackTestStart(totalQuestions) {
    return trackTestEvent('started', { total_questions: totalQuestions });
}

/**
 * Track test progress
 * @param {number} currentQuestion - Current question number
 * @param {number} totalQuestions - Total number of questions
 */
export async function trackTestProgress(currentQuestion, totalQuestions) {
    return trackTestEvent('progress', {
        current_question: currentQuestion,
        total_questions: totalQuestions,
        progress: Math.round((currentQuestion / totalQuestions) * 100)
    });
}

/**
 * Track test completion
 * @param {object} result - Test result data
 * @param {number} timeSpent - Time spent on test in seconds
 */
export async function trackTestCompletion(result, timeSpent) {
    return trackTestEvent('completed', {
        result_data: result,
        time_spent: timeSpent,
        completed_at: new Date().toISOString()
    });
}

/**
 * Get analytics summary
 * @returns {Promise<object>} Analytics data
 */
export async function getAnalyticsSummary() {
    try {
        const response = await fetch(ANALYTICS_ENDPOINT);
        const data = await response.json();
        
        if (data.success) {
            return data.data;
        }
        
        throw new Error(data.error || 'Failed to fetch analytics');
    } catch (error) {
        console.error('Failed to load analytics:', error);
        // Fallback to empty data
        return {
            total_visitors: 0,
            total_completions: 0,
            daily_metrics: [],
            weekly_metrics: [],
            monthly_metrics: []
        };
    }
}

// Initialize analytics
document.addEventListener('DOMContentLoaded', () => {
    // Track page view
    trackTestEvent('page_view', {
        page: window.location.pathname,
        referrer: document.referrer
    });
});
