<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/database.php';

try {
    $db = new Database();
    $conn = $db->connect();
    
    echo "Connected to database.<br>";
    
    // Read the schema file
    $sql = file_get_contents(__DIR__ . '/database_schema.sql');
    
    if (!$sql) {
        die("Error reading database_schema.sql");
    }
    
    // Execute the SQL commands
    // Note: PDO doesn't support multiple queries in one execute call by default in some configurations,
    // but usually works for simple schemas. If not, we might need to split by semicolon.
    // Let's try executing the whole block first.
    
    $conn->exec($sql);
    echo "Database schema executed successfully.<br>";
    
    // Verify settings table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->rowCount() > 0) {
        echo "Settings table confirmed.<br>";
    } else {
        echo "Error: Settings table not found after execution.<br>";
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "<br>";
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?>
