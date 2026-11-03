
<?php
$host = "localhost";
$dbName = "cos30020a2";
$dbUser = "root";
$dbPass = "";

try {
    // Connect to MySQL server
    $pdo = new PDO(
        "mysql:host=$host;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Create database automatically
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `$dbName`
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci
    ");

    // Select database
    $pdo->exec("USE `$dbName`");

    // 1. User table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS user_table (
            email VARCHAR(50) NOT NULL PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            dob DATE NULL,
            gender VARCHAR(6) NOT NULL,
            hometown VARCHAR(50) NOT NULL,
            profile_image VARCHAR(100) NULL
        ) ENGINE=InnoDB
    ");

    // 2. Account table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS account_table (
            email VARCHAR(50) NOT NULL PRIMARY KEY,
            password VARCHAR(255) NOT NULL,
            type VARCHAR(5) NOT NULL,
            FOREIGN KEY (email)
                REFERENCES user_table(email)
                ON UPDATE CASCADE
                ON DELETE CASCADE
        ) ENGINE=InnoDB
    ");

    // 3. Smart feature table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS smart_feature_table (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            item_name VARCHAR(50) NOT NULL,
            item_category VARCHAR(50) NOT NULL,
            item_image VARCHAR(100) NULL,
            description_file VARCHAR(100) NULL,
            status VARCHAR(10) NOT NULL
        ) ENGINE=InnoDB
    ");

    // 4. Activity table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS activity_table (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(50) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            date DATE NULL,
            time TIME NULL,
            contact_number VARCHAR(15) NULL,
            status VARCHAR(10) NOT NULL
        ) ENGINE=InnoDB
    ");

    // 5. Community table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS community_table (
            id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            contribution_image VARCHAR(100) NULL,
            related_activity VARCHAR(50) NOT NULL,
            status VARCHAR(10) NOT NULL
        ) ENGINE=InnoDB
    ");

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    exit("Database setup failed.");
}
?>
