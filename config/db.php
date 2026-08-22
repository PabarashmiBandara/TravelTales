<?php

// Function to safely load environment variables from .env file
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return [];
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $env = [];

    foreach ($lines as $line) {
        // Ignore comments
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) {
            continue;
        }

        // Parse KEY=VALUE
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Strip surrounding quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }

            $env[$key] = $value;
        }
    }

    return $env;
}

// Load environment configuration
$envPath = __DIR__ . '/../.env';
$env = loadEnv($envPath);

$host     = $env['DB_HOST']     ?? 'localhost';
$dbName   = $env['DB_NAME']     ?? 'travel_tales';
$username = $env['DB_USER']     ?? 'root';
$password = $env['DB_PASSWORD'] ?? '';

// Establish PDO Connection
try {
    $dsn = "mysql:host={$host};dbname={$dbName};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on SQL errors
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Return associative arrays
        PDO::ATTR_EMULATE_PREPARES   => false,                  // Use native prepared statements for security
    ];

    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Auto-migrate view_count column
    try {
        $pdo->exec("ALTER TABLE `blog_posts` ADD COLUMN `view_count` INT NOT NULL DEFAULT 0");
    } catch (PDOException $e) {
    }
    
} catch (PDOException $e) {
    // Log internal error for debugging and show an message to the user
    error_log("Database Connection Error: " . $e->getMessage());
    die("<div style='font-family:sans-serif; padding:20px; background:#fff0f0; border-left:4px solid #e53e3e; margin:20px auto; max-width:600px; border-radius:6px;'>
        <h3 style='margin-top:0; color:#c53030;'>Unable to Connect to Database</h3>
        <p style='color:#4a5568;'>Please verify that MySQL is running and that your <code>.env</code> file contains the correct database configuration.</p>
    </div>");
}