<?php
/**
 * Database Configuration:
 * Establish a connection to the MySQL database server.
 * Parameters: (Hostname, Username, Password, Database Name)
 */
$conn = new mysqli("localhost", "root", "", "freshmart");

/**
 * Connection Validation:
 * Check if the connection was successful. 
 * If it fails, stop the script and display the error.
 */
if ($conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

/**
 * Character Set Configuration:
 * Set the encoding to utf8mb4 to ensure support for all characters 
 * (including special symbols and different languages).
 */
$conn->set_charset("utf8mb4");
?>