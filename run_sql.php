<?php
require_once 'includes/db.php';
$sql = file_get_contents('schema_update_stories.sql');
$pdo->exec($sql);
echo "SQL Executed Successfully.\n";
?>
