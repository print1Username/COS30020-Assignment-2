<?php
require_once __DIR__."/auth.php";
require_once __DIR__."/helpers.php";
startSession();

if (isset($_GET["logout"])) {
	session_unset();
	session_destroy();
	header("Location: login.php");
	exit;
}

requireAdmin();

require_once __DIR__."/db_connect.php";

function adminEscape($value): string
{
	return e($value);
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

function adminFormatDate(?string $value): string
{
	$timestamp = $value ? strtotime($value) : false;
	return $timestamp === false ? "" : date("d M Y", $timestamp);
}

function adminFormatTime(?string $value): string
{
	$timestamp = $value ? strtotime($value) : false;
	return $timestamp === false ? "" : date("g:i A", $timestamp);
}

function adminFormatDateTime(?string $value): string
{
	$timestamp = $value ? strtotime($value) : false;
	return $timestamp === false ? "" : date("d M Y, g:i A", $timestamp);
}
