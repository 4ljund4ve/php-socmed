<?php
require_once('db.php');

// Check if user is logged in
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

// Get the post ID from the URL parameter
if (isset($_GET["post_id"])) {
    $post_id = $_GET["post_id"];
} else {
    // Redirect to the dashboard or post listing page if no post ID is provided
    header("Location: index.php");
    exit();
}

// Retrieve the post data from the database
$sql = "SELECT * FROM posts WHERE id = '$post_id'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();
} else {
    // Redirect to the dashboard or post listing page if the post is not found
    header("Location: index.php");
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_SESSION["username"];
    $comment = $_POST["comment"];
    
    // Insert the comment into the database
    $sql_insert = "INSERT INTO comments (post_id, username, comment) VALUES ('$post_id', '$username', '$comment')";
    
    if ($conn->query($sql_insert) === TRUE) {
        // Redirect back to the comment page after successful comment submission
        header("Location: comment.php?post_id=$post_id");
        exit();
    } else {
        echo "Error adding comment: " . $conn->error;
    }
}

// Retrieve the comments for the post from the database
$sql_comments = "SELECT * FROM comments WHERE post_id = '$post_id' ORDER BY created_at DESC";
$result_comments = $conn->query($sql_comments);

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Comments</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Post Details</h2>
    <h3>Title: <?php echo $row["title"]; ?></h3>
    <p>Content: <?php echo $row["content"]; ?></p>
    <p>By: <?php echo $row["author_id"]; ?>
    
    <hr>
    
    <h2>Comments</h2>
    <?php if ($result_comments->num_rows > 0): ?>
        <?php while ($comment_row = $result_comments->fetch_assoc()): ?>
            <div>
                <p>Username: <?php echo $comment_row["username"]; ?></p>
                <p>Comment: <?php echo $comment_row["comment"]; ?></p>
<p>posted on: <?php echo date("M j, Y h:i A", strtotime($comment_row["created_at"])); ?></p>
            </div>
            <hr>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No comments yet.</p>
    <?php endif; ?>
    
    <h2>Add a Comment</h2>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?post_id=$post_id"; ?>">
        <textarea name="comment" rows="5" required></textarea><br><br>
        <input type="submit" value="Post Comment">
    </form>
    <p><a href="dashboard.php">Back</a></p>
</body>
</html>
