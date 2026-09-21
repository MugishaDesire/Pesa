<?php
// config.php
// This file connects to the MySQL database.
// Every other PHP file will "require" this file so they can talk to the database.

$host = "localhost";      // MySQL server address (localhost = same computer)
$db_user = "root";        // default XAMPP MySQL username
$db_pass = "password";            // default XAMPP MySQL password (empty)
$db_name = "auth_demo";   // the database we created in database.sql

// mysqli is PHP's built-in way of talking to MySQL databases
$conn = new mysqli($host, $db_user, $db_pass, $db_name);

// If the connection fails, stop the script and show an error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start a PHP session on every page that includes this file.
// A session lets the server "remember" a logged-in user across page loads.
// session_start() must be called before ANY HTML output.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
