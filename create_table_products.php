<?php
include 'connection.php';
$connection=mysqli_connect("localhost","root","", "sales_inventory");
if(!$connection){
    die("Connection failed: ".mysqli_connect_error());
}
$sql="CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    buying_price DECIMAL(10, 2) NOT NULL,
    selling_price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    min_stock_level INT NOT NULL DEFAULT 5
)";
if(mysqli_query($connection, $sql)){
    echo "products table created successfully";
}else{
    echo "Error creating table: ". mysqli_error($connection);
}
mysqli_close($connection);
?>