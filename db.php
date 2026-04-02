<?php
$conn = new mysqli("localhost", "root", "", "freshmart");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>