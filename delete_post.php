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

// Retrieve the post data from the database
$sql = "SELECT * FROM posts WHERE id = '$post_id'";
$result = $conn->query($sql);

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

// Process post deletion
if ($_SERVER["REQUEST_METHOD"] == "POST") {
   $sql_delete = "DELETE FROM posts WHERE id = '$post_id'";
    
    if ($conn->query($sql_delete) === TRUE) {
        // Redirect to the dashboard or post listing page upon successful deletion
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error deleting post: " . $conn->error;
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Post</title>
       <link rel="stylesheet" type="text/css" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <h2>Delete Post</h2>
    <p>Are you sure you want to delete the post titled: "<?php echo $row["title"]; ?>"?</p>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?post_id=$post_id"; ?>">
        <input type="submit" value="Delete">
    </form>
    <p><a href="dashboard.php">Cancel</a></p>
</body>
</html>
