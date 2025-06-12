<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_host = '212.68.34.228';
$db_user = 'bakbiico';
$db_pass = '3UYd*6o[wkC78S';
$db_name = 'bakbiico_bakbiiemlak';

try {
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $conn->exec("SET NAMES utf8mb4");
} catch(PDOException $e) {
    // Log the error
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Display a user-friendly error message
    header('HTTP/1.1 500 Internal Server Error');
    echo "<h1>Sistem Hatası</h1>";
    echo "<p>Üzgünüz, şu anda sistemimize erişilemiyor. Lütfen daha sonra tekrar deneyiniz.</p>";
    die();
}
?>