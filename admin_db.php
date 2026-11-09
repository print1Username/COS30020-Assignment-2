<?php
session_start();

if (isset($_GET["logout"])) {
	session_unset();
	session_destroy();
	header("Location: login.php");
	exit;
}

if (($_SESSION["logged_in"] ?? false) !== true ||
	($_SESSION["type"] ?? null) !== "admin" ||
	!isset($_SESSION["email"])) {
	header("Location: login.php");
	exit;
}

require_once __DIR__."/db_setup.php";
require_once __DIR__."/db_connect.php";

function adminEscape($value): string
{
	return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function adminToken(): string
{
	if (!isset($_SESSION["admin_token"])) {
		$_SESSION["admin_token"] = bin2hex(random_bytes(32));
	}
	return $_SESSION["admin_token"];
}

function adminCheckPost(): void
{
	if (!hash_equals(adminToken(), (string)($_POST["token"] ?? ""))) {
		http_response_code(403);
		exit("Invalid request token.");
	}
}

function adminRedirect(string $page, string $message): void
{
	$_SESSION["admin_notice"] = $message;
	header("Location: ".$page);
	exit;
}
