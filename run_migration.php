<?php
require_once __DIR__ . '/config/database.php';

try {
    $database = new Database();
    $db = $database->connect();
    
    $sql = file_get_contents(__DIR__ . '/cms_migration.sql');
    
    // Split SQL by semicolon to execute multiple statements
    // This is a simple split and might fail on complex SQL with semicolons in strings, 
    // but for this specific migration file it should be fine.
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }
    
    echo "Migration completed successfully.";
    
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
    exit(1);
}
?>
