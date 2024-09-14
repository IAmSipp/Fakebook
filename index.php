<?php
session_start();
require_once 'db.php'; // DATABASE CONNECTION

// REDIRECT TO REGISTER IF NOT LOGGED IN
if (!isset($_SESSION['user_id'])) {
    header("Location: register.php");
    exit();
}

// FETCHING POSTS
$posts_query = "SELECT posts.*, users.username FROM posts JOIN users ON posts.owner_id = users.id ORDER BY created_at DESC";
$result = $conn->query($posts_query);

// FETCHING USER DATA FOR SIDEBAR
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
    <title>Posts</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Custom CSS for Facebook-like styling */
        body {
            background-color: #f0f2f5;
        }

        .navbar {
            background-color: #4267b2;
            color: white;
            padding-left: 20%;
        }

        .navbar a {
            color: white;
            font-weight: bold;
        }

        .sidebar {
            background-color: white;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            padding-top: 20px;
            border-right: 1px solid #ddd;
        }

        .main-content {
            margin-left: 20%;
            margin-top: 20px;
        }

        .posts-container {
            margin-left: 3%;
        }

        .post {
            background-color: white;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px;
        }

        .post img {
            max-width: 100%;
            border-radius: 8px;
        }

        .post .btn {
            margin-top: 10px;
        }

        .comment-form textarea {
            text-transform: uppercase;
        }

        .post-comments {
            text-transform: uppercase;
            font-weight: bold;
        }

        .card {
            background-color: white;
            border-radius: 8px;
        }

        .card-body {
            padding: 20px;
        }

        .btn-primary,
        .btn-danger,
        .btn-secondary {
            font-weight: bold;
        }

        .btn-close {
            color: white;
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

    <div class="container-fluid">
        <div class="row">
            <!-- SIDEBAR -->
            <nav class="sidebar">
                <div class="user-profile text-center mb-4">
                    <img src="https://via.placeholder.com/150" alt="User" class="img-fluid rounded-circle mb-3" style="width: 120px;">
                    <h4><?php echo "Profile: " . "<b>" . htmlspecialchars($user['username']) . "</b>"; ?></h4>
                    <hr>
                </div>
                <div class="d-grid gap-2">
                    <a href="create_post.php" class="btn btn-primary mb-2">Create New Post</a>
                    <a href="login.php" class="btn btn-danger">Log Out</a>
                </div>
            </nav>

            <!-- MAIN CONTENT -->
            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-4 mt-3">
                <div class="posts-container">
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($post = $result->fetch_assoc()): ?>
                            <?php
                            // CHECK IF THE CURRENT USER HAS LIKED THIS POST
                            $like_check_query = "SELECT * FROM post_likes WHERE user_id = ? AND post_id = ?";
                            $stmt = $conn->prepare($like_check_query);
                            $stmt->bind_param("ii", $_SESSION['user_id'], $post['id']);
                            $stmt->execute();
                            $user_liked = $stmt->get_result()->num_rows > 0;
                            ?>
                            <div class="post mb-4 p-3 border rounded bg-white">
                                <p><strong><?php echo htmlspecialchars($post['username']); ?></strong></p>
                                <p><?php echo htmlspecialchars($post['content']); ?></p>
                                <?php if ($post['image']): ?>
                                    <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image" class="img-fluid">
                                <?php endif; ?>
                                <p class="text-muted">Posted on: <?php echo $post['created_at']; ?></p>

                                <!-- LIKE/UNLIKE BUTTON -->
                                <form method="POST" action="like_post.php" class="d-inline">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="btn btn-<?php echo $user_liked ? 'danger' : 'primary'; ?> btn-sm">
                                        <?php echo $user_liked ? 'Unlike' : 'Like'; ?> (<?php echo $post['likes']; ?>)
                                    </button>
                                </form>

                                <!-- EDIT AND DELETE OPTIONS FOR THE POST OWNER -->
                                <?php if ($_SESSION['user_id'] == $post['owner_id']): ?>
                                    <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                                <?php endif; ?>

                                <!-- COMMENT FORM -->
                                <div class="comment-form mt-1">
                                    <form method="POST" action="post_comment.php">
                                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                        <div class="mb-1">
                                            <textarea name="content" id="comment" class="form-control" rows="2" placeholder="WRITE YOUR COMMENT HERE..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary py-0"><small>Submit</small></button>
                                    </form>
                                </div>

                                <!-- SHOW MORE COMMENTS BUTTON -->
                                <div class="mt-1 post-comments">
                                    <a href="comment.php?post_id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm p-0 px-1">SHOW MORE COMMENTS ></a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card text-center p-4">
                            <div class="card-body">
                                <h5 class="card-title">No content</h5>
                                <p class="card-text">There are no posts available at the moment. Be the first to create one!</p>
                                <a href="create_post.php" class="btn btn-primary">Create New Post</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>

    <script src="js/bootstrap.bundle.min.js"></script>
</body>

</html>