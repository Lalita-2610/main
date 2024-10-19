<?php
include_once("checkloginadmin.php");

// เชื่อมต่อฐานข้อมูล
include("connectdb.php");
// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: " . $conn->connect_error);
}

$message = ""; // ตัวแปรสำหรับเก็บข้อความแจ้งเตือน
$ptid = ""; // ตัวแปรสำหรับเก็บรหัสประเภทสินค้า
$ptname = ""; // ตัวแปรสำหรับเก็บชื่อประเภทสินค้า

// ตรวจสอบว่ามีการส่งข้อมูลจากฟอร์มหรือไม่
if (isset($_POST['Submit'])) {
    // ตรวจสอบว่ามีการป้อนรหัสและชื่อประเภทสินค้า
    if (!empty($_POST['ptid']) && !empty($_POST['ptname'])) {
        // รับค่าจากฟอร์ม
        $ptid = mysqli_real_escape_string($conn, $_POST['ptid']);
        $ptname = mysqli_real_escape_string($conn, $_POST['ptname']);

        // เพิ่มข้อมูลประเภทสินค้าใหม่
        $sql = "INSERT INTO `product_type` (`pt_id`, `pt_name`) VALUES ('$ptid', '$ptname');";

        if (mysqli_query($conn, $sql)) {
            // หากอัปเดตสำเร็จ
            echo "<script>alert('เพิ่มประเภทสินค้าสำเร็จ');</script>";
            echo "<script>window.location='indexttype.php';</script>";
            exit();
        } else {
            $message = "เกิดข้อผิดพลาดในการเพิ่มประเภทสินค้า: " . mysqli_error($conn);
        }
    } else {
        $message = "กรุณากรอกรหัสและชื่อประเภทสินค้า";
    }
}
// ปิดการเชื่อมต่อฐานข้อมูลถ้า $conn ไม่ใช่ null
if ($conn) {
    mysqli_close($conn);
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>IT Shop - เพิ่มประเภทสินค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@400;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Noto Sans Thai', sans-serif;
        }

        .container {
            margin-top: 100px;
            padding: 30px;
            width: 100%;
            max-width: 600px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
        }

        .form-label {
            font-weight: bold;
            color: #495057;
        }

        .btn {
            font-size: 1rem;
            padding: 10px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .btn-success {
            background-color: #28a745;
            border: none;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-md navbar-light bg-dark">
            <a class="navbar-brand text-light" href="indexproductadmin.php">IT Shop</a>
        </nav>
    </header>

    <div class="container">
        <h1>เพิ่มประเภทสินค้า</h1>
        <form method="post" action="">
            <div class="mb-3">
                <label for="ptid" class="form-label">รหัสประเภทสินค้า</label>
                <input type="text" name="ptid" id="ptid" class="form-control" value="<?= htmlspecialchars($ptid); ?>" required>
            </div>

            <div class="mb-3">
                <label for="ptname" class="form-label">ชื่อประเภทสินค้า</label>
                <input type="text" name="ptname" id="ptname" class="form-control" value="<?= htmlspecialchars($ptname); ?>" required>
            </div>
            
            <hr>
            
            <div class="mb-3 d-flex justify-content-center">
                <button type="submit" name="Submit" class="btn btn-success me-2">
                    <i class="bi bi-plus-circle"></i> เพิ่ม
                </button>
                
                <button type="reset" name="Reset" class="btn btn-danger me-2">
                    <i class="bi bi-x-circle"></i> ยกเลิก
                </button>
                
                <a href="indexttype.php" class="btn btn-secondary me-2">
                    <i class="bi bi-arrow-left"></i> กลับ
                </a>
            </div>
        </form>

        <?php if ($message): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    </div>

<script src="../../assets/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
