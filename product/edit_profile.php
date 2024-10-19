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
    <title>แก้ไขข้อมูลผู้ใช้</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">

    <style>
        .profile-card {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            max-width: 800px; /* กว้างขึ้นจาก 700px เป็น 800px */
            margin: 20px auto; /* ใช้ margin-top เป็น 20px เพื่อให้พอดีกับ navbar */
            padding: 20px;
            border-radius: 10px;
            background-color: #ffffff;
        }
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f8f9fa; /* สีพื้นหลัง */
        }
        .content {
            margin-top: 80px; /* ปรับค่าตามความสูงของ navbar */
        }
    </style>

    <script>
        function togglePasswordChange() {
            const passwordChangeDiv = document.getElementById('passwordChange');
            const checkbox = document.getElementById('changePasswordCheckbox');
            passwordChangeDiv.style.display = checkbox.checked ? 'block' : 'none';
        }
    </script>
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

    <div class="container content d-flex align-items-center justify-content-center">
<div class="profile-card">
    <h2 class="text-center">แก้ไขข้อมูลส่วนตัว</h2>
    <form action="update_profile.php" method="POST">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="firstName" class="form-label">ชื่อ</label>
                <input type="text" class="form-control" id="firstName" name="first_name" value="<?= htmlspecialchars($userData['first_name']); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="lastName" class="form-label">นามสกุล</label>
                <input type="text" class="form-control" id="lastName" name="last_name" value="<?= htmlspecialchars($userData['last_name']); ?>" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-12">
                <label for="username" class="form-label">ชื่อผู้ใช้</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($userData['username']); ?>" required>
            </div>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">เบอร์โทรศัพท์</label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($userData['phone']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">อีเมล</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($userData['email']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="address" class="form-label">ที่อยู่</label>
            <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($userData['address']); ?></textarea>
        </div>

        <!-- เช็คบ็อกซ์เพื่อเปลี่ยนรหัสผ่าน -->
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="changePasswordCheckbox" onclick="togglePasswordChange()">
            <label class="form-check-label" for="changePasswordCheckbox">ต้องการเปลี่ยนรหัสผ่าน</label>
        </div>

        <!-- ฟอร์มการเปลี่ยนรหัสผ่าน -->
        <div id="passwordChange" style="display: none;">
            <h4>เปลี่ยนรหัสผ่าน</h4>
            <div class="mb-3">
                <label for="currentPassword" class="form-label">รหัสผ่านปัจจุบัน</label>
                <input type="password" class="form-control" id="currentPassword" name="current_password">
            </div>
            <div class="mb-3">
                <label for="newPassword" class="form-label">รหัสผ่านใหม่</label>
                <input type="password" class="form-control" id="newPassword" name="new_password">
            </div>
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">ยืนยันรหัสผ่านใหม่</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirm_password">
            </div>
        </div>

        <div class="d-flex justify-content-between">
            <a href="indexporfile.php" class="btn btn-secondary">กลับไปยังข้อมูลส่วนตัว</a>
            <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
        </div>
    </form>
</div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
} else {
    echo "<div class='alert alert-danger'>ไม่พบข้อมูลผู้ใช้</div>";
}
?>
