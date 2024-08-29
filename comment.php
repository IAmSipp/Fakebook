<?php
session_start();
require_once 'db.php'; // Database connection

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the post_id from the URL
if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    header("Location: index.php");
    exit();
}

$post_id = (int)$_GET['post_id'];

// Fetch the post details
$post_query = "SELECT posts.*, users.username FROM posts JOIN users ON posts.owner_id = users.id WHERE posts.id = ?";
$stmt = $conn->prepare($post_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post_result = $stmt->get_result();
$post = $post_result->fetch_assoc();

if (!$post) {
    header("Location: index.php");
    exit();
}

// Fetch comments for the post
$comments_query = "SELECT comments.*, users.username AS commenter FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at ASC";
$stmt = $conn->prepare($comments_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments_result = $stmt->get_result();

// Fetch user data for sidebar
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments for Post</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-dark text-white p-3 vh-100 position-fixed">
                <div class="user-profile text-center mb-4">
                    <h4><?php echo "Profile: " . "<b>" . htmlspecialchars($user['username']) . "</b>"; ?></h4>
                </div>
                <div class="d-grid gap-2">
                    <a href="create_post.php" class="btn btn-primary mb-2">Create New Post</a>
                    <a href="logout.php" class="btn btn-danger">Log Out</a>
                </div>
            </nav>

            <!-- MAIN CONTENT -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-4 mt-3" style="margin-left: 250px;">
                <div class="post mb-4 p-3 border rounded bg-white">
                    <h4><?php echo htmlspecialchars($post['username']); ?></h4>
                    <p><?php echo htmlspecialchars($post['content']); ?></p>
                    <?php if ($post['image']): ?>
                        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="img-fluid">
                    <?php endif; ?>
                    <p class="text-muted">Posted on: <?php echo $post['created_at']; ?></p>
                </div>

                <h5>Comments</h5>
                <div class="comments-container mb-4">
                    <?php if ($comments_result->num_rows > 0): ?>
                        <?php while ($comment = $comments_result->fetch_assoc()): ?>
                            <div class="comment mb-2 p-3 border rounded bg-light">
                                <p><strong><?php echo htmlspecialchars($comment['commenter']); ?>:</strong></p>
                                <p><?php echo htmlspecialchars($comment['content']); ?></p>
                                <p class="text-muted">Commented on: <?php echo $comment['created_at']; ?></p>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card text-center p-4">
                            <div class="card-body">
                                <h5 class="card-title">No Comments Yet</h5>
                                <p class="card-text">Be the first to comment on this post!</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Comment Form -->
                <div class="comment-form mb-4">
                    <form method="POST" action="post_comment.php">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <div class="mb-3">
                            <label for="commentContent" class="form-label">Add a Comment</label>
                            <textarea name="content" id="commentContent" class="form-control" rows="3" placeholder="Write your comment here..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>

                <a href="index.php" class="btn btn-secondary">Back to Posts</a>
            </main>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>