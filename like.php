<?php
require_once('db.php');

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}
$errors = array();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_GET["post_id"])) {
        $postId = $_GET["post_id"];
        $username = $_SESSION["username"];
        
        // Check if the user has already liked the post
        $checkQuery = "SELECT * FROM likes WHERE post_id = $postId AND user_id = '$username'";
        $checkResult = $conn->query($checkQuery);
        
        if ($checkResult->num_rows == 0) {
            // User hasn't liked the post, insert a new like
            $insertQuery = "INSERT INTO likes (post_id, user_id) VALUES ($postId, '$username')";
            $insertResult = $conn->query($insertQuery);
            
            if ($insertResult) {
                // Increment the like count in the posts table
                $updateQuery = "UPDATE posts SET like_count = like_count + 1 WHERE id = $postId";
                $updateResult = $conn->query($updateQuery);
                
                if ($updateResult) {
                    // Like added successfully
                    echo json_encode(['status' => 'success', 'message' => 'Liked!']);
                   echo "<script>";
                    echo "document.getElementById('thumbs" . $postId . "').classList.add('liked');";
                    echo "</script>";
                    header("Location: dashboard.php");
                    exit();
                } else {
                    echo "Error updating like count: " . $conn->error;
                }
            } else {
                echo "Error inserting like: " . $conn->error;
            }
        } else {
            // User has already liked the post, remove the like
            $deleteQuery = "DELETE FROM likes WHERE post_id = $postId AND user_id = '$username'";
            $deleteResult = $conn->query($deleteQuery);

            if ($deleteResult) {
                // Decrement the like count in the posts table
                $updateQuery = "UPDATE posts SET like_count = like_count - 1 WHERE id = $postId";
                $updateResult = $conn->query($updateQuery);

                if ($updateResult) {
                    // Like removed successfully
                    echo "<script>";
                    echo "document.getElementById('thumbs" . $postId . "').classList.remove('liked');";
                    echo "</script>";
                    header("Location: dashboard.php");
                    exit();
                } else {
                    echo "Error updating like count: " . $conn->error;
                }
            } else {
                echo "Error removing like: " . $conn->error;
            }
        }
    }
}

// ...
?>
