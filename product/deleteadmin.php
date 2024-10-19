<?php
include_once("checkloginadmin.php");

if(isset($_GET['pid'])){
	include("connectdb.php");
	$sql = "DELETE FROM product WHERE `product`.`p_id` = '{$_GET['pid']}' ";
	mysqli_query($conn, $sql) ;
	
	unlink("images/".$_GET['pid'].".".$_GET['picture']);
	
	echo "<script>";
	echo "window.location='indexproductadmin.php';";
	echo "</script>";
}
?>