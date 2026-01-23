<?php
$servername = "localhost";  
$username = "root";
$password = ""; 
$dbname = "healnest_db";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}   
// Removed echo to prevent output when included
?>