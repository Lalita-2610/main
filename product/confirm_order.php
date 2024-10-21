<?php
session_start(); // เริ่มต้น session

// เชื่อมต่อฐานข้อมูล
include("connectdb.php");

// ตรวจสอบ CSRF Token
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token validation failed.");
}

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['uid'])) {
    header("Location: indexlogin.php"); // เปลี่ยนเป็นหน้าล็อกอินของคุณ
    exit();
}

// ดึงข้อมูลจากตะกร้า
$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$products = getProducts($conn, $cart); // ฟังก์ชันที่คุณมีอยู่เพื่อดึงข้อมูลผลิตภัณฑ์

// คำนวณยอดรวม
$total = 0;
foreach ($products as $pid => $product) {
    $subtotal = $product['p_price'] * $cart[$pid];
    $total += $subtotal;
}

// ข้อมูลลูกค้า
$name = $_POST['name'];
$lastname = $_POST['lastname'];
$phone = $_POST['phone'];
$address = $_POST['address'];

// บันทึกข้อมูลคำสั่งซื้อ
$sqlOrder = "INSERT INTO orders (ototal, odate, id, status_id, customer_name, customer_address) VALUES (?, NOW(), ?, ?, ?, ?)";
$stmtOrder = $conn->prepare($sqlOrder);
$status_id = 1; // ค่าที่กำหนดสำหรับสถานะคำสั่งซื้อ (เช่น รอการชำระเงิน)
$stmtOrder->bind_param("diiss", $total, $_SESSION['uid'], $status_id, $name . ' ' . $lastname, $address);

if ($stmtOrder->execute()) {
    $oid = $stmtOrder->insert_id; // ดึง ID ของคำสั่งซื้อล่าสุด

    // บันทึกข้อมูลรายละเอียดคำสั่งซื้อ
    $sqlOrderDetail = "INSERT INTO orders_detail (oid, pid, item, status_id) VALUES (?, ?, ?, ?)";
    $stmtDetail = $conn->prepare($sqlOrderDetail);
    
    foreach ($cart as $pid => $qty) {
        $item = htmlspecialchars($products[$pid]['p_name'], ENT_QUOTES, 'UTF-8');
        $status_detail_id = 1; // ค่าที่กำหนดสำหรับสถานะรายละเอียดคำสั่งซื้อ
        $stmtDetail->bind_param("iisi", $oid, $pid, $item, $status_detail_id);
        $stmtDetail->execute();
    }

    $stmtDetail->close();
    $stmtOrder->close();

    // เคลียร์ตะกร้าหลังจากสั่งซื้อ
    unset($_SESSION['cart']);
    
    // แสดงข้อความยืนยันการสั่งซื้อ
    echo "<h2>คำสั่งซื้อของคุณได้รับการบันทึกแล้ว</h2>";
    echo "<p>ยอดรวม: " . number_format($total, 2) . " บาท</p>";
    echo "<p><a href='index.php'>กลับไปยังหน้าแรก</a></p>";
} else {
    echo "เกิดข้อผิดพลาดในการบันทึกคำสั่งซื้อ: " . $conn->error;
}

$conn->close(); // ปิดการเชื่อมต่อฐานข้อมูล
?>
