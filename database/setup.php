<?php
/**
 * Database Setup Script
 * Run this once to initialize the database schema
 */

// Load configuration
require_once '../config/app.php';
require_once '../config/database.php';

// Check if we're running from CLI
$isCli = php_sapi_name() === 'cli';

// Read schema file
$schemaFile = __DIR__ . '/schema.sql';
if (!file_exists($schemaFile)) {
    die("Error: schema.sql not found at " . $schemaFile);
}

// Read SQL statements
$sql = file_get_contents($schemaFile);

// Split statements
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($s) => !empty($s) && !str_starts_with($s, '--')
);

// Execute statements
$success = 0;
$failed = 0;

foreach ($statements as $statement) {
    if (empty(trim($statement))) continue;
    
    // Skip comments and procedure delimiters
    if (str_starts_with(trim($statement), '--')) continue;
    if (trim($statement) === 'DELIMITER $$') continue;
    if (trim($statement) === 'DELIMITER ;') continue;
    
    try {
        if ($conn->query($statement . ';')) {
            $success++;
            if ($isCli) echo "✓ Executed statement\n";
        } else {
            $failed++;
            if ($isCli) echo "✗ Failed: " . $conn->error . "\n";
        }
    } catch (Exception $e) {
        $failed++;
        if ($isCli) echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

$message = "\n=== Database Setup Complete ===\n";
$message .= "Successful: $success\n";
$message .= "Failed: $failed\n\n";

if ($failed === 0) {
    $message .= "✓ Database initialized successfully!\n";
    $message .= "✓ You can now register users and start using the application.\n";
} else {
    $message .= "✗ Some statements failed. Check errors above.\n";
}

$message .= "\nNext steps:\n";
$message .= "1. Visit http://localhost:8000/register to create a user account\n";
$message .= "2. Login with your credentials\n";
$message .= "3. Start managing products!\n";

echo $message;

$conn->close();
?>
