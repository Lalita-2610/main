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
    echo "No products selected for the order.";
    exit();
}

$pids = $_POST['pid'];
$qtys = $_POST['qty'];

// ตรวจสอบว่าเป็นอาเรย์และมีข้อมูล
if (!is_array($pids) || !is_array($qtys) || count($pids) !== count($qtys)) {
    echo "Invalid product data.";
    exit();
}

// ตรวจสอบให้แน่ใจว่าทั้งหมดเป็นตัวเลข
foreach ($pids as $pid) {
    if (!is_numeric($pid) || intval($pid) <= 0) {
        echo "Invalid product ID.";
        exit();
    }
}

foreach ($qtys as $qty) {
    if (!is_numeric($qty) || intval($qty) <= 0) {
        echo "Invalid quantity.";
        exit();
    }
}

// ตรวจสอบ CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Invalid CSRF token');
}

$total = 0;

// Begin transaction to ensure atomicity
$conn->begin_transaction();

try {
    // Insert into orders table
    $user_id = $_SESSION['uid']; // สมมติว่าคุณมี uid ในเซสชันของผู้ใช้
    $status_id = 1; // กำหนดสถานะเริ่มต้นเป็น 'รอดำเนินการ'

    // Calculate total
    $products = [];
    foreach ($pids as $index => $pid) {
        $pid = intval($pid);
        $qty = intval($qtys[$index]);

        // ตรวจสอบว่า pid มีอยู่ใน product หรือไม่
        $product_check_sql = "SELECT p_price FROM product WHERE p_id = ?";
        $product_stmt = $conn->prepare($product_check_sql);
        if (!$product_stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $product_stmt->bind_param("i", $pid);
        $product_stmt->execute();
        $product_result = $product_stmt->get_result();

        if ($product_result->num_rows === 0) {
            throw new Exception("Product ID $pid not found in the product table.");
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

    // ตรวจสอบว่ามียอดรวมก่อนที่จะบันทึก
    if ($total <= 0) {
        throw new Exception("Total amount must be greater than zero.");
    }

    // Insert into orders table
    $sql_order = "INSERT INTO orders (ototal, odate, id, status_id, customer_name) VALUES (?, NOW(), ?, ?, ?)";
    $stmt_order = $conn->prepare($sql_order);
    if (!$stmt_order) {
        throw new Exception("Error while creating order: " . $conn->error);
    }

    // กำหนดค่าเริ่มต้นสำหรับ customer_name
    $customer_name = "unknown"; // หรือสามารถใช้ชื่ออื่นๆ ตามที่ต้องการ
    $stmt_order->bind_param("diis", $total, $user_id, $status_id, $customer_name);
    if (!$stmt_order->execute()) {
        throw new Exception("Error while creating order: " . $stmt_order->error);
    }
    $order_id = $stmt_order->insert_id;
    $stmt_order->close();

    // Insert into orders_detail table
    $sql_detail = "INSERT INTO orders_detail (oid, pid, item) VALUES (?, ?, ?)";
    $stmt_detail = $conn->prepare($sql_detail);
    if (!$stmt_detail) {
        throw new Exception("Error while adding order detail: " . $conn->error);
    }

    foreach ($products as $product) {
        $stmt_detail->bind_param("iii", $order_id, $product['pid'], $product['qty']);
        if (!$stmt_detail->execute()) {
            throw new Exception("Error while adding order detail: " . $stmt_detail->error);
        }
    }
    $stmt_detail->close();

    // Commit transaction
    $conn->commit();

    // Clear the cart after successful order
    unset($_SESSION['cart']);

    // สร้างหน้าแสดงผลการสั่งซื้อสำเร็จ
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
            <p>การสั่งซื้อของคุณได้ถูกดำเนินการเรียบร้อยแล้ว ทีมงานของเราจะติดต่อคุณเร็วๆ นี้เพื่อยืนยันการสั่งซื้อ</p>
            <p><strong>หมายเลขคำสั่งซื้อ:</strong> <?= htmlspecialchars($order_id, ENT_QUOTES, 'UTF-8'); ?></p>
            <a href="indexproduct.php" class="btn btn-primary mt-3">
                <i class="bi bi-cart-check"></i> กลับไปช้อปปิ้งต่อ
            </a>
            <a href="order_status.php" class="btn btn-secondary mt-3">
                <i class="bi bi-card-checklist"></i> ดูสถานะการสั่งซื้อ
            </a>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    
    // Show detailed error message
    echo "Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');

    // Log the error message for further analysis
    error_log($e->getMessage());

    // Show user-friendly error message
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
            <p>ขออภัยในความไม่สะดวก กรุณาลองใหม่อีกครั้งหรือติดต่อฝ่ายสนับสนุนลูกค้า.</p>
            <a href="../checkout/indexcheckout.php" class="btn btn-warning mt-3">
                <i class="bi bi-arrow-left"></i> กลับไปแก้ไขตะกร้า
            </a>
            <a href="indexproduct.php" class="btn btn-primary mt-3">
                <i class="bi bi-cart-check"></i> กลับไปช้อปปิ้ง
            </a>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
