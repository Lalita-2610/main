<?php
    include("connectdb.php");

    // ฟังก์ชันดึงชื่อประเภทสินค้า
    function getCategoryName($conn, $pt_id) {
        $sql = "SELECT pt_name FROM product_type WHERE pt_id = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $pt_id);
            $stmt->execute();
            $stmt->bind_result($pt_name);
            if ($stmt->fetch()) {
                $stmt->close();
                return htmlspecialchars($pt_name, ENT_QUOTES, 'UTF-8');
            }
            $stmt->close();
        }
        return "ไม่ระบุประเภท";
    }

    // รับค่าจาก GET สำหรับการค้นหาและการกรองตามประเภท
    $kw = isset($_GET['search']) ? trim($_GET['search']) : '';
    $pt_id = isset($_GET['pt_id']) ? intval($_GET['pt_id']) : 0;

    // สร้างฐานของ SQL query
    $sql = "SELECT * FROM product WHERE 1";

    // สร้างอาเรย์สำหรับเก็บเงื่อนไขเพิ่มเติม
    $conditions = [];
    $params = [];
    $types = "";

    // เพิ่มเงื่อนไขการค้นหา
    if ($kw !== '') {
        $conditions[] = "(p_name LIKE CONCAT('%', ?, '%') OR p_detail LIKE CONCAT('%', ?, '%'))";
        $params[] = $kw;
        $params[] = $kw;
        $types .= "ss";
    }

    // เพิ่มเงื่อนไขการกรองตามประเภท
    if ($pt_id > 0) {
        $conditions[] = "pt_id = ?";
        $params[] = $pt_id;
        $types .= "i";
    }

    // ถ้ามีเงื่อนไขเพิ่มเติม ให้ต่อเข้ากับ SQL query
    if (count($conditions) > 0) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    // เตรียมคำสั่ง SQL ด้วย prepared statements
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        if (count($params) > 0) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        echo "<p>เกิดข้อผิดพลาดในการเตรียมคำสั่ง SQL: " . $conn->error . "</p>";
        exit;
    }
?>
<!doctype html>
<html lang="th" data-bs-theme="auto">
<head>
    <script src="../assets/js/color-modes.js"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&display=swap" rel="stylesheet">
    <style>
        /* สไตล์ที่คุณมีอยู่ */
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }
        /* สไตล์สำหรับหมวดหมู่ */
        .category-item {
            display: inline-block;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .category-circle {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            overflow: hidden;
            position: relative;
            background-color: #f8f9fa; /* สีพื้นหลังของกรอบหมวดหมู่ */
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .category-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            /* ลบกรอบภาพ */
            border: none;
        }

        .category-text {
            margin-top: 10px;
            font-weight: 600;
            color: #343a40; /* สีข้อความ */
            font-family: 'Kanit', sans-serif;
        }

        /* เอฟเฟกต์เมื่อเอาเมาส์ไปชี้ */
        .category-item:hover .category-circle {
            transform: scale(1.05);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        /* สไตล์สำหรับหมวดหมู่ที่ถูกเลือก */
        .category-circle.active {
            border: 2px solid #ffc107; /* กรอบสีเหลือง */
            box-shadow: 0 0 10px rgba(255, 193, 7, 0.5); /* เงาสีเหลือง */
        }
        .card {
            transition: transform 0.3s ease; /* ระยะเวลาในการขยาย */
        }

        .card:hover {
            transform: scale(1.03); /* ขยายการ์ดเมื่อชี้ */
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3); /* เพิ่มเงา */
        }
		.modal-custom {
			max-width: 70%; /* ปรับความกว้างสูงสุด */
		}

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }
		body {
        	font-family: 'Kanit', sans-serif;
    	}

        .b-example-divider {
            width: 100%;
            height: 3rem;
            background-color: rgba(0, 0, 0, .1);
            border: solid rgba(0, 0, 0, .15);
            border-width: 1px 0;
            box-shadow: inset 0 .5em 1.5em rgba(0, 0, 0, .1), inset 0 .125em .5em rgba(0, 0, 0, .15);
        }

        .b-example-vr {
            flex-shrink: 0;
            width: 1.5rem;
            height: 100vh;
        }

        .bi {
            vertical-align: -.125em;
            fill: currentColor;
        }

        .nav-scroller {
            position: relative;
            z-index: 2;
            height: 2.75rem;
            overflow-y: hidden;
        }

        .nav-scroller .nav {
            display: flex;
            flex-wrap: nowrap;
            padding-bottom: 1rem;
            margin-top: -1px;
            overflow-x: auto;
            text-align: center;
            white-space: nowrap;
            -webkit-overflow-scrolling: touch;
        }

        .btn-bd-primary {
            --bd-violet-bg: #712cf9;
            --bd-violet-rgb: 112.520718, 44.062154, 249.437846;

            --bs-btn-font-weight: 600;
            --bs-btn-color: var(--bs-white);
            --bs-btn-bg: var(--bd-violet-bg);
            --bs-btn-border-color: var(--bd-violet-bg);
            --bs-btn-hover-color: var(--bs-white);
            --bs-btn-hover-bg: #6528e0;
            --bs-btn-hover-border-color: #6528e0;
            --bs-btn-focus-shadow-rgb: var(--bd-violet-rgb);
            --bs-btn-active-color: var(--bs-btn-hover-color);
            --bs-btn-active-bg: #5a23c8;
            --bs-btn-active-border-color: #5a23c8;
        }

        .bd-mode-toggle {
            z-index: 1500;
        }

        .bd-mode-toggle .dropdown-menu .active .bi {
            display: block !important;
        }

        /* สไตล์สำหรับ Modal */
        .modal-img {
            width: 100%;
            height: auto;
        }
    </style>
</head>
<body>
    <!-- SVG symbols -->
    <svg xmlns="http://www.w3.org/2000/svg" class="d-none">
      <symbol id="check2" viewBox="0 0 16 16">
        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
      </symbol>
      <symbol id="circle-half" viewBox="0 0 16 16">
        <path d="M8 15A7 7 0 1 0 8 1v14zm0 1A8 8 0 1 1 8 0a8 8 0 0 1 0 16z"/>
      </symbol>
      <symbol id="moon-stars-fill" viewBox="0 0 16 16">
        <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
        <path d="M10.794 3.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387a1.734 1.734 0 0 0-1.097 1.097l-.387 1.162a.217.217 0 0 1-.412 0l-.387-1.162A1.734 1.734 0 0 0 9.31 6.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387a1.734 1.734 0 0 0 1.097-1.097l.387-1.162zM13.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.156 1.156 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.156 1.156 0 0 0-.732-.732l-.774-.258a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732L13.863.1z"/>
      </symbol>
      <symbol id="sun-fill" viewBox="0 0 16 16">
        <path d="M8 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
      </symbol>
    </svg>
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

    <main>
        <!-- ส่วนค้นหา -->
        <section class="py-5 text-center container">
            <div class="row py-lg-5">
                <div class="col-lg-6 col-md-8 mx-auto">
                    <form class="d-flex align-items-center col-12 col-lg-auto mb-3 mb-lg-0 me-lg-3" 
                          role="search" action="indexproduct.php" method="GET">
                        <span class="me-2">ค้นหา</span>
                        <input type="text" class="form-control me-2" name="search" placeholder="ค้นหา..." 
                               aria-label="Search" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search'], ENT_QUOTES, 'UTF-8') : ''; ?>">
                        <?php
                            if (isset($_GET['pt_id']) && intval($_GET['pt_id']) > 0) {
                                echo '<input type="hidden" name="pt_id" value="' . intval($_GET['pt_id']) . '">';
                            }
                        ?>
                        <button class="btn btn-outline-warning" type="submit" name="Submit" aria-label="Search">
                            <i class="bi bi-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </section>
        
        <!-- หมวดหมู่สินค้า -->
        <div class="container marketing">
            <div class="row d-flex flex-wrap justify-content-center text-center" style="gap: 40px;">
                <!-- Template สำหรับหมวดหมู่ -->
                <?php
                    // กำหนดหมวดหมู่ในรูปแบบของอาเรย์
                    $categories = [
                        ['id' => 1, 'name' => 'Mobile', 'image' => 'tele.png'],
                        ['id' => 2, 'name' => 'Laptop', 'image' => 'lt.png'],
                        ['id' => 4, 'name' => 'Tablet', 'image' => 'teb.png'],
                        ['id' => 3, 'name' => 'Smart Watch', 'image' => 'sm.png'],
                        ['id' => 5, 'name' => 'Accessories', 'image' => 'acc.png']
                    ];

                    foreach ($categories as $category) {
                        // ตรวจสอบว่าหมวดหมู่ปัจจุบันถูกเลือกหรือไม่
                        $activeClass = ($pt_id === $category['id']) ? 'active' : '';

                        echo '
                        <div class="col-auto">
                            <a href="indexproduct.php?pt_id=' . urlencode($category['id']) . '" class="text-decoration-none category-item">
                                <div class="category-circle ' . $activeClass . '">
                                    <img src="images/' . htmlspecialchars($category['image'], ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '" class="category-image">
                                    <p class="mt-2 category-text">' . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . '</p>
                                </div>
                            </a>
                        </div><!-- /.col-auto -->
                        ';
                    }
                ?>
                <!-- Template สำหรับหมวดหมู่สิ้นสุด -->
            </div><!-- /.row -->
        </div><!-- /.container -->

        <!-- ส่วนแสดงสินค้า -->
        <div class="album py-5 bg-body-tertiary">
            <div class="container">
                <h1 class="text-start" style="margin-bottom: 20px; margin-left: 0px; font-family: 'Kanit', sans-serif;">ผลิตภัณฑ์ของเรา</h1>
                
                <?php if ($pt_id > 0): ?>
                    <h2 style="margin-bottom: 20px; font-family: 'Kanit', sans-serif;">
                        สินค้าประเภท: <span class="text-secondary"><?= getCategoryName($conn, $pt_id); ?></span>
                    </h2>
                <?php endif; ?>

                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4 mt-4">
                    <?php
                        if ($result->num_rows > 0) {
                            while ($data = $result->fetch_assoc()) {
                                // ตรวจสอบการมีอยู่ของภาพสินค้า
                                $imagePath = "images/" . htmlspecialchars($data['p_id'], ENT_QUOTES, 'UTF-8') . "." . htmlspecialchars($data['p_picture'], ENT_QUOTES, 'UTF-8');
                                if (!file_exists($imagePath)) {
                                    $imagePath = "images/default.jpg"; // กำหนดภาพเริ่มต้นถ้าไม่มีภาพสินค้า
                                }

                                // เตรียมข้อมูลสำหรับ Modal
                                $product_id = htmlspecialchars($data['p_id'], ENT_QUOTES, 'UTF-8');
                                $product_name = htmlspecialchars($data['p_name'], ENT_QUOTES, 'UTF-8');
                                $product_detail = htmlspecialchars($data['p_detail'], ENT_QUOTES, 'UTF-8');
                                $product_price = number_format($data['p_price'], 2);
                    ?>  
                    <div class="col d-flex align-items-stretch">
                        <div class="card shadow-sm w-100" data-bs-toggle="modal" data-bs-target="#productModal<?= $product_id; ?>" style="cursor: pointer;">
                            <img src="<?= $imagePath; ?>" class="product-image card-img-top img-fluid" 
                                 alt="<?= $product_name; ?>" loading="lazy">
                            <div class="card-body d-flex flex-column">
                                <p class="card-text mb-4">
                                    <?= $product_name; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <span>
                                        <?= $product_price; ?> บาท
                                    </span>
									<a href="cart.php?pid=<?= urlencode($data['p_id']); ?>&qty=1" class="btn btn-sm btn-dark cart-button">
										<i class="bi bi-cart"></i>
									</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal สำหรับรายละเอียดสินค้า -->
					<div class="modal fade" id="productModal<?= $product_id; ?>" tabindex="-1" aria-labelledby="productModalLabel<?= $product_id; ?>" aria-hidden="true">
						<div class="modal-dialog modal-custom modal-dialog-centered"> <!-- ใช้ modal-custom เพื่อขยายขนาด -->
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body d-flex"> <!-- ใช้ d-flex เพื่อจัดเรียงแนวนอน -->
									<!-- รูปภาพอยู่ทางซ้าย -->
									<div class="col-md-6">
										<img src="<?= $imagePath; ?>" class="img-fluid modal-img" alt="<?= $product_name; ?>">
									</div>
									<!-- ข้อความอยู่ทางขวา -->
									<div class="col-md-6">
										<h5 class="text-start"><?= $product_name; ?></h5> <!-- ชื่อสินค้าด้านบน -->
										<p class="text-start"><?= nl2br($product_detail); ?></p> <!-- รายละเอียดสินค้า -->

										<!-- ราคาลงไปที่ด้านล่าง -->
										<h4 class="mb-0">ราคา: <?= $product_price; ?> บาท</h4>

										<!-- ปรับจำนวนสินค้าและปุ่มเพิ่มลงตะกร้า -->
<div class="d-flex justify-content-between align-items-center mt-4"> <!-- ใช้ d-flex เพื่อจัดเรียงแนวนอน -->
    <div class="d-flex align-items-center">
        <button class="btn btn-warning decreaseQty" data-product-id="<?= $data['p_id']; ?>">-</button>
        <input type="number" id="qty<?= $product_id; ?>" value="1" class="form-control mx-2" style="width: 60px;" min="1" readonly>
        <button class="btn btn-warning increaseQty" data-product-id="<?= $data['p_id']; ?>">+</button>
    </div>

    <!-- ปุ่มเพิ่มลงตะกร้า -->
    <a href="cart.php?pid=<?= urlencode($data['p_id']); ?>&qty=1" class="btn btn-dark addToCart">
        <i class="bi bi-cart"></i> เพิ่มลงตะกร้า
    </a>
</div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
								</div>
							</div>
						</div>
					</div>
                    <?php 
                            }
                        } else {
                            echo "<p>ไม่พบสินค้าที่ค้นหา กรุณาลองใหม่อีกครั้ง</p>";
                        }
                        $stmt->close();
                        $conn->close();
                    ?>
                </div>
            </div>
        </div>
    </main>
	<script>
		// สคริปต์สำหรับเพิ่มและลดจำนวนสินค้า
		document.addEventListener('DOMContentLoaded', function() {
			document.querySelectorAll('.increaseQty').forEach(button => {
				button.addEventListener('click', function() {
					const qtyInput = this.closest('.modal-body').querySelector('input[type="number"]');
					qtyInput.value = parseInt(qtyInput.value) + 1; // เพิ่มจำนวน
					updateAddToCartLink(this);
				});
			});

			document.querySelectorAll('.decreaseQty').forEach(button => {
				button.addEventListener('click', function() {
					const qtyInput = this.closest('.modal-body').querySelector('input[type="number"]');
					if (qtyInput.value > 1) {
						qtyInput.value = parseInt(qtyInput.value) - 1; // ลดจำนวน
						updateAddToCartLink(this);
					}
				});
			});
		});

		function updateAddToCartLink(button) {
			const qtyInput = button.closest('.modal-body').querySelector('input[type="number"]');
			const productId = button.dataset.productId; // ดึง product_id จาก data-* attribute
			const addToCartButton = button.closest('.modal-body').querySelector('.addToCart'); // ค้นหา addToCart button

			addToCartButton.href = 'cart.php?pid=' + encodeURIComponent(productId) + '&qty=' + qtyInput.value; // อัปเดตลิงค์เพิ่มลงตะกร้า
		}
	</script>

    <!-- Footer -->
    <footer class="text-body-secondary py-5">
        <div class="container">
            <p class="float-end mb-1">
                <a href="#">กลับขึ้นด้านบน</a>
            </p>
            <p class="mb-1">&copy; IT Shop 2024</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="../assets/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
