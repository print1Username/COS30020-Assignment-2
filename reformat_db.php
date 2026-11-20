<?php
/*
 * ============================================================
 * WARNING: DESTRUCTIVE DATABASE OPERATION
 * ============================================================
 *
 * This script will permanently delete ALL existing tables
 * and their data from the cos30020a2 database.
 *
 * IMPORTANT:
 * 1. You MUST back up the entire database before running.
 * 2. Deleted tables and data CANNOT be recovered by this script.
 * 3. Without a valid backup, deleted data may be permanently lost.
 * 4. The script will then run db_connect.php to recreate the
 *    database tables and db_setup.php to initialize default data.
 *
 * Run this script manually through the PHP CLI only.
 *
 * php reformat_db.php
 *
 * ============================================================
 */
if ( PHP_SAPI !== 'cli' ) {
	http_response_code(403);
	exit('CLI only.');
}

$pdo = new PDO(
	'mysql:host=localhost;charset=utf8mb4',
	'root',
	'',
	[ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ]
);
$pdo -> exec('CREATE DATABASE IF NOT EXISTS cos30020a2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$pdo -> exec('USE cos30020a2');

$tables = $pdo -> query('SHOW FULL TABLES WHERE Table_type = \'BASE TABLE\'') -> fetchAll(PDO::FETCH_NUM);
$pdo -> exec('SET FOREIGN_KEY_CHECKS = 0');
try {
	foreach ( $tables as $table ) {
		$name = str_replace('`', '``', $table[0]);
		$pdo -> exec("DROP TABLE IF EXISTS `$name`");
		echo "Dropped: $name\n";
	}
} finally {
	$pdo -> exec('SET FOREIGN_KEY_CHECKS = 1');
}

// Recreate the schema, then seed the existing sample data.
require __DIR__.'/db_connect.php';
define('DB_INITIALIZE_DUMMY_DATA', true);
require __DIR__.'/db_setup.php';
echo "Database setup completed.\n";
