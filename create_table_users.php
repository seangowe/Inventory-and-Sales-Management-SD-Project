<?php
include 'connection.php';
$connection=mysqli_connect("localhost","root","", "sales_inventory");
if(!$connection){
    die("Connection failed: ".mysqli_connect_error());
}
$sql="CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'cashier') NOT NULL
)";
if(mysqli_query($connection, $sql)){
    echo "users table created successfully";
}else{
    echo "Error creating table: ". mysqli_error($connection);
}
mysqli_close($connection);
?>