<?php
session_start();
include_once("connectdb.php");

// ตรวจสอบการเข้าสู่ระบบ
if (!isset($_SESSION['uid'])) {
    header('Location: indexlogin.php');
    exit;
}

// ตัวแปรสำหรับข้อความ
$message = '';
$redirect_url = '';

// ตรวจสอบการส่งฟอร์ม
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $address = trim($_POST['address']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // เช็คหากรหัสผ่านใหม่ไม่ว่างและยืนยันรหัสผ่านตรงกัน
    if (!empty($new_password) && $new_password === $confirm_password) {
        // ตรวจสอบรหัสผ่านปัจจุบัน
        $stmtCheck = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmtCheck->bind_param("i", $_SESSION['uid']);
        $stmtCheck->execute();
        $resultCheck = $stmtCheck->get_result();
        $userData = $resultCheck->fetch_assoc();

        // ใช้ password_verify เพื่อตรวจสอบรหัสผ่านปัจจุบัน
        if (password_verify($current_password, $userData['password_hash'])) {
            // รหัสผ่านใหม่จะต้องมีการเข้ารหัส
            $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // อัปเดตรหัสผ่าน
            $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, phone = ?, email = ?, address = ?, password_hash = ? WHERE id = ?");
            $stmt->bind_param("sssssssi", $first_name, $last_name, $username, $phone, $email, $address, $new_password_hash, $_SESSION['uid']);
        } else {
            $message = "รหัสผ่านปัจจุบันไม่ถูกต้อง";
            $redirect_url = "../profile/edit_profile.php";
        }
    } else {
        // อัปเดตข้อมูลโดยไม่เปลี่ยนรหัสผ่าน
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, username = ?, phone = ?, email = ?, address = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $first_name, $last_name, $username, $phone, $email, $address, $_SESSION['uid']);
    }

    // Execute the statement
    if (isset($stmt)) {
        if ($stmt->execute()) {
            $message = "ข้อมูลถูกบันทึกเรียบร้อยแล้ว";
            $redirect_url = "indexporfile.php"; // เปลี่ยนเส้นทางไปยังหน้าที่ต้องการ
        } else {
            $message = "ไม่สามารถอัปเดตข้อมูลได้: " . htmlspecialchars($stmt->error);
            $redirect_url = "edit_profile.php"; // เปลี่ยนเส้นทางไปยังหน้าที่ต้องการ
        }
    } else {
        $message = "เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL";
        $redirect_url = "edit_profile.php"; // เปลี่ยนเส้นทางไปยังหน้าที่ต้องการ
    }

    // ปิดการเตรียมการ
    if (isset($stmt)) {
        $stmt->close();
    }
}

// ปิดการเชื่อมต่อกับฐานข้อมูล
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>อัปเดตข้อมูลผู้ใช้</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function redirect() {
            window.location.href = "<?php echo $redirect_url; ?>";
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <div class="alert <?php echo strpos($message, 'สำเร็จ') !== false ? 'alert-success' : 'alert-danger'; ?> text-center">
            <?php if (!empty($message)) echo htmlspecialchars($message); ?>
        </div>
        <?php if (!empty($redirect_url)): ?>
            <script>
                setTimeout(redirect, 3000); // เปลี่ยนเส้นทางหลังจาก 3 วินาที
            </script>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
