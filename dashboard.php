<?php
require_once('db.php');

session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit();
}

// Retrieve posts from the database in descending order of creation timestamp
$sql = "SELECT p.*, COUNT(l.post_id) AS like_count, u.pcount
        FROM posts p
        LEFT JOIN likes l ON p.id = l.post_id
        LEFT JOIN (
            SELECT author_id, COUNT(id) AS pcount
            FROM posts
            GROUP BY author_id
        ) u ON p.author_id = u.author_id
        GROUP BY p.id
        ORDER BY p.is_sticky DESC, p.created_at DESC";
$result = $conn->query($sql);

function getAuthorColorClass($postCount) {
    if ($postCount >= 0 && $postCount <= 5) {
        return "author-color-1";
        
    } elseif ($postCount >= 21 && $postCount <= 50) {
        return "intermediate";
        
    } elseif ($postCount >= 51 && $postCount <= 100) {
        return "advanced";
        
    } elseif ($postCount >= 101 && $postCount <= 500) {
         return "superior";
         
    } elseif ($postCount >= 501 && $postCount <= 999) {
        return "distinguished";
         
    } elseif ($postCount >= 1000 && $postCount <= 2000) {
        return "admin-color";
    }
    
    // Default color class
    return "";
}


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" type="text/css" href="css/dashboard.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" /> -->
   <script src="https://kit.fontawesome.com/52aeb6aca6.js" crossorigin="anonymous"></script>
    <link href='https://fonts.googleapis.com/css?family=Varela Round' rel='stylesheet'>
    <style>

    </style>
</head>
<body>
    <div class="fixed-container">
        <!-- create post button -->
        <form action="create_post.php">
            <button class="fixed-button"><i class="fa-solid fa-plus"></i></button>
        </form>

        <!-- header logo -->
<div class="header">
  <img class="logo" src="pic/ppp.png" alt="Logo">
</div>
<div class="post <?php echo $row['is_sticky'] ? 'sticky-post' : ''; ?>" id="imp">
<h3>What's up!</h3>
<p>There are 5 levels to become a pro-writer, the novice, intermediate, advanced, superior, and distinguished.</p>
<button onclick="removeBox(this)">Remove</button>
</div>
<script>
        function removeBox(button) {
            var box = button.parentNode;
            var removalTimestamp = new Date().getTime();

            // Store the removal timestamp in session storage
            sessionStorage.setItem('boxRemovalTimestamp', removalTimestamp);

            // Remove the box from the DOM
            box.remove();
        }
        document.addEventListener('DOMContentLoaded', function() {
            var removalTimestamp = sessionStorage.getItem('boxRemovalTimestamp');

            // If removal timestamp exists, remove the box
            if (removalTimestamp) {
                var boxes = document.querySelectorAll('.box');

                for (var i = 0; i < boxes.length; i++) {
                    boxes[i].remove();
                }
            }
        });
    </script>
<hr>
        <h2>Posts</h2>

        <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
        <?php
        $post_id = $row["id"];
        ?>

        <!--posts-->

        <div class="post">
            <div class="head2">
                <div class="gege">
                 <?php if ($_SESSION["username"] === $row["author_id"]): ?>
                <span><a href="delete_post.php?post_id=<?php echo $row["id"]; ?>"><i class="fas fa-times-circle"></i></a></span>
                                    <?php endif; ?>
                </div>
             <!-- title -->
            <center><a href="post.php?id=<?php echo $post_id; ?>" id="title-text"><?php echo $row["title"]; ?></a>
                <br>

                <!-- author name -->
                <span class="<?php echo getAuthorColorClass($row["pcount"]); ?>"><span style="font-size: 11px;"><i class="fas fa-feather"></i></span> <?php echo $row["author_id"]; ?></span>

            </center>
            </div>


            <!--content -->
           <center> <?php
            $content = $row["content"];
            if (strlen($content) > 300) {
                $trimmedContent = substr($content, 0, 200);
                $remainingContent = substr($content, 200);
                echo "<center><div class='trimmed'><p id='tri'>" . $trimmedContent . "<span id='expand_" . $row["id"] . "' style='display:flex;'>" . $remainingContent . "</span><br><button id='readMore_" . $row["id"] . "' class='read-more-link' onclick='toggleContent(" . $row["id"] . ")'>Read More</button></p></div></center>";
            } else {
                echo "<p class='post-content'>" . $content . "</p>";
              }

            ?>
            </center>
<div class="action-button">
            <!-- date time -->
            <div class="date-time">
                <i class="fa-regular fa-calendar"></i>&nbsp;<span style="margin-top: 6px;"><?php echo date("D, M j", strtotime($row["created_at"])); ?></span>&nbsp;&nbsp;&nbsp;<i class="fa-regular fa-clock"></i>&nbsp;<span style="margin-top: 6px;"><?php echo date("h:iA", strtotime($row["created_at"]));?></span>
            </div>
            
            <!-- like comment -->
            <div class="like-comment">
                <form method="POST" action="like.php?post_id=<?php echo $post_id; ?>">
                    <button type="submit" class="boton" id="lakers"><span id="numb"><?php echo $row["like_count"]; ?></span>&nbsp;<i class="fa-regular fa-thumbs-up" id="thumbs"></i></button>
                </form>
                
                <button class="boton"><a href="comment.php?post_id=<?php echo $post_id; ?>" class=""><i class="fa-regular fa-comment"></i></a></button>
                
                                 <?php if ($_SESSION["username"] === $row["author_id"]): ?>
                <button class="gedit"><a href="edit_post.php?post_id=<?php echo $row["id"]; ?>"><i class="fas fa-edit"></i></a></button>
                                    <?php endif; ?>
               
           </div>

            </div>

            <!-- edit delete -->
          <!--  <div class="edit-delete"> -->
               
        </div>
        
        <?php endwhile; ?>
        <?php else : ?>
        <p>
            No posts found.
        </p>
        <?php endif; ?>

        <p>
            <a href="logout.php">LOGOUT!</a>
        </p>
    </div>

    <script>
        function toggleContent(postId) {
            var expandContent = document.getElementById("expand_" + postId);
            var readMoreLink = document.getElementById("readMore_" + postId);

            if (expandContent.style.display === "none") {
                expandContent.style.display = "flex";
                readMoreLink.innerHTML = "Read Less";
            } else {
                expandContent.style.display = "none";
                readMoreLink.innerHTML = "Read More";
            }
        }
        // Hide expanded content by default
document.addEventListener("DOMContentLoaded", function() {
    var expandedContents = document.querySelectorAll("[id^='expand_']");

    for (var i = 0; i < expandedContents.length; i++) {
        expandedContents[i].style.display = "none";
    }
});
    </script>
</body>
</html>