<?php
session_start();
require_once 'db.php';

// REDIRECT TO LOGIN IF NOT LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// GET THE POST_ID FROM THE URL
if (!isset($_GET['post_id']) || !is_numeric($_GET['post_id'])) {
    header("Location: index.php");
    exit();
}

$post_id = (int)$_GET['post_id'];

// FETCH THE POST DETAILS
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

// FETCH COMMENTS FOR THE POST
$comments_query = "SELECT comments.*, users.username AS commenter FROM comments JOIN users ON comments.user_id = users.id WHERE comments.post_id = ? ORDER BY comments.created_at ASC";
$stmt = $conn->prepare($comments_query);
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments for Post</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: #4267b2;
            color: white;
        }

        .navbar a {
            color: white;
            font-weight: bold;
        }

        .container {
            margin-top: 20px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
        }

        .btn-primary,
        .btn-secondary {
            font-weight: bold;
        }

        .back-arrow {
            font-size: 1.2rem;
            text-decoration: none;
            color: #4267b2;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 10px;
        }

        .back-arrow:hover {
            text-decoration: underline;
        }

        .post,
        .comments-container,
        .comment-form {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .comment {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Fakebook</a>
        </div>
    </nav>

    <div class="container">
        <!-- BACK ARROW LINK -->
        <a href="index.php" class="back-arrow">&larr; Back to Posts</a>

        <div class="post mb-4 p-3 border rounded">
            <h4><?php echo htmlspecialchars($post['username']); ?></h4>
            <p><?php echo htmlspecialchars($post['content']); ?></p>
            <?php if ($post['image']): ?>
                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="img-fluid">
            <?php endif; ?>
            <p class="text-muted">Posted on: <?php echo $post['created_at']; ?></p>
        </div>

        <h5>Comments</h5>
        <div class="comments-container">
            <?php if ($comments_result->num_rows > 0): ?>
                <?php while ($comment = $comments_result->fetch_assoc()): ?>
                    <div class="comment mb-2">
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

        <!-- COMMENT FORM -->
        <div class="comment-form">
            <form method="POST" action="post_comment.php">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <div class="mb-3">
                    <label for="commentContent" class="form-label">Add a Comment</label>
                    <textarea name="content" id="commentContent" class="form-control" rows="3" placeholder="Write your comment here..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>