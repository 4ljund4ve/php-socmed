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
    header("Location: dashboard.php");
    exit();
}

// Retrieve the post data from the database using a prepared statement
$sql = "SELECT * FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 1) {
    $row = $result->fetch_assoc();

    // Check if the logged-in user is the author of the post
    if ($_SESSION["username"] !== $row["author_id"]) {
        // Redirect to the dashboard or post listing page if the user is not the author
        header("Location: dashboard.php");
        exit();
    }
} else {
    // Redirect to the dashboard or post listing page if the post is not found
    header("Location: dashboard.php");
    exit();
}

// Initialize an array to store error messages
$errors = array();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = ($_POST["title"]);
    $content = ($_POST["content"]);

    // Validate title and content
    if (strlen($title) < 3) {
        $errors[] = "Title must have at least 3 characters.";
    }

    if (strlen($content) < 15) {
        $errors[] = "Content must have at least 15 characters.";
    }

    // If there are no errors, update the post using a prepared statement
    if (empty($errors)) {
        $username = $_SESSION["username"];
        
        // Wrap links with <a> tags
$linkRegex = '/(?<!http:\/\/|https:\/\/)(?:www\.)?([^\s]+\.(?:com|net|org|edu|gov|mil|io|co|us|uk|de|ca|au)\S*)/i';
$content = preg_replace($linkRegex, '<a href="http://$0">$0</a>', $content);


        // Update post data in the database
        $sql = "UPDATE posts SET title = ?, content = ? WHERE id = ? AND author_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $title, $content, $post_id, $username);

        if ($stmt->execute()) {
            // Redirect to the dashboard or post listing page upon successful post creation or update
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Error updating post: " . $stmt->error;
        }
    }
}

// Close the prepared statement
$stmt->close();

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Post</title>
      <link rel="stylesheet" type="text/css" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Edit Post</h2>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?post_id=$post_id"; ?>">
        <label for="title">Title:</label>
        <input type="text" name="title" value="<?php echo htmlspecialchars($row["title"]); ?>" required><br><br>

        <label for="content">Content:</label><br>
        <textarea name="content" rows="20" required><?php echo htmlspecialchars($row["content"]); ?></textarea><br><br>

        <input type="submit" value="Update Post">
    </form>
    <p><a href="dashboard.php">Cancel</a></p>
</body>
</html>