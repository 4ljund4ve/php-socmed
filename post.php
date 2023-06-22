<?php
// post.php

require_once('db.php');

if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // Retrieve the specific post from the database based on the post ID
    $sql = "SELECT p.*, COUNT(l.post_id) AS like_count
            FROM posts p
            LEFT JOIN likes l ON p.id = l.post_id
            WHERE p.id = $post_id
            GROUP BY p.id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Display the post details
        echo "<h2>" . $row["title"] . "</h2>";
        echo "<p>written by " . $row["author_id"] . "</p>";
        echo "<p>" . $row["content"] . "</p>";
        echo "<p>posted on: " . date("D, M j — h:iA", strtotime($row["created_at"])) . "</p>";
        echo "<p>Like count: " . $row["like_count"] . "</p>";
        // Add more details as needed

    } else {
        echo "Post not found.";
    }

    // Close the database connection
    $conn->close();
} else {
    echo "Invalid post ID.";
}
?>
