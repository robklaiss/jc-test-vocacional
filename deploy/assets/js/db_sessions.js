// db_sessions.js
// Additive, isolated session persistence logic for Vocational Test (DO NOT BREAK guideline)
// This module handles saving/restoring sessions to the backend via api/session.php

const DB_SESSIONS_API = 'api/session.php';

/**
 * Save session state to SQLite via API (additive, does not remove localStorage)
 * @param {string} sessionId - Unique session identifier
 * @param {object} state - Session state object to persist
 * @returns {Promise<boolean>} success
 */
export async function saveSessionToDb(sessionId, state) {
    try {
        const res = await fetch(DB_SESSIONS_API, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({session_id: sessionId, state})
        });
        const data = await res.json();
        return data.success === true;
    } catch (e) {
        console.warn('DB session save failed:', e);
        return false;
    }
}

/**
 * Restore session state from SQLite via API (additive, fallback to localStorage)
 * @param {string} sessionId - Unique session identifier
 * @returns {Promise<object|null>} Session state or null if not found
 */
export async function loadSessionFromDb(sessionId) {
    try {
        const res = await fetch(`${DB_SESSIONS_API}?session_id=${encodeURIComponent(sessionId)}`);
        const data = await res.json();
        if (data && data.state) {
            return JSON.parse(data.state);
        }
        return null;
    } catch (e) {
        console.warn('DB session load failed:', e);
        return null;
    }
}

/**
 * Fetch all recent sessions for dashboard analytics (additive)
 * @returns {Promise<Array>} Array of session objects
 */
export async function fetchAllSessions() {
    try {
        const res = await fetch(DB_SESSIONS_API);
        const data = await res.json();
        return Array.isArray(data) ? data : [];
    } catch (e) {
        console.warn('DB fetchAllSessions failed:', e);
        return [];
    }
}
