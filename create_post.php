<?php
session_start();
require_once 'db.php'; // DATABASE CONNECTION

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_post'])) {
    $owner_id = $_SESSION['user_id']; // ASSUMING USER IS LOGGED IN AND THEIR ID IS STORED IN SESSION
    $content = $_POST['content'];
    $image = ''; // DEFAULT VALUE IF NO IMAGE IS UPLOADED

    // HANDLING IMAGE UPLOAD
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        $image_path = 'uploads/' . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_path; // STORE THE PATH TO THE UPLOADED IMAGE
        }
    }

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
<form action="create_post.php" method="POST" enctype="multipart/form-data">
    <textarea name="content" placeholder="What's on your mind?" required></textarea>
    <input type="file" name="image">
    <button type="submit" name="create_post">Post</button>
</form>