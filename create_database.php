<?php
include 'connection.php';
// query to create database
$sql="CREATE DATABASE sales_inventory";
if($conn->query($sql) === TRUE){
    echo "database created successfully";
}else{
    echo "Error: " .$conn->error;
}
?>