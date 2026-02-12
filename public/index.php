<?php
// Login page - main entry point for the application


// echo "Welcome to the Login Page! Please enter your credentials to continue.";

// App logic for handling login can be implemented here, such as validating user input, 
// checking credentials against a database, and managing user sessions.

?>

<!-- 
    Now i need to create a login form that allows users to enter their username and password.

    Application name is: Responda – A Community Emergency Alert and Response System.

    Need a simple and user-friendly login form that includes fields for username and password, as well as a submit button. 
    The form should also have a link for users who forgot their password and an option to register for new users.

    ../includes/header.php should be included at the top of the page to maintain a consistent look and feel across the application.
    ../includes/footer.php should be included at the bottom of the page to maintain a consistent look and feel across the application.

    ../assets/css/styles.css should be linked in the header to apply the necessary styling to the login form and overall page layout.

    ../assets/js/scripts.js should be linked in the footer to handle any necessary JavaScript functionality for the login form, 
        such as form validation or handling user interactions.
    
    ../assets/images/logo.png should be used as the logo for the application and should be displayed prominently on the login page.

-->
<?php include '../includes/header.php'; ?>

<div class="login-container">
    <img src="../assets/images/logo.png" alt="Responda Logo" class="logo">
    <h2>Welcome to Responda</h2>
    <p>Please enter your credentials to continue.</p>
    <form action="login_process.php" method="POST" class="login-form">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit" class="btn">Login</button>
    </form>
    <div class="additional-options">
        <a href="forgot_password.php">Forgot Password?</a>
        <span>|</span>
        <a href="register.php">Register</a>
    </div>
</div>

<?php include '../includes/footer.php'; ?>


