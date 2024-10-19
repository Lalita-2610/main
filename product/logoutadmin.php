<?php
session_start();

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['aid'])) {
    // ถ้ายังไม่ได้เข้าสู่ระบบ ให้แสดงข้อความ
    echo "<script>";
    echo "alert('คุณยังไม่ได้เข้าสู่ระบบ');"; // แสดงข้อความเตือน
    echo "window.location='indexloginadmin.php';"; // เปลี่ยนเส้นทางไปยังหน้าล็อกอิน
    echo "</script>";
    exit(); // หยุดการทำงานของสคริปต์
}

// ลบข้อมูลเซสชันสำหรับ uid และ uname
unset($_SESSION['aid']);
unset($_SESSION['uname']);

// เปลี่ยนเส้นทางไปยังหน้าล็อกอิน
echo "<script>";
echo "window.location='indexloginadmin.php';"; // แก้ไขชื่อไฟล์ให้ถูกต้อง
echo "</script>";
?>
