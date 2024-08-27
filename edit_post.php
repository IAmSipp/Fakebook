<?php
session_start();
require_once 'db.php'; // DATABASE CONNECTION

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$post_id = $_GET['id'];

// FETCH THE POST TO EDIT
$post_query = "SELECT * FROM posts WHERE id = ? AND owner_id = ?";
$stmt = $conn->prepare($post_query);
$stmt->bind_param("ii", $post_id, $_SESSION['user_id']);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();

if (!$post) {
    $_SESSION['error_message'] = "You are not authorized to edit this post.";
    header('Location: index.php');
    exit();
}

// HANDLE THE POST UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_post'])) {
    $content = $_POST['content'];
    $image = $post['image']; // KEEP THE EXISTING IMAGE BY DEFAULT

    // HANDLE NEW IMAGE UPLOAD
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $image_name = time() . '_' . $_FILES['image']['name'];
        $image_path = 'uploads/' . $image_name;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
            $image = $image_path; // UPDATE WITH THE NEW IMAGE PATH
        }
    }

    $update_query = "UPDATE posts SET content = ?, image = ? WHERE id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi", $content, $image, $post_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Post updated successfully!";
    } else {
        $_SESSION['error_message'] = "Error: " . $stmt->error;
    }

    header('Location: index.php');
    exit();
}
?>

<!-- HTML FORM FOR EDITING THE POST -->
<form action="edit_post.php?id=<?php echo $post_id; ?>" method="POST" enctype="multipart/form-data">
    <textarea name="content" required><?php echo htmlspecialchars($post['content']); ?></textarea>
    <input type="file" name="image">
    <button type="submit" name="edit_post">Save Changes</button>
</form>
