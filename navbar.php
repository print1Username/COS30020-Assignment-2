<?php
/*
 * Navbar PHP Component
 * Handles PHP Session and passes login status to navbar.js.
 */

if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

$isLoggedIn = isset($_SESSION["logged_in"]) && $_SESSION["logged_in"] === true;
?>

<div id="navbar-container"></div>

<script>
    // Pass PHP Session login status to navbar.js
    window.isLoggedIn = <?= $isLoggedIn ? "true" : "false" ?>;
</script>

<script src="components/navbar.js"></script>