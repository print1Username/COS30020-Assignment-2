<?php

function startSession(): void
{
	if (session_status() !== PHP_SESSION_ACTIVE) {
		session_start();
	}
}

function requireUser(bool $allowAdmin = false): void
{
	startSession();
	$allowedTypes = $allowAdmin ? ["user", "admin"] : ["user"];
	if (($_SESSION["logged_in"] ?? false) !== true ||
		!in_array($_SESSION["type"] ?? null, $allowedTypes, true) ||
		!isset($_SESSION["email"])) {
		header("Location: login.php");
		exit;
	}
}

function requireAdmin(): void
{
	startSession();
	if (($_SESSION["logged_in"] ?? false) !== true ||
		($_SESSION["type"] ?? null) !== "admin" ||
		!isset($_SESSION["email"])) {
		header("Location: login.php");
		exit;
	}
}
