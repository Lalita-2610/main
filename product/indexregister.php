<?php
session_start();
include_once("connectdb.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $sex = $_POST['sex'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];

    // Check if the username already exists
    $checkSql = "SELECT * FROM users WHERE username = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $username);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Username already exists
        echo "Error: Username already taken. Please choose another one.";
    } else {
        // Insert into the database
        $sql = "INSERT INTO users (first_name, last_name, sex, username, password_hash, phone, email, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssss", $firstName, $lastName, $sex, $username, $password, $phone, $email, $address);

        if ($stmt->execute()) {
            // Registration successful
            $_SESSION['success_message'] = "Registration successful! You can now log in.";
            header("Location: indexlogin.php"); // Redirect to login page
            exit();
        } else {
            // Error handling
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $checkStmt->close();
}

$conn->close();
?>


<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <script src="../assets/js/color-modes.js"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.122.0">
    <title>Register</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/checkout/">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/dist/css/bootstrap.min.css">
    <style>
        .full-screen-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            background-color: #ffffff; /* เปลี่ยนสีพื้นหลังของ container เป็นสีขาว */
        }
        .card {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 800px;
        }
        .img-fluid1 {
            margin-left: -0px;
        }
        body {
            background-color: #ffffff; /* เปลี่ยนสีพื้นหลังของ body เป็นสีขาว */
        }
    </style>
    <link href="checkout.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid full-screen-container">
        <main>
            <div class="row py-5">
                <span class="col-md-4 d-flex justify-content-start align-items-center" style="position: relative; padding-left: 0;">
                    <img class="img-fluid1" src="images/logol.png" alt="" width="250" height="200">
                </span>
                <div class="col-md-8" style="padding-left: 0;">
                    <div class="card shadow" style="height: 100%;">
                        <div class="card-body">
                            <h2 class="title">Create an account</h2>
                            <h4 class="subtitle mb-3">Fill in the information to create an account</h4>
                            <p class="text-left">Already have a user account? <a class="link-offset-2 link-offset-3-hover link-underline link-underline-opacity-0 link-underline-opacity-75-hover" href="indexlogin.php">Click here</a></p>

                            <form class="needs-validation" method="POST" novalidate>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label for="firstName" class="form-label">First name</label>
                                        <input type="text" class="form-control" id="firstName" name="firstName" required>
                                        <div class="invalid-feedback">Valid first name is required.</div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label for="lastName" class="form-label">Last name</label>
                                        <input type="text" class="form-control" id="lastName" name="lastName" required>
                                        <div class="invalid-feedback">Valid last name is required.</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="sex" class="form-label">Sex</label>
                                        <select class="form-select" id="sex" name="sex" required>
                                            <option value="" disabled selected>Select...</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <div class="invalid-feedback">Please select a valid sex.</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="username" class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" required>
                                        <div class="invalid-feedback">Valid username is required.</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div class="invalid-feedback">Valid password is required.</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="phone" class="form-label">Phone</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" required>
                                        <div class="invalid-feedback">Valid phone number is required.</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                        <div class="invalid-feedback">Valid email is required.</div>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="address" class="form-label">Address</label>
                                        <input type="text" class="form-control" id="address" name="address" required>
                                        <div class="invalid-feedback">Valid address is required.</div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-warning w-100 mt-3">Create an account</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function () {
            'use strict';
            window.addEventListener('load', function () {
                var form = document.getElementsByClassName('needs-validation')[0];
                form.addEventListener('submit', function (event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            }, false);
        })();
    </script>
</body>
</html>
