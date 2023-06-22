<?php
require_once('db.php');

// Check if user is logged in
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}
function removeEmojis($text) {
    // Remove emojis and other non-basic multilingual plane characters
    $regex = '/[\x{10000}-\x{10FFFF}]/u';
    $cleanText = preg_replace($regex, '', $text);
    return $cleanText;
}

$query = "SELECT total FROM posts";
$result = mysqli_query($conn, $query);
// Initialize an array to store error messages
$errors = array();


// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = htmlspecialchars($_POST["title"]);
    $content = htmlspecialchars($_POST["content"]);

    // Validate title and content
    if (strlen($title) < 1) {
        $errors[] = "Title must have at least 1 character.";
    }

    if (strlen($content) < 1) {
        $errors[] = "Content must have at least 1 character.";
    }

    // If there are no errors, proceed with post creation
    if (empty($errors)) {
        $username = $_SESSION["username"];
$title = str_replace(['"', '“', '”'], '', $title);
$title = removeEmojis($title);
$content = removeEmojis($content);
// Wrap links with <a> tags
$linkRegex = '/(?<!http:\/\/|https:\/\/)(?:www\.)?([^\s]+\.(?:com|net|org|edu|gov|mil|io|co|us|uk|de|ca|au)\S*)/i';
$content = preg_replace($linkRegex, '<a href="$0" id="in-link" target="_blank">$0</a>', $content);



        // Insert post data into the database
        $stmt = $conn->prepare("INSERT INTO posts (author_id, title, content) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $title, $content);
        
        if ($stmt->execute()) {
            // Increment the 'total' column by 1 in the posts table
            $updateStmt = $conn->prepare("UPDATE posts SET total = total + 1");
        
            $updateStmt = $conn->prepare("UPDATE users SET post_count = post_count + 1 WHERE username = ?");
            $updateStmt->bind_param("s", $username);
            $updateStmt->execute();
            
            
            // Redirect to existing_posts.php upon successful post creation
            header("Location: dashboard.php");
            exit();
        } else {
            $errors[] = "Error: " . $stmt->error;
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
     <meta charset="UTF-8">
    <title>Create Post</title>
    <link rel="stylesheet" type="text/css" href="css/create_post.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <script src="https://kit.fontawesome.com/52aeb6aca6.js" crossorigin="anonymous"></script>
    <link href='https://fonts.googleapis.com/css?family=Varela Round' rel='stylesheet'>
<body>
    <?php if (!empty($errors)): ?>
        <div class="error">
            <?php foreach ($errors as $error): ?>
                <p><?php echo $error; ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <div class="header">
        <h3>&nbsp;Hi, <span style="color: #f44336"><?php echo $_SESSION["username"]; ?></span>!</h3>
        <hr>
    </div>
    <div class="field">
        <form action="dashboard.php"><button class="baks"><i class="fa fa-circle-xmark"></i></button></form>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="title">&nbsp;Title:</label>
       <input type="text" name="title" required id="title-place"><br>
        <label for="content">&nbsp;Content:</label>
        <textarea name="content" required id="content-place" ></textarea><br>

        <button type="submit" value="PUBLISH" id="submit-btn">PUBLISH</button>
    </form>
</div>
</body>
</html>