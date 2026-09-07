<?php
include 'connection.php';
$connection=mysqli_connect("localhost","root","", "sales_inventory");
if(!$connection){
    die("Connection failed: ".mysqli_connect_error());
}
$sql="CREATE TABLE IF NOT EXISTS sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    sale_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    user_id INT NOT NULL,
    FOREIGN KEY (product_id) REFERENCES products(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
)";
if(mysqli_query($connection, $sql)){
    echo "sales table created successfully";
}else{
    echo "Error creating table: ". mysqli_error($connection);
}
mysqli_close($connection);
?>