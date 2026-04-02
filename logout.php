<?php
// 1. Initialize the session to access existing session data
session_start();

/**
 * Session Termination:
 * session_destroy() removes all data associated with the current session.
 * This effectively logs the user out by clearing their ID and Cart.
 */
session_destroy();

/**
 * Redirection:
 * Send the user back to the Login page immediately after logging out.
 */
header("Location: login.php");

// Stop script execution to ensure the redirect happens safely
exit();
?>