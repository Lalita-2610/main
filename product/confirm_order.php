<?php
session_start(); // เริ่มต้น session

// เชื่อมต่อฐานข้อมูล
include("connectdb.php");

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['uid'])) {
    header("Location: indexlogin.php"); // เปลี่ยนเป็นหน้าล็อกอินของคุณ
    exit();
}

// ตรวจสอบ CSRF Token
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token mismatch.");
    }

    // ดึงข้อมูลจากฟอร์ม
    $customer_name = isset($_POST['name']) ? $_POST['name'] : '';
    $customer_address = isset($_POST['address']) ? $_POST['address'] : '';
    $total = isset($_POST['total']) ? $_POST['total'] : 0; // ยอดรวม

    // สร้างรายการคำสั่งซื้อ
    $stmt = $conn->prepare("INSERT INTO orders (ototal, odate, id, status_id, customer_name, customer_address) VALUES (?, NOW(), ?, ?, ?, ?)");
    $userId = $_SESSION['uid']; // id ของผู้ใช้ที่ล็อกอิน
    $status_id = 1; // สถานะเริ่มต้น (ปรับได้ตามต้องการ)

    if ($stmt) {
        $stmt->bind_param("diiss", $total, $userId, $status_id, $customer_name, $customer_address);
        $stmt->execute();
        $oid = $stmt->insert_id; // รับ oid ของคำสั่งซื้อที่สร้างขึ้น
        $stmt->close();

        // บันทึกรายละเอียดคำสั่งซื้อ
        if (!empty($_POST['pid'])) {
            $stmt = $conn->prepare("INSERT INTO orders_detail (oid, pid, item, status_id) VALUES (?, ?, ?, ?)");
            $status_id_detail = 1; // สถานะเริ่มต้นสำหรับรายการสินค้า

            foreach ($_POST['pid'] as $key => $pid) {
                $item_quantity = isset($_POST['qty'][$key]) ? $_POST['qty'][$key] : 0;

                if ($stmt) {
                    $stmt->bind_param("iiis", $oid, $pid, $item_quantity, $status_id_detail);
                    $stmt->execute();
                }
            }
            $stmt->close();
        }

        // เคลียร์ตะกร้าสินค้า
        unset($_SESSION['cart']);
        
        // แสดงข้อความสำเร็จ
        echo "<h2>การสั่งซื้อของคุณสำเร็จแล้ว!</h2>";
        echo "<p>หมายเลขคำสั่งซื้อ: $oid</p>";
    } else {
        echo "เกิดข้อผิดพลาดในการสร้างคำสั่งซื้อ";
    }
} else {
    header("Location: index.php"); // เปลี่ยนเส้นทางถ้าไม่ใช่การโพสต์
}
?>
