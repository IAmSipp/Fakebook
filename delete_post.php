<?php
session_start();
require_once 'db.php'; // DATABASE CONNECTION

if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // DELETE THE POST ONLY IF THE USER IS THE OWNER
    $delete_query = "DELETE FROM posts WHERE id = ? AND owner_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("ii", $post_id, $_SESSION['user_id']);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Post deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }

    header('Location: index.php');
    exit();
}
