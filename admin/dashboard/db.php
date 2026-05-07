<?php
$host = "localhost";      
$user = "medical-unit";          
$password = "Medical--Unit2026$$";          
$database = "medical-unit"; 

$conn = mysqli_connect($host, $user, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>