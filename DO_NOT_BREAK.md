# 🚨 DO NOT BREAK THE TEST! — Safe Development Guideline

## Purpose

This document is a strict protocol for making changes to the Vocational Test application, especially regarding session persistence in SQLite and dashboard integration. Follow these steps to **guarantee** that the current, stable test flow is never broken.

---

## 1. General Principles

- **Do not change existing test logic or UI flow unless absolutely necessary.**
- **All new features (sessions in SQLite, dashboard display) must be additive and backward compatible.**
- **Never remove or alter localStorage/sessionStorage logic until SQLite flow is 100% verified.**
- **All changes must be tested end-to-end (start → answer → result → restart → dashboard) before merging.**

---

## 2. Safe Session Persistence Protocol

1. **Additive Only:**  
   - When saving sessions to SQLite, do not remove or disable localStorage/sessionStorage.  
   - Implement a dual-write: save to both localStorage/sessionStorage (for legacy) and SQLite (for new).
   - Reads should prefer SQLite if available, but always fallback to localStorage/sessionStorage if not.

2. **Non-Destructive Migration:**  
   - Never clear or overwrite existing localStorage/sessionStorage data unless the user explicitly restarts.
   - When reading sessions for the dashboard, always check both SQLite and localStorage/sessionStorage for legacy data.

3. **Isolation:**  
   - All new database code should be encapsulated in new functions/files (`db_sessions.js`, `api/session.php`, etc.).
   - Do not mix new DB logic with legacy code until fully tested.

---

## 3. Dashboard Integration

- Fetch and display sessions from SQLite, but always verify that legacy dashboard data is still shown if DB fetch fails.
- Add clear error handling: if DB is unavailable, show a warning but do not break the dashboard.
- Always log API errors and unexpected data for debugging.

---

## 4. Testing and Rollback

- Before merging, run full manual tests for:
  - New session save
  - Session restore on reload
  - Dashboard session display
  - Restart flow
  - Legacy flow (with DB disabled)
- If any test fails, **revert immediately** to the last known good version.
- Always keep a backup of the last stable release.

---

## 5. Code Review Checklist

- [ ] No legacy code removed or disabled
- [ ] All new DB/session code is additive and isolated
- [ ] Full error handling and fallback to legacy
- [ ] Manual end-to-end test results attached
- [ ] Rollback plan in place

---

## 6. Dashboard CSS and Styling

- **DO NOT modify the CSS or layout of the dashboard-fixed.html file.**
- **The current styling and layout of the dashboard-fixed.html is final and approved.**
- **Any future functionality changes must preserve the existing visual design.**
- **When fixing JavaScript or functionality issues, ensure the CSS remains unchanged.**
- **Use the dashboard-fixed.html as the reference for all dashboard styling.**

---

## 7. For Cascade and All Contributors

**Do not proceed with any session or dashboard changes unless you strictly follow this protocol.  
If unsure, ask for review before merging.  
When in doubt, prioritize stability over new features.**
