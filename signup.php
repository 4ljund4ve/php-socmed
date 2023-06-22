<?php
require_once('db.php');

// Check if user is already logged in
session_start();
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("Location: dashboard.html");
    exit();
}

// Initialize an array to store error messages
$errors = array();

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];
    
    // Username validation
    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $username)) {
        $errors[] = "Username can only contain alphanumeric characters, underscores, and dashes.";
    }
    
    // Password validation
    if (strlen($password) < 8) {
        $errors[] = "Password must have at least 8 characters.";
    }
    
    // Confirm password validation
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }
    
    // Check if username is already taken
    $checkUsernameQuery = "SELECT * FROM users WHERE username='$username'";
    $checkUsernameResult = $conn->query($checkUsernameQuery);
    if ($checkUsernameResult->num_rows > 0) {
        $errors[] = "Username is already taken.";
    }
    
    // Check if email is already registered
    $checkEmailQuery = "SELECT * FROM users WHERE email='$email'";
    $checkEmailResult = $conn->query($checkEmailQuery);
    if ($checkEmailResult->num_rows > 0) {
        $errors[] = "Email is already registered.";
    }
    
    // If there are no errors, proceed with signup
    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user data into the database
        $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashedPassword')";
        
        if ($conn->query($sql) === TRUE) {
            // Redirect to login page upon successful signup
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Error: " . $sql . "<br>" . $conn->error;
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
      <center><p style="color: gray; position: relative; margin: 0; margin-top: 10px; font-size: 14px;">Sign up...</p></center><br>
    <?php if (!empty($errors)): ?>
      
      
      
    <?php endif; ?>
    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input class="input_field" type="text" name="username" required placeholder="Username" autocomplete="off"><br><br>
        
        <input class="input_field" type="email" name="email" required placeholder="Email Address" autocomplete="off"><br><br>
        
        <input class="input_field" type="password" name="password" required placeholder="Password"><br><br>
        
      
        <input class="input_field" type="password" name="confirm_password" required placeholder="Confirm Password" autocomplete="off"><br><br>
        
        <input type="submit" value="REGISTER" id="btn">
    </form>
    <form action="login.php">
  <input type="submit" value="LOGIN" id="reg">
</form>

<div class="error" id="err">
            <center>
<?php foreach ($errors as $error): ?>
                <p><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
            <?php endforeach; ?></center>
        </div>
        
    </div>
    <script>
        setTimeout(function() {
  var element = document.getElementById("err");
  element.style.opacity = "0";
}, 5000);

    </script>
    </div>
    </div>
</body>
</html>
