<?php
$conn = mysqli_connect('localhost', 'samuel', '1234567890','orple_db');
if(!$conn) {
    die("Mysql Error: " . mysqli_connect_error());
}
?>