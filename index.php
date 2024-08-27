<?php
session_start();
require_once 'db.php'; // DATABASE CONNECTION

// REDIRECT TO REGISTER IF NOT LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

$posts_query = "SELECT posts.*, users.username FROM posts JOIN users ON posts.owner_id = users.id ORDER BY created_at DESC";
$result = $conn->query($posts_query);
?>

<!-- Displaying posts -->
<?php while ($post = $result->fetch_assoc()): ?>
    <div class="post">
        <p><strong><?php echo htmlspecialchars($post['username']); ?></strong></p>
        <p><?php echo htmlspecialchars($post['content']); ?></p>
        <?php if ($post['image']): ?>
            <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" style="max-width: 100%;">
        <?php endif; ?>
        <p>Posted on: <?php echo $post['created_at']; ?></p>

        <!-- Edit and Delete options for the post owner -->
        <?php if ($_SESSION['user_id'] == $post['owner_id']): ?>
            <a href="edit_post.php?id=<?php echo $post['id']; ?>">Edit</a>
            <a href="delete_post.php?id=<?php echo $post['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
        <?php endif; ?>
    </div>
<?php endwhile; ?>