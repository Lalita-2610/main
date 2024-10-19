<?php
require 'connectdb.php'; // รวมไฟล์เชื่อมต่อฐานข้อมูล

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ตรวจสอบว่ามีการส่งค่า oid และ status_id หรือไม่
    if (isset($_POST['oid']) && isset($_POST['status_id'])) {
        $order_id = intval($_POST['oid']);  // รับค่า oid จาก POST
        $status_id = intval($_POST['status_id']);  // รับค่า status_id จาก POST

        // เตรียมคำสั่ง SQL เพื่ออัปเดต status_id
        $sql = "UPDATE orders SET status_id = ? WHERE oid = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("ii", $status_id, $order_id);  // ผูกพารามิเตอร์
            $stmt->execute();

            // ตรวจสอบว่าการอัปเดตสำเร็จหรือไม่
            if ($stmt->affected_rows > 0) {
                // เปลี่ยนเส้นทางกลับไปที่หน้า order_admin.php พร้อมข้อความสำเร็จ
                header("Location: order_admin.php?id=" . $order_id . "&success=1");
                exit();
            } else {
                // ถ้าไม่มีแถวที่ถูกอัปเดต
                header("Location: order_admin.php?id=" . $order_id . "&error=1");
                exit();
            }
        } else {
            echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL.";
            exit();
        }
    } else {
        echo "ข้อมูลไม่ครบถ้วน.";
        exit();
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
