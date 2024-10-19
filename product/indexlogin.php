<?php
session_start();
include_once("connectdb.php");
?>

<!doctype html>
<html lang="th">
<head>
    <script src="../assets/js/color-modes.js"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <title>Login</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles -->
    <style>
        body {
            background-color: #f8f9fa; /* Background color */
            font-family: 'Roboto', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .form-signin {
            background-color: #fff; /* White for the form */
            padding: 50px;
            width: 450px;
            border-radius: 12px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1); /* Light shadow */
            text-align: center;
        }

        .form-signin h1 {
            color: #333; /* Dark text for the header */
            margin-bottom: 30px;
        }

        .form-floating label {
            color: #b8860b; /* Yellow label */
        }

        .form-floating input {
            border: 2px solid #d3d3d3; /* Gray border */
            border-radius: 8px;
            padding: 12px;
            height: 50px;
            font-size: 1rem;
            background-color: #f0f0f0; /* Light gray input */
            color: #333; /* Dark text */
            transition: border-color 0.2s ease;
        }

        .form-floating input:focus {
            border-color: #ffd700; /* Bright yellow on focus */
            outline: none;
            box-shadow: 0 0 5px rgba(255, 215, 0, 0.5);
        }

        .form-floating input:focus ~ label {
            color: #ffd700; /* Bright yellow label on focus */
        }

        .btn-primary {
            background-color: #ffd700; /* Bright yellow button */
            border: none;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 1.1rem;
            width: 100%;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .btn-primary:hover {
            background-color: #e6be00; /* Darker yellow on hover */
        }

        .form-check-label {
            color: #333; /* Dark text for the checkbox label */
        }

        .logo-img {
            width: 160px;
            height: auto;
            margin-bottom: 20px;
        }

        p.copyright {
            font-size: 0.9rem;
            margin-top: 40px;
            color: #6c757d; /* Light gray for copyright */
        }
    </style>
</head>
<body>
<main class="form-signin">
    <form method="post" action="">
        <img src="images/logol.png" width="180" height="172">
        <h1 class="h3 mb-3 fw-normal">Sign In</h1>

        <div class="form-floating mb-3">
            <input type="text" class="form-control" name="ausername" placeholder="Username" autofocus required>
            <label for="floatingInput">Username</label>
        </div>
        <div class="form-floating mb-3">
            <input type="password" class="form-control" name="apassword" placeholder="Password" required>
            <label for="floatingPassword">Password</label>
        </div>
		
      <div class="form-check text-start my-3">
            <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
            <label class="form-check-label" for="flexCheckDefault">จำฉันไว้</label>
        </div>
      <button class="btn btn-warning w-100 py-2" type="submit" name="Submit">เข้าสู่ระบบ</button>
		<p class="text-left">ยังไม่มีบัญชีผู้ใช้? <a class="link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-75-hover" href="indexregister.php">สมัครสมาชิกใหม่</a></p>
		
        <p class="copyright">&copy; 2024 ร้าน IT-Shop</p>
    </form>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
	
<?php
if(isset($_POST['Submit'])){
    // รับค่าจากฟอร์มและเตรียมข้อมูลเพื่อป้องกัน SQL Injection
    $username = mysqli_real_escape_string($conn, $_POST['ausername']);
    $password = $_POST['apassword']; // รหัสผ่านยังไม่ได้เข้ารหัส

    // ตรวจสอบว่าการเชื่อมต่อฐานข้อมูลสำเร็จหรือไม่
    if ($conn) {
        // เตรียมคำสั่ง SQL ด้วย Prepared Statements
        $stmt = $conn->prepare("SELECT id, first_name, password_hash FROM users WHERE username = ?");
        if ($stmt) {
            // ผูกพารามิเตอร์
            $stmt->bind_param("s", $username);
            // รันคำสั่ง
            $stmt->execute();
            // เก็บผลลัพธ์
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                // ผูกตัวแปรกับผลลัพธ์
                $stmt->bind_result($id, $firstName, $passwordHash);
                $stmt->fetch();

                // ตรวจสอบรหัสผ่าน
                if (password_verify($password, $passwordHash)) {
                    // ตั้งค่าเซสชัน
                    $_SESSION['uid'] = $id;
                    $_SESSION['uname'] = $firstName;
                    // Redirect ไปยังหน้าอื่น
                    echo "<script>window.location='index.php';</script>";
                    exit();
                } else {
                    // รหัสผ่านไม่ถูกต้อง
                    echo "<script>alert('Username หรือ Password ไม่ถูกต้อง');</script>";
                }
            } else {
                // ไม่มีผู้ใช้ในระบบ
                echo "<script>alert('Username หรือ Password ไม่ถูกต้อง');</script>";
            }

            // ปิด statement
            $stmt->close();
        } else {
            // เกิดข้อผิดพลาดในการเตรียม statement
            echo "เกิดข้อผิดพลาด: " . $conn->error;
        }
    } else {
        echo "การเชื่อมต่อกับฐานข้อมูลล้มเหลว: " . mysqli_connect_error();
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
mysqli_close($conn);
?>
</body>
</html>
