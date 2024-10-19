<?php
// includes/connectdb.php

$servername = "localhost";         // ชื่อเซิร์ฟเวอร์ฐานข้อมูล
$dbusername = "root";  // ชื่อผู้ใช้ฐานข้อมูล
$dbpassword = "save1234";  // รหัสผ่านฐานข้อมูล
$dbname = "shop1";     // ชื่อฐานข้อมูล

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $dbusername, $dbpassword, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("การเชื่อมต่อล้มเหลว: " . $conn->connect_error);
}
?>