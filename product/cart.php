<?php
session_start(); // เริ่มต้น session

// รวมไฟล์เชื่อมต่อฐานข้อมูล
include("connectdb.php");

// ฟังก์ชันสำหรับดึงข้อมูลสินค้าจากฐานข้อมูล
function getProduct($conn, $p_id) {
    $sql = "SELECT p_id, p_name, p_detail, p_price, p_picture FROM product WHERE p_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $p_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
    }
    return null; // ถ้าหากไม่พบสินค้า ให้คืนค่า null
}

// เพิ่มสินค้าลงในตะกร้า
if (isset($_GET['pid']) && isset($_GET['qty'])) {
    $pid = filter_var($_GET['pid'], FILTER_VALIDATE_INT);
    $qty = filter_var($_GET['qty'], FILTER_VALIDATE_INT);

    if ($pid !== false && $qty !== false && $qty > 0) {
        if (isset($_SESSION['cart'][$pid])) {
            $_SESSION['cart'][$pid] += $qty; // เพิ่มจำนวนสินค้า
        } else {
            $_SESSION['cart'][$pid] = $qty; // เพิ่มสินค้าใหม่ในตะกร้า
        }
    }

    // รีไดเร็กไปยังหน้าตะกร้าเพื่อแสดงผล
    header("Location: cart.php");
    exit();
}

// ลบสินค้าจากตะกร้า
if (isset($_GET['remove'])) {
    $remove_id = filter_var($_GET['remove'], FILTER_VALIDATE_INT);
    if ($remove_id !== false && isset($_SESSION['cart'][$remove_id])) {
        unset($_SESSION['cart'][$remove_id]);
    }
    header("Location: cart.php");
    exit();
}

// อัปเดตจำนวนสินค้าภายในตะกร้า
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    if (isset($_POST['quantities']) && is_array($_POST['quantities'])) {
        foreach ($_POST['quantities'] as $pid => $qty) {
            $pid = filter_var($pid, FILTER_VALIDATE_INT);
            $qty = filter_var($qty, FILTER_VALIDATE_INT);
            if ($pid !== false && $qty !== false) {
                if ($qty > 0) {
                    $_SESSION['cart'][$pid] = $qty;
                } else {
                    unset($_SESSION['cart'][$pid]);
                }
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// คำนวณราคาทั้งหมด
$total = 0;
$cart_items = [];

if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $product = getProduct($conn, $pid);
        if ($product) {
            // กำหนดเส้นทางของรูปภาพ
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            $p_picture_extension = strtolower(pathinfo($product['p_picture'], PATHINFO_EXTENSION));
            if (!in_array($p_picture_extension, $allowed_extensions)) {
                $p_picture_extension = 'jpg'; // กำหนดเป็น jpg หากนามสกุลไม่ถูกต้อง
            }
            $imagePath = "images/" . htmlspecialchars($product['p_id'], ENT_QUOTES, 'UTF-8') . "." . $p_picture_extension;
            if (!file_exists($imagePath)) {
                $imagePath = "images/default.jpg"; // กำหนดภาพเริ่มต้นถ้าไม่มีภาพสินค้า
            }

            $subtotal = $product['p_price'] * $qty;
            $total += $subtotal;
            $product['qty'] = $qty;
            $product['subtotal'] = $subtotal;
            $product['imagePath'] = $imagePath; // เพิ่มเส้นทางของภาพเข้าไปในข้อมูลสินค้า
            $cart_items[] = $product;
        }
    }
}
?>

<!doctype html>
<html lang="th" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ตะกร้าสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Kanit', sans-serif;
        }
        /* เพิ่มระยะห่างด้านบนของเนื้อหาเนื่องจาก navbar fixed-top */
        .container.mt-5.pt-5 {
            margin-top: 80px;
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

<!-- เนื้อหาตะกร้าสินค้า -->
<div class="container mt-5 pt-5">
    <h2 class="mb-4">ตะกร้าสินค้าของคุณ</h2>

    <?php if (empty($cart_items)): ?>
        <div class="alert alert-info" role="alert">
            ตะกร้าสินค้าของคุณยังว่างเปล่า <a href="indexproduct.php" class="alert-link">เริ่มต้นช้อปปิ้งเลย!</a>
        </div>
    <?php else: ?>
        <form method="POST" action="cart.php">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-secondary">
                    <tr>
                        <th scope="col">รูปภาพ</th>
                        <th scope="col">ชื่อสินค้า</th>
                        <th scope="col">ราคา</th>
                        <th scope="col">จำนวน</th>
                        <th scope="col">รวม</th>
                        <th scope="col">ลบ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item): ?>
                        <tr>
                            <td>
                                <img src="<?= htmlspecialchars($item['imagePath'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($item['p_name'], ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid" style="max-width: 100px;">
                            </td>
                            <td><?= htmlspecialchars($item['p_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= number_format($item['p_price'], 2); ?> บาท</td>
                            <td>
                                <input type="number" name="quantities[<?= $item['p_id']; ?>]" 
                                       value="<?= $item['qty']; ?>" min="1" class="form-control" style="width: 80px;">
                            </td>
                            <td><?= number_format($item['subtotal'], 2); ?> บาท</td>
                            <td>
                                <a href="cart.php?remove=<?= urlencode($item['p_id']); ?>" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> ลบ
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

			<!-- ยอดรวม -->
			<div class="d-flex justify-content-between mt-4">
				<h4 class="ms-auto">ยอดรวม: </h4>
				<h4><?= number_format($total, 2); ?> บาท</h4>
			</div>

            <!-- ปุ่มกลับไปหน้าช้อปปิ้ง และ ชำระเงิน -->
			<div class="d-flex justify-content-between mb-4">
				<a href="indexproduct.php" class="btn btn-secondary">
					<i class="bi bi-arrow-left"></i> กลับไปหน้าช้อปปิ้ง
				</a>
				<div class="ms-auto">
					<button type="submit" name="update_cart" class="btn btn-warning">
						<i class="bi bi-arrow-clockwise"></i> อัปเดตตะกร้า
					</button>
					<a href="indexcheckout.php" class="btn btn-success ms-2">
						<i class="bi bi-check-circle"></i> ชำระเงิน
					</a>
				</div>
			</div>
        </form>
    <?php endif; ?>
</div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>
</body>
</html>