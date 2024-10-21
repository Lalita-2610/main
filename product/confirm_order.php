<?php
session_start(); // เริ่มต้น session

// เชื่อมต่อฐานข้อมูล
include("connectdb.php");

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['uid'])) {
    header("Location: indexlogin.php"); // เปลี่ยนเป็นหน้าล็อกอินของคุณ
    exit();
}

// ตรวจสอบค่าที่ส่งมาจากฟอร์ม
if (!isset($_POST['pid']) || !isset($_POST['qty'])) {
    echo "ไม่มีสินค้าที่ถูกเลือกเพื่อสั่งซื้อ";
    exit();
}

$pids = $_POST['pid'];
$qtys = $_POST['qty'];

// ตรวจสอบว่าเป็นอาเรย์และมีข้อมูลที่ถูกต้อง
if (!is_array($pids) || !is_array($qtys) || count($pids) !== count($qtys)) {
    echo "ข้อมูลสินค้าที่ไม่ถูกต้อง";
    exit();
}

// ตรวจสอบว่า pid และ qty เป็นตัวเลข
foreach ($pids as $pid) {
    if (!is_numeric($pid) || intval($pid) <= 0) {
        echo "รหัสสินค้าที่ไม่ถูกต้อง";
        exit();
    }
}

foreach ($qtys as $qty) {
    if (!is_numeric($qty) || intval($qty) <= 0) {
        echo "จำนวนสินค้าที่ไม่ถูกต้อง";
        exit();
    }
}

// ตรวจสอบ CSRF token เพื่อความปลอดภัย
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF token ไม่ถูกต้อง');
}

$total = 0;

// เริ่มต้น transaction เพื่อความมั่นใจในการบันทึกข้อมูล
$conn->begin_transaction();

try {
    // รับค่า user id และ status เริ่มต้น
    $user_id = $_SESSION['uid'];
    $status_id = 1; // กำหนดสถานะเริ่มต้นเป็น 'รอดำเนินการ'

    // คำนวณยอดรวมทั้งหมด
    $products = [];
    foreach ($pids as $index => $pid) {
        $pid = intval($pid);
        $qty = intval($qtys[$index]);

        // ตรวจสอบว่ามีสินค้าในฐานข้อมูล
        $product_check_sql = "SELECT p_price FROM product WHERE p_id = ?";
        $product_stmt = $conn->prepare($product_check_sql);
        if (!$product_stmt) {
            throw new Exception("เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $conn->error);
        }
        $product_stmt->bind_param("i", $pid);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();

        if ($product_result->num_rows === 0) {
            throw new Exception("ไม่พบรหัสสินค้า $pid ในฐานข้อมูล");
        }

        $product = $product_result->fetch_assoc();
        $subtotal = $product['p_price'] * $qty;
        $total += $subtotal;

        $products[] = [
            'pid' => $pid,
            'qty' => $qty,
            'subtotal' => $subtotal
        ];

        $product_stmt->close();
    }

    // ตรวจสอบยอดรวม
    if ($total <= 0) {
        throw new Exception("ยอดรวมต้องมากกว่าศูนย์");
    }

    // บันทึกคำสั่งซื้อในตาราง orders
    $sql_order = "INSERT INTO orders (ototal, odate, id, status_id) VALUES (?, NOW(), ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    if (!$stmt_order) {
        throw new Exception("เกิดข้อผิดพลาดขณะสร้างคำสั่งซื้อ: " . $conn->error);
    }
    $stmt_order->bind_param("dii", $total, $user_id, $status_id);
    if (!$stmt_order->execute()) {
        throw new Exception("เกิดข้อผิดพลาดขณะสร้างคำสั่งซื้อ: " . $stmt_order->error);
    }
    $order_id = $stmt_order->insert_id;
    $stmt_order->close();

    // บันทึกรายละเอียดสินค้าใน orders_detail
    $sql_detail = "INSERT INTO orders_detail (oid, pid, item) VALUES (?, ?, ?)";
    $stmt_detail = $conn->prepare($sql_detail);
    if (!$stmt_detail) {
        throw new Exception("เกิดข้อผิดพลาดขณะเพิ่มรายละเอียดสินค้า: " . $conn->error);
    }

    foreach ($products as $product) {
        $stmt_detail->bind_param("iii", $order_id, $product['pid'], $product['qty']);
        if (!$stmt_detail->execute()) {
            throw new Exception("เกิดข้อผิดพลาดขณะเพิ่มรายละเอียดสินค้า: " . $stmt_detail->error);
        }
    }
    $stmt_detail->close();

    // บันทึกสำเร็จ
    $conn->commit();

    // ลบตะกร้าสินค้าเมื่อคำสั่งซื้อสำเร็จ
    unset($_SESSION['cart']);

    // แสดงหน้าแสดงผลสำเร็จ
    ?>
    <!doctype html>
    <html lang="th" data-bs-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ยืนยันการสั่งซื้อ</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Kanit', sans-serif;
            }
        </style>
    </head>
    <body>
        <div class="container mt-5 text-center">
            <h2 class="mb-4">ขอบคุณสำหรับการสั่งซื้อ!</h2>
            <p>การสั่งซื้อของคุณได้ถูกดำเนินการเรียบร้อยแล้ว</p>
            <p><strong>หมายเลขคำสั่งซื้อ:</strong> <?= htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8'); ?></p>
            <a href="indexproduct.php" class="btn btn-primary mt-3">กลับไปช้อปปิ้งต่อ</a>
            <a href="order_status.php" class="btn btn-secondary mt-3">ดูสถานะการสั่งซื้อ</a>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    // หากเกิดข้อผิดพลาด Rollback transaction
    $conn->rollback();
    ?>
    <!doctype html>
    <html lang="th" data-bs-theme="light">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>เกิดข้อผิดพลาดในการสั่งซื้อ</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Kanit', sans-serif;
            }
        </style>
    </head>
    <body>
        <div class="container mt-5 text-center">
            <h2 class="mb-4 text-danger">เกิดข้อผิดพลาดในการสั่งซื้อ</h2>
            <p>ขออภัยในความไม่สะดวก กรุณาลองใหม่อีกครั้งหรือติดต่อฝ่ายสนับสนุนลูกค้า</p>
            <a href="indexproduct.php" class="btn btn-primary mt-3">กลับไปช้อปปิ้ง</a>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}

$conn->close();
?>
