<?php
session_start(); // เริ่มต้น session

// เชื่อมต่อฐานข้อมูล
include("connectdb.php");

// ตรวจสอบ CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token validation failed.");
}

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['uid'])) {
    header("Location: indexlogin.php"); // เปลี่ยนเป็นหน้าล็อกอินของคุณ
    exit();
}

// ตรวจสอบข้อมูลสินค้าที่ส่งมาจากฟอร์ม
if (!isset($_POST['pid']) || !isset($_POST['qty'])) {
    die("Invalid order data.");
}

// ดึงข้อมูลสินค้าที่ถูกสั่งซื้อ
$pids = $_POST['pid'];
$quantities = $_POST['qty'];
$uid = $_SESSION['uid']; // ID ของผู้ใช้ที่ล็อกอิน

// บันทึกข้อมูลในตารางคำสั่งซื้อ
$sql_order = "INSERT INTO orders (ototal, odate, id, status_id) VALUES (?, NOW(), ?, ?)";
$stmt_order = $conn->prepare($sql_order);

// คำนวณยอดรวม
$total = 0;
foreach ($pids as $index => $pid) {
    $quantity = $quantities[$index];

    // ดึงข้อมูลราคาสินค้า
    $sql_price = "SELECT p_price FROM product WHERE p_id = ?";
    $stmt_price = $conn->prepare($sql_price);
    $stmt_price->bind_param('i', $pid);
    $stmt_price->execute();
    $result = $stmt_price->get_result();
    $product = $result->fetch_assoc();

    if ($product) {
        $price = $product['p_price'];
        $subtotal = $price * $quantity;
        $total += $subtotal;
    }
}

// สถานะคำสั่งซื้อ (เช่น 1 สำหรับกำลังดำเนินการ)
$status_id = 1;

if ($stmt_order) {
    $stmt_order->bind_param('dii', $total, $uid, $status_id);
    $stmt_order->execute();
    $oid = $stmt_order->insert_id; // รับ oid ของคำสั่งซื้อที่เพิ่งสร้าง

    // บันทึกข้อมูลในตารางรายละเอียดคำสั่งซื้อ
    $sql_order_details = "INSERT INTO order_details (oid, pid, item, status_id) VALUES (?, ?, ?, ?)";
    $stmt_order_details = $conn->prepare($sql_order_details);

    if ($stmt_order_details) {
        foreach ($pids as $index => $pid) {
            $quantity = $quantities[$index];
            $item = $quantity; // หรือจะเก็บรายละเอียดอื่นๆ ตามต้องการ
            $stmt_order_details->bind_param('iiis', $oid, $pid, $item, $status_id);
            $stmt_order_details->execute();
        }
        $stmt_order_details->close();
    }
    $stmt_order->close();

    // เคลียร์ตะกร้าสินค้า
    unset($_SESSION['cart']);
    // เปลี่ยนไปยังหน้ายืนยันคำสั่งซื้อ
    header("Location: order_confirmation.php?oid=" . $oid);
    exit();
} else {
    die("Order creation failed.");
}
?>
