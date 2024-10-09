<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>

    <title>Sign up</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/sign-in/">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        /* Target only the Confirm Password field to remove margin above */
        #confirmPasswordInput {
            margin-top: -1px; /* Adjust as needed to remove the gap */
        }

        /* Ensure form-floating elements have no extra spacing */
        .form-floating {
            margin-bottom: 0px; /* Ensure no gap between all form fields */
            padding-bottom: 0px;
        }

        /* Add a small space between all fields except the password and confirm password fields */
        .form-floating:not(:last-child) {
            margin-bottom: 10px; /* Adjust as needed */
        }

        #form {
            border: solid 1px;
            border-width: 1px;
            border-radius: 1rem;
            box-shadow: 0 0 0 10px rgba(0, 0, 0, 0.05); /* Adds a "border-like" shadow */
            padding: 20px; /* Adds space between border and form fields */
            box-sizing: border-box; /* Ensures padding doesn't affect the form's size */
        }

        .form-container {
            position: relative;
        }

        /* Error message styling */
        .error-message {
            color: red;
            font-size: 0.9rem;
        }
    </style>

    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center py-4 bg-body-tertiary">

<main class="form-signin w-100 m-auto">
    <form id="form" action="/process_signup.php" method="post" enctype="multipart/form-data">
        <h1 style="text-align: center;" class="h3 mb-3 fw-normal">Sign up</h1>

        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?php
        session_start(); // Start the session
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generate a random token
        }
        echo $_SESSION['csrf_token']; // Output the token
        ?>">
        
        <!-- Name -->
        <div class="form-floating">
            <input type="text" class="form-control" id="floatingInput" placeholder="Name" name="name" required>
            <label for="floatingInput">Name</label>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
                if (!preg_match("/^[a-zA-Z\s]+$/", $_POST['name'])) {
                    echo '<div class="error-message">Please enter a valid name (letters only).</div>';
                }
            }
            ?>
        </div>

        <!-- Select Role to log in -->
        <div class="form-floating">
            <select id="floatingInput" class="form-select" name="permission" aria-label="Default select example" required>
                <option selected>Select role</option>
                <option value="driver">Driver</option>
                <option value="client">Client</option>
            </select>
        </div>

        <!-- Age -->
        <div class="form-floating">
            <input type="number" min="18" class="form-control" id="ageInput" placeholder="Age" name="age" required>
            <label for="ageInput">Age</label>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['age'])) {
                if (!is_numeric($_POST['age']) || $_POST['age'] < 1) {
                    echo '<div class="error-message">Please enter a valid age.</div>';
                }
            }
            ?>
        </div>

        <!-- Username -->
        <div class="form-floating">
            <input type="text" class="form-control" id="usernameInput" placeholder="Username" name="username" required>
            <label for="usernameInput">Username</label>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'])) {
                if (strlen($_POST['username']) < 5 || ctype_digit($_POST['username'])) {
                    echo '<div class="error-message">Username must be at least 5 characters and cannot be numbers only.</div>';
                }
            }
            ?>
        </div>

        <!-- Email -->
        <div class="form-floating">
            <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="email" required>
            <label for="floatingInput">Email address</label>
        </div>

        <!-- Password -->
        <div class="form-floating">
            <input type="password" class="form-control" id="passwordInput" placeholder="Create new password" name="password" required>
            <label for="passwordInput">Create new password</label>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'])) {
                if (strlen($_POST['password']) < 8 || !preg_match("/[A-Z]/", $_POST['password'])) {
                    echo '<div class="error-message">Password must be at least 8 characters long and include at least one capital letter.</div>';
                }
            }
            ?>
        </div>

        <!-- Confirm Password -->
        <div class="form-floating">
            <input type="password" class="form-control" id="confirmPasswordInput" placeholder="Confirm your password" name="confirmpassword" required>
            <label for="confirmPasswordInput">Confirm your password</label>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirmpassword'])) {
                if ($_POST['password'] !== $_POST['confirmpassword']) {
                    echo '<div class="error-message">Passwords do not match.</div>';
                }
            }
            ?>
        </div>

        <!-- button -->
        <button class="btn btn-primary w-100 py-2" type="submit">Submit</button>
        <br><br>
        <p class="text-body-secondary" style="font-size:15.6px">Already have an account? <a href="login.php">Login now</a>.</p>

    </form>
</main>
</body>
</html>
