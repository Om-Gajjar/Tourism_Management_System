<?php
/**
 * Tourism Management System - Database Connection
 * Returns a PDO instance using centralized configuration
 */

require_once __DIR__ . '/../config/config.php';

function initSchemaIfNeeded(PDO $pdo) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE 'packages'");
        if ($result && $result->rowCount() > 0) {
            return;
        }
    } catch (\Exception $e) {
        // Table check failed or tables missing
    }

    $schemaFile = '/var/www/html/database/schema.sql';
    if (!file_exists($schemaFile)) {
        $schemaFile = __DIR__ . '/../../database/schema.sql';
    }

    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        $sql = preg_replace('/CREATE\s+DATABASE\s+[^;]+;/i', '', $sql);
        $sql = preg_replace('/USE\s+[^;]+;/i', '', $sql);
        
        $pdo->exec($sql);
    }
}

function getDbConnection() {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $port = defined('DB_PORT') ? DB_PORT : '3306';
    $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if (DB_SSL_CA !== '') {
        $options[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = DB_SSL_VERIFY;
    }

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        initSchemaIfNeeded($pdo);
        return $pdo;
    } catch (\PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        throw new \PDOException("Database connection failed. Please check your configuration.");
    }
}

$pdo = getDbConnection();
