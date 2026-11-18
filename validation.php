<?php

function isValidEmail(string $email, int $maxLength = 50): bool
{
	return $email !== "" && strlen($email) <= $maxLength &&
		filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidName(string $name, int $maxLength = 50): bool
{
	return preg_match('/^[A-Za-z ]{1,'. $maxLength .'}$/', $name) === 1;
}

function isValidDate(string $date): bool
{
	$parsed = DateTime::createFromFormat("!Y-m-d", $date);
	$errors = DateTime::getLastErrors();
	return $parsed !== false &&
		($errors === false || ($errors["warning_count"] === 0 && $errors["error_count"] === 0)) &&
		$parsed->format("Y-m-d") === $date;
}

function isValidTime(string $time): bool
{
	return preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $time) === 1;
}
