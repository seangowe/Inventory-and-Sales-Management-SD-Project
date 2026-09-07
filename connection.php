<?php
//server,user,password
//conncects PHP to mysql
$conn=new mysqli("localhost","root","");
//Line 6 to 8 checks to see if the connection failed
if($conn->connect_error){
    die("conncection failed: " .$conn->connect_error);
}
?>