<?php
$servername = "poss.mabcoonline.com";
$username = "mabco";
$password = "123456";
$dbname = "mabco3_ost";
$conn = mysqli_connect($servername, $username, $password, $dbname) ;
$con=new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);
$con->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
// $conn = mysqli_connect("localhost","root","","sourcecodester_events") ;
mysqli_set_charset($conn,"utf8");
if (!$conn)
{
echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
?>