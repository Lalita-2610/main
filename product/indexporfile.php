<?php
session_start();
include_once("connectdb.php");

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['uid'])) {
    echo "<div class='d-flex flex-column align-items-center justify-content-center vh-100'>
            <h2>โปรดเข้าสู่ระบบก่อน</h2>
            <p>กำลังไปยังหน้าล็อกอิน...</p>
            <script>
                setTimeout(function() {
                    window.location.href = 'indexlogin.php'; // เปลี่ยนเส้นทางไปยังหน้าล็อกอิน
                }, 3000); // รอ 3 วินาทีก่อนเปลี่ยนเส้นทาง
            </script>
          </div>";
    exit; // หยุดการทำงานของสคริปต์
}

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

// ใช้ Prepared Statements เพื่อความปลอดภัย
$stmt = $conn->prepare("SELECT first_name, last_name, username, phone, email, address FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['uid']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $userData = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="th" data-bs-theme="auto">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>โปรไฟล์ผู้ใช้</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

    <style>
        /* สไตล์เพิ่มเติมสำหรับการ์ดโปรไฟล์ */
        .profile-card {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            max-width: 600px;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
        }
        .profile-card h2 {
            margin-bottom: 20px;
        }
        .profile-field {
            margin-bottom: 15px;
        }
        .btn-edit {
            margin-top: 20px;
        }
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
    
    <div class="container d-flex align-items-center justify-content-center vh-100">
        <div class="profile-card">
            <h2 class="text-center">ข้อมูลส่วนตัว</h2>
            <form>
                <div class="row">
                    <div class="col-md-6 profile-field">
                        <label for="firstName" class="form-label">ชื่อ</label>
                        <input type="text" class="form-control" id="firstName" value="<?= htmlspecialchars($userData['first_name']); ?>" readonly>
                    </div>
                    <div class="col-md-6 profile-field">
                        <label for="lastName" class="form-label">นามสกุล</label>
                        <input type="text" class="form-control" id="lastName" value="<?= htmlspecialchars($userData['last_name']); ?>" readonly>
                    </div>
                </div>
                <div class="row">
                    <div class="profile-field">
                        <label for="username" class="form-label">ชื่อผู้ใช้</label>
                        <input type="text" class="form-control" id="username" value="<?= htmlspecialchars($userData['username']); ?>" readonly>
                    </div>
                </div>
                <div class="profile-field">
                    <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
                    <input type="tel" class="form-control" id="phone" value="<?= htmlspecialchars($userData['phone']); ?>" readonly>
                </div>
                <div class="profile-field">
                    <label for="email" class="form-label">อีเมล</label>
                    <input type="email" class="form-control" id="email" value="<?= htmlspecialchars($userData['email']); ?>" readonly>
                </div>
                <div class="profile-field">
                    <label for="address" class="form-label">ที่อยู่</label>
                    <textarea class="form-control" id="address" rows="3" readonly><?= htmlspecialchars($userData['address']); ?></textarea>
                </div>
                <div class="d-flex justify-content-between">
                    <a href="edit_profile.php" class="btn btn-warning btn-edit">
                        <i class="bi bi-pencil"></i> แก้ไขข้อมูล
                    </a>
                    <a href="index.php" class="btn btn-secondary btn-edit">
                        กลับหน้าหลัก
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS และ dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} else {
    echo "<div class='d-flex flex-column align-items-center justify-content-center vh-100'>
            <h2>ไม่พบข้อมูลผู้ใช้</h2>
            <a href='index.php' class='btn btn-warning mt-3'>กลับหน้าหลัก</a>
          </div>";
}

$stmt->close();
$conn->close();
?>
