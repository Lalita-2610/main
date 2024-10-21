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
        die("CSRF token validation failed");
    }

    // ดึงข้อมูลจากฟอร์ม
    $uid = $_SESSION['uid'];
    $pids = $_POST['pid'];
    $qtys = $_POST['qty'];
    $phone = $_POST['phone'];
    $email = $_POST['email'] ?? ''; // อาจจะมีหรือไม่มี
    $save_info = isset($_POST['save_info']) ? 1 : 0;

    // ตรวจสอบข้อมูลที่ส่งมา
    if (empty($pids) || empty($qtys) || count($pids) !== count($qtys)) {
        die("Invalid order data");
    }

    // คำนวณยอดรวม
    $total = 0;
    foreach ($pids as $key => $pid) {
        // สมมุติว่าให้ไปดึงราคาสินค้าโดยใช้ $pid
        $stmt = $conn->prepare("SELECT p_price FROM product WHERE p_id = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $price = $row['p_price'];
            $qty = $qtys[$key];
            $total += $price * $qty;
        }
        $stmt->close();
    }

    // บันทึกคำสั่งซื้อในฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO orders (uid, ototal, odate, status_id) VALUES (?, ?, NOW(), 1)");
    $stmt->bind_param("id", $uid, $total);
    $stmt->execute();
    $oid = $stmt->insert_id; // ดึง ID ของคำสั่งซื้อที่เพิ่งสร้างขึ้น
    $stmt->close();

    // บันทึกรายละเอียดสินค้าในคำสั่งซื้อ
    foreach ($pids as $key => $pid) {
        $qty = $qtys[$key];
        $stmt = $conn->prepare("INSERT INTO order_details (oid, pid, quantity) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $oid, $pid, $qty);
        $stmt->execute();
        $stmt->close();
    }

    // อาจจะส่งอีเมลยืนยันการสั่งซื้อหรือการแจ้งเตือนที่นี่

    // แสดงผลการสั่งซื้อสำเร็จ
    echo "<h1>คำสั่งซื้อสำเร็จ</h1>";
    echo "<p>หมายเลขคำสั่งซื้อของคุณคือ: <strong>$oid</strong></p>";
    echo "<p>ยอดรวม: <strong>" . number_format($total, 2) . " บาท</strong></p>";
    
    // อาจจะนำไปยังหน้าที่ต้องการหรือหน้าประวัติการสั่งซื้อ
    // header("Location: order_status.php");
    // exit();
} else {
    // ถ้าไม่ใช่ POST request ให้ไปที่หน้าล็อกอินหรือหน้าที่ต้องการ
    header("Location: indexlogin.php");
    exit();
}
?>
