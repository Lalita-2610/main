<?php
session_start(); // เริ่มต้น session

// เชื่อมต่อฐานข้อมูล
include("connectdb.php");

// ตรวจสอบว่าผู้ใช้ล็อกอินหรือไม่
if (!isset($_SESSION['uid'])) {
    header("Location: indexlogin.php"); // เปลี่ยนเป็นหน้าล็อกอินของคุณ
    exit();
}

$user_id = $_SESSION['uid']; // สมมติว่าคุณมี uid ในเซสชันของผู้ใช้

// ดึงข้อมูลคำสั่งซื้อของผู้ใช้
$sql_orders = "SELECT o.oid, o.ototal, o.odate, o.status_id FROM orders o WHERE o.id = ?";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("i", $user_id);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();
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
    
    <div class="container mt-5 pt-5">
        <h2 class="mb-4">สถานะการสั่งซื้อ</h2>

        <?php if ($result_orders->num_rows === 0): ?>
            <div class="alert alert-info" role="alert">
                คุณยังไม่มีคำสั่งซื้อ
            </div>
        <?php else: ?>
            <table class="table table-bordered">
                <thead class="table-secondary">
                    <tr>
                        <th scope="col">หมายเลขคำสั่งซื้อ</th>
                        <th scope="col">วันที่สั่งซื้อ</th>
                        <th scope="col">ยอดรวม</th>
                        <th scope="col">สถานะ</th>
                        <th scope="col">ดูรายละเอียด</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($order = $result_orders->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($order['oid'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($order['odate'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= number_format($order['ototal'], 2); ?> บาท</td>
                            <td>
                                <?php
                                // กำหนดชื่อสถานะ
                                switch ($order['status_id']) {
                                    case 1:
                                        echo "รอดำเนินการ";
                                        break;
                                    case 2:
                                        echo "กำลังจัดส่ง";
                                        break;
                                    case 3:
                                        echo "จัดส่งสำเร็จ";
                                        break;
                                    default:
                                        echo "ไม่ทราบสถานะ";
                                }
                                ?>
                            </td>
                            <td>
                                <a href="order_detail.php?oid=<?= $order['oid']; ?>" class="btn btn-info btn-sm">ดูรายละเอียด</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// ปิดการเชื่อมต่อ
$conn->close();
?>
