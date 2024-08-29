<?php
session_start();
require_once 'db.php'; // DATABASE CONNECTION

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_id'])) {
    $post_id = $_POST['post_id'];
    $user_id = $_SESSION['user_id'];

    // CHECK IF USER HAS ALREADY LIKED THIS POST
    $check_like_query = "SELECT * FROM post_likes WHERE user_id = ? AND post_id = ?";
    $stmt = $conn->prepare($check_like_query);
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    $like_exists = $stmt->get_result()->num_rows > 0;

    if ($like_exists) {
        // IF LIKE EXISTS, UNLIKE THE POST
        $delete_like_query = "DELETE FROM post_likes WHERE user_id = ? AND post_id = ?";
        $stmt = $conn->prepare($delete_like_query);
        $stmt->bind_param("ii", $user_id, $post_id);
        if ($stmt->execute()) {
            // DECREMENT THE LIKE COUNT IN THE POSTS TABLE
            $update_post_query = "UPDATE posts SET likes = likes - 1 WHERE id = ?";
            $stmt = $conn->prepare($update_post_query);
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
        }
    } else {
        // IF LIKE DOESN'T EXIST, LIKE THE POST
        $insert_like_query = "INSERT INTO post_likes (user_id, post_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insert_like_query);
        $stmt->bind_param("ii", $user_id, $post_id);
        if ($stmt->execute()) {
            // INCREMENT THE LIKE COUNT IN THE POSTS TABLE
            $update_post_query = "UPDATE posts SET likes = likes + 1 WHERE id = ?";
            $stmt = $conn->prepare($update_post_query);
            $stmt->bind_param("i", $post_id);
            $stmt->execute();
        }
    }

    // REDIRECT BACK TO POSTS PAGE
    header("Location: index.php");
    exit();
}
