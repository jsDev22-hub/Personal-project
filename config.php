<?php
$host = "sql208.infinityfree.com";
$db_name = "if0_41411487_neu__library";
$username = "if0_41411487";
$password = "NrWScZIRQOMjXo";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>