<?php
session_start();
unset($_SESSION['success_message']);
require_once 'db.php'; // DATABASE CONNECTION

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_post'])) {
    $owner_id = $_SESSION['user_id']; // ASSUMING USER IS LOGGED IN AND THEIR ID IS STORED IN SESSION
    $content = $_POST['content'];
    $image = ''; // DEFAULT VALUE IF NO IMAGE IS UPLOADED

    // HANDLING IMAGE UPLOAD
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // ENSURE THE FILE IS AN IMAGE
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            $image_name = time() . '_' . basename($_FILES['image']['name']);
            $image_path = 'uploads/' . $image_name;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $image = $image_path; // STORE THE PATH TO THE UPLOADED IMAGE
            } else {
                $_SESSION['error_message'] = "Failed to upload image.";
                header('Location: create_post.php');
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Invalid image type. Please upload a JPG, PNG, or GIF.";
            header('Location: create_post.php');
            exit();
        }
    }

    // INSERT THE POST DATA INTO THE DATABASE
    $insert_query = "INSERT INTO posts (owner_id, content, image) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iss", $owner_id, $content, $image);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Post created successfully!";
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();

    header('Location: index.php');
    exit();
}
?>

<!-- HTML FORM FOR CREATING A POST -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>

<body>
    <div class="container mt-5">
        <h2>Create a New Post</h2>

        <!-- Display success or error messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $_SESSION['success_message'];
                unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION['error_message'];
                unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form action="create_post.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="content" class="form-label">What's on your mind?</label>
                <textarea name="content" id="content" class="form-control" rows="4" placeholder="Write your post here..." required></textarea>
            </div>
            <div class="mb-3">
                <label for="image" class="form-label">Upload Image</label>
                <input type="file" name="image" id="image" class="form-control">
            </div>
            <button type="submit" name="create_post" class="btn btn-primary">Post</button>
        </form>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>