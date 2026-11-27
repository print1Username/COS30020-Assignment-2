<?php

if ( PHP_SAPI !== "cli" ) {
	http_response_code(404);
	exit;
}

final class AdminAuthorizationTestStatement extends PDOStatement
{
	public function __construct(private string $case)
	{
	}

	public function execute(?array $params = null): bool
	{
		if ( $params !== [ "admin@example.test" ] ) {
			throw new LogicException("Admin email was not bound to the query.");
		}
		return true;
	}

	public function fetchColumn(int $column = 0): mixed
	{
		return match ( $this -> case ) {
			"existing-admin" => "admin",
			"admin-demotion" => "user",
			default => false
		};
	}
}

final class AdminAuthorizationTestPdo extends PDO
{
	public int $prepareCount = 0;

	public function __construct(private string $case)
	{
	}

	public function prepare(string $query, array $options = []): PDOStatement|false
	{
		$this -> prepareCount++;
		foreach ( [ "FROM user_table AS u", "INNER JOIN account_table AS a", "WHERE u.email = ?" ] as $part ) {
			if ( !str_contains($query, $part) ) {
				throw new LogicException("Authorization query does not validate both account tables.");
			}
		}
		return new AdminAuthorizationTestStatement($this -> case);
	}
}

if ( ($argv[1] ?? "") === "--case" ) {
	$case = $argv[2] ?? "";
	ini_set("session.use_cookies", "0");
	session_id(bin2hex(random_bytes(16)));
	session_start();
	if ( $case !== "unauthorized-after-invalidation" ) {
		$_SESSION = [
			"logged_in" => true,
			"type" => "admin",
			"email" => "admin@example.test"
		];
	}

	$pdo = new AdminAuthorizationTestPdo($case);
	require_once __DIR__."/../auth.php";
	$returned = false;
	register_shutdown_function(static function () use (&$returned, $case, $pdo): void {
		$sessionActive = session_status() === PHP_SESSION_ACTIVE;
		$sessionEmpty = ($_SESSION ?? []) === [];
		$authorized = $case === "existing-admin";
		$passed = $returned === $authorized &&
			$pdo -> prepareCount === ($case === "unauthorized-after-invalidation" ? 0 : 1);
		if ( $case === "existing-admin" ) {
			$passed = $passed && $sessionActive && ($_SESSION["type"] ?? null) === "admin";
		} elseif ( $case === "unauthorized-after-invalidation" ) {
			$passed = $passed && $sessionActive && $sessionEmpty;
		} else {
			$passed = $passed && !$sessionActive && $sessionEmpty;
		}
		if ( $sessionActive ) session_abort();
		echo $passed ? "PASS" : "FAIL";
	});

	requireAdmin();
	$returned = true;
	exit;
}

$cases = [
	"existing-admin" => "Existing Admin session",
	"admin-demotion" => "Admin account demotion",
	"admin-deletion" => "Admin account deletion",
	"unauthorized-after-invalidation" => "Unauthorized access after session invalidation"
];
$failures = 0;
foreach ( $cases as $case => $description ) {
	$command = escapeshellarg(PHP_BINARY)." ".escapeshellarg(__FILE__).
		" --case ".escapeshellarg($case);
	$output = [];
	$exitCode = 0;
	exec($command, $output, $exitCode);
	if ( $exitCode !== 0 || implode("\n", $output) !== "PASS" ) {
		fwrite(STDERR, "FAIL: ".$description."\n");
		$failures++;
		continue;
	}
	echo "PASS: ".$description."\n";
}

exit($failures === 0 ? 0 : 1);
