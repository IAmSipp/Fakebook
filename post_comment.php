<?php
session_start();
require_once 'db.php'; // DATABASE CONNECTION

// REDIRECT TO LOGIN IF NOT LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];
    $content = $_POST['content'];

    // INSERT COMMENT INTO THE DATABASE
    $insert_query = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iis", $post_id, $user_id, $content);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Comment added successfully!";
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();

    header('Location: index.php'); // REDIRECT BACK TO THE POSTS PAGE
    exit();
}
