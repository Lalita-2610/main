<?php
session_start(); // เริ่มต้น session

// รวมไฟล์เชื่อมต่อฐานข้อมูล
include("connectdb.php");

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['uid'])) {
    header("Location: indexlogin.php"); // เปลี่ยนเป็นหน้าล็อกอินของคุณ
    exit();
}

// ตรวจสอบว่า oid ถูกส่งมาหรือไม่
if (!isset($_GET['oid'])) {
    echo "หมายเลขคำสั่งซื้อไม่ถูกต้อง.";
    exit();
}

$order_id = intval($_GET['oid']); // แปลงค่าเป็น integer เพื่อความปลอดภัย
$user_id = $_SESSION['uid']; // สมมติว่าคุณมี uid ในเซสชันของผู้ใช้

// ตรวจสอบว่า oid นั้นเป็นของผู้ใช้ที่ล็อกอินอยู่หรือไม่
$sql_check_order = "SELECT * FROM orders WHERE oid = ? AND id = ?";
$stmt_check_order = $conn->prepare($sql_check_order);
if (!$stmt_check_order) {
    echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL.";
    exit();
}
$stmt_check_order->bind_param("ii", $order_id, $user_id);
$stmt_check_order->execute();
$result_check_order = $stmt_check_order->get_result();

if ($result_check_order->num_rows === 0) {
    echo "คำสั่งซื้อไม่ถูกต้องหรือคุณไม่มีสิทธิ์เข้าถึงคำสั่งซื้อนี้.";
    exit();
}
$stmt_check_order->close();

// ดึงข้อมูลคำสั่งซื้อและข้อมูลผู้ซื้อจากตาราง users
$sql_order_info = "SELECT o.*, u.first_name, u.last_name, u.address 
                   FROM orders o
                   JOIN users u ON o.id = u.id
                   WHERE o.oid = ?";
$stmt_order_info = $conn->prepare($sql_order_info);
if (!$stmt_order_info) {
    echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL สำหรับข้อมูลคำสั่งซื้อ.";
    exit();
}
$stmt_order_info->bind_param("i", $order_id);
$stmt_order_info->execute();
$result_order_info = $stmt_order_info->get_result();

if ($result_order_info->num_rows === 0) {
    echo "ไม่พบข้อมูลคำสั่งซื้อ.";
    exit();
}

$order_info = $result_order_info->fetch_assoc();
$stmt_order_info->close();

// ดึงข้อมูลรายละเอียดของคำสั่งซื้อ
$sql_details = "SELECT od.*, p.p_name, p.p_price 
                FROM orders_detail od
                JOIN product p ON od.pid = p.p_id
                WHERE od.oid = ?";
$stmt_details = $conn->prepare($sql_details);
if (!$stmt_details) {
    echo "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL.";
    exit();
}
$stmt_details->bind_param("i", $order_id);
$stmt_details->execute();
$result_details = $stmt_details->get_result();
?>

<!doctype html>
<html lang="th" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>สถานะการสั่งซื้อ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }
    </style>
    
</head>
<body>
    <!-- Navbar -->
    <header data-bs-theme="dark">
        <nav class="navbar navbar-expand-md navbar-light fixed-top bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">IT Shop</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse" 
                        aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav me-auto mb-2 mb-md-0">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="indexproduct.php">Product</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" 
                               aria-expanded="false">
                                <i class="bi bi-bag"></i>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="cart.php">ตะกร้าสินค้า</a></li>
                                <li><a class="dropdown-item" href="order_status.php">คำสั่งซื้อ</a></li>
								<li><a class="dropdown-item" href="order_history.php">ประวัติคำสั่งซื้อ</a></li>
                            </ul>
                        </li>
                    </ul>
                    <div class="dropdown text-end">
                        <a href="#" class="d-block link-body-emphasis text-decoration-none dropdown-toggle" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle" style="font-size: 32px;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end"> 
                          <li><a class="dropdown-item" href="indexloginadmin.php"><i class="bi bi-person-lock"></i></i> Administrator</a></li>
                            <li>
                                <a class="dropdown-item" href="indexporfile.php"><i class="bi bi-person-vcard"></i> โปรไฟล์</a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right"></i> ออกจากระบบ</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    
    <!-- เนื้อหารายละเอียดการสั่งซื้อ -->
    <div class="container mt-5 pt-5">
        <h2 class="mb-4">รายละเอียดการสั่งซื้อ</h2>

        <!-- แสดงข้อมูลผู้ซื้อ -->
        <div class="mb-4">
            <h4>ข้อมูลผู้ซื้อ</h4>
            <p><strong>ชื่อ:</strong> <?= htmlspecialchars($order_info['first_name'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($order_info['last_name'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>ที่อยู่:</strong> <?= nl2br(htmlspecialchars($order_info['address'], ENT_QUOTES, 'UTF-8')); ?></p>
            <p><strong>วันที่สั่งซื้อ:</strong> <?= htmlspecialchars($order_info['odate'], ENT_QUOTES, 'UTF-8'); ?></p>
            <p><strong>ยอดรวม:</strong> <?= number_format($order_info['ototal'], 2); ?> บาท</p>
            <p><strong>สถานะ:</strong>
                <?php
                // กำหนดชื่อสถานะ
                switch ($order_info['status_id']) {
                    case 1:
                        echo "รอดำเนินการ";
                        break;
                    case 2:
                        echo "ดำเนินการสำเร็จ";
                        break;
                    case 3:
                        echo "ยกเลิก";
                        break;
                    default:
                        echo "ไม่ทราบสถานะ";
                }
                ?>
            </p>
        </div>

        <!-- ตารางรายละเอียดการสั่งซื้อ -->
        <?php if ($result_details->num_rows === 0): ?>
            <div class="alert alert-info" role="alert">
                ไม่พบรายละเอียดคำสั่งซื้อ
            </div>
        <?php else: ?>
            <table class="table table-bordered">
                <thead class="table-secondary">
                    <tr>
                        <th scope="col">ชื่อสินค้า</th>
                        <th scope="col">ราคา</th>
                        <th scope="col">จำนวน</th>
                        <th scope="col">รวม</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total = 0; // ตัวแปรสำหรับคำนวณยอดรวมทั้งหมด
                    while ($detail = $result_details->fetch_assoc()):
                        $subtotal = $detail['p_price'] * $detail['item'];
                        $grand_total += $subtotal;
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($detail['p_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= number_format($detail['p_price'], 2); ?> บาท</td>
                            <td><?= intval($detail['item']); ?></td>
                            <td><?= number_format($subtotal, 2); ?> บาท</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <!-- แสดงยอดรวมทั้งหมดนอกตาราง -->
            <div class="text-end">
                <strong>รวมทั้งหมด:</strong> <strong><?= number_format($grand_total, 2); ?> บาท</strong>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js" integrity="sha384-dZZnK84XpgWmbxlU6hCc7B/2i7GFDsqkwiCzQso3r7aVk5dA1PmJdG0ElP/q4z8z3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
