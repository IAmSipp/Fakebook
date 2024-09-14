<?php
session_start();
require_once 'db.php'; // INCLUDE DATABASE CONNECTION

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $sign = $_POST['sign'];
    $password = $_POST['password'];

    // CHECK IF LOGIN IS EMAIL OR USERNAME
    $login_query = "SELECT * FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($login_query);
    $stmt->bind_param("ss", $sign, $sign);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit();
        } else {
            $_SESSION['warning_message'] = "Invalid password!";
        }
    } else {
        $_SESSION['warning_message'] = "Email not found!";
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h3>Login</h3>
                    </div>
                    <?php if (isset($_SESSION['warning_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['warning_message']; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['warning_message']); ?>
                    <?php endif; ?>
                    <div class="card-body">
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label for="login" class="form-label">Email address or Username</label>
                                <input type="text" class="form-control" id="sign" name="sign" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" name="login" class="btn btn-primary">Login</button>
                            </div>
                        </form>
                        <div class="text-center mt-3">
                            <p class="mb-0">Don't have an account yet?</p>
                            <p>
                                <a href="register.php" class="link-primary">Sign Up</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Team Members</h5>
                    <ul class="list-unstyled">
                        <li>ไชยวัฒน์ จัดเจนนาวี 610-12</li>
                        <li>สิปปกร จันทร์พุ่ม 610-22</li>
                        <li>ชุติเดช เทิดสถิตบุญฤทธิ์ 610-26</li>
                        <li>อภิชญา ตะโกจีน 610-33</li>
                        <li>นัทธมน วชิรสุดเลขา 610-34</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Contact Us</h5>
                    <p>Feel free to reach out to any team member!</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <h5>Follow Us</h5>
                    <ul class="list-inline">
                        <li class="list-inline-item"><a href="#" class="text-light"><i class="bi bi-facebook"></i> Facebook</a></li>
                        <li class="list-inline-item"><a href="#" class="text-light"><i class="bi bi-github"></i> GitHub</a></li>
                    </ul>
                </div>
            </div>
            <div class="text-center pt-3">
                <small>&copy; 2024 Fakebook. All Rights Reserved.</small>
            </div>
        </div>
    </footer>



</body>

</html>