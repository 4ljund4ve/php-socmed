<?php
require_once('db.php');

// Check if user is already logged in
session_start();
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: dashboard.php");
    exit();
}

// Initialize an array to store error messages
$errors = array();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    // Validate username and password
    if (empty($username)) {
        $errors[] = "Please enter your username.";
    }

    if (empty($password)) {
        $errors[] = "Please enter your password.";
    }

    // If there are no errors, proceed with login
    if (empty($errors)) {
        // Check if the username exists in the database
        $checkUsernameQuery = "SELECT * FROM users WHERE username='$username'";
        $checkUsernameResult = $conn->query($checkUsernameQuery);

        if ($checkUsernameResult->num_rows > 0) {
            $row = $checkUsernameResult->fetch_assoc();
            $hashedPassword = $row["password"];

            // Verify the password
            if (password_verify($password, $hashedPassword)) {
                // Start a new session and set the loggedin variable
                session_start();
                $_SESSION["loggedin"] = true;
                $_SESSION["username"] = $username;

                // Set remember me cookie if selected
                if ($remember) {
                    $cookieExpiration = time() + (30 * 24 * 60 * 60); // 30 days
                    setcookie("remember_user", $username, $cookieExpiration);
                    setcookie("remember_pass", $password, $cookieExpiration);
                } else {
                    // Delete the remember me cookie if not selected
                    setcookie("remember_user", "", time() - 3600);
                    setcookie("remember_pass", "", time() - 3600);
                }

                // Redirect to the dashboard upon successful login
                header("Location: dashboard.php");
                exit();
            } else {

                $errors[] = "Invalid password.";
            }
        } else {
           $errors[] = "Invalid username.";
        }
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="css/login.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
    <link href='https://fonts.googleapis.com/css?family=Varela Round' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <header>
            <img src="pic/ppp.png">
        </header>
        <center><p style="color: gray; position: relative; margin: 0; margin-top: 10px; font-size: 14px;">
            Login to continue...
        </p>
        </center>
        <?php if (!empty($errors)): ?>

        <?php endif; ?>


        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <br>
            <input type="text" name="username" class="input_field" required placeholder="Username" autocomplete="off"><br>

            <label for="password" id="label"></label><br>
            <input type="password" name="password" class="input_field" required placeholder="Password" autocomplete="off"><br><br>
            <input type="submit" value="ENTER" id="btn">

        </form>

        <form action="signup.php">
            <input type="submit" value="REGISTER" id="reg">
        </form>

        <div class="error" id="err">
            <center>
                <?php foreach ($errors as $error): ?>
                <p>
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </p>
                <?php endforeach; ?></center>
        </div> 
    <script>
        setTimeout(function() {
            var element = document.getElementById("err");
            element.style.opacity = "0";
        }, 5000);

    </script>
</body>
</html>