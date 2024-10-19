<?php
include_once("checkloginadmin.php");
include_once("connectdb.php");

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if (!$conn) {
    die("การเชื่อมต่อฐานข้อมูลล้มเหลว: " . mysqli_connect_error());
}

// ตรวจสอบว่ามีการส่งค่า id มาหรือไม่
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // แปลงเป็นจำนวนเต็มเพื่อป้องกัน SQL Injection

    // สร้าง SQL query เพื่อลบข้อมูล
    $sql = "DELETE FROM users WHERE id = ?";

    // เตรียม statement
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt === false) {
        die("เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . mysqli_error($conn));
    }

    // ผูกพารามิเตอร์
    mysqli_stmt_bind_param($stmt, 'i', $id); // 'i' แสดงว่า $id เป็น integer

    // ดำเนินการลบข้อมูล
    if (mysqli_stmt_execute($stmt)) {
        // หากลบสำเร็จ
        $_SESSION['message'] = "ลบข้อมูลผู้ใช้เรียบร้อยแล้ว"; // ตั้งค่าข้อความใน session
        header("Location: customer.php"); // Redirect ไปยังหน้าข้อมูลลูกค้า
        exit(); // หยุดการทำงานของโค้ดหลังจากการ redirect
    } else {
        // หากลบไม่สำเร็จ
        echo "เกิดข้อผิดพลาดในการลบ: " . mysqli_stmt_error($stmt);
    }

    // ปิด statement
    mysqli_stmt_close($stmt);
} else {
    echo "ไม่พบ ID ของผู้ใช้!";
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
