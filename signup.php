<?php
// Include necessary files
require_once __DIR__ . '../Classes/User.php';

session_start(); // Start the session

// Use correct namespaces
use DELIVERY\User\User;


// Generate CSRF token if it doesn't exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Initialize error and success message containers
$errorMessages = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if the CSRF token is set and valid
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("Invalid CSRF token.");
    }

    // Retrieve form data
    $name = $_POST['name'];
    $permission = isset($_POST['permission']) ? $_POST['permission'] : ''; // Check if permission is set
    $age = $_POST['age'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmpassword'];

    // Basic validation
    $errors = [];

    // Name validation
    if (!preg_match("/^[A-Z][a-zA-Z\s]+$/", $name)) {
        $errors[] = "Name must start with a capital letter and cannot include numbers or punctuation.";
    }

    // Age validation
    if (!filter_var($age, FILTER_VALIDATE_INT) || $age < 18) {
        $errors[] = "Age must be a valid integer greater than or equal to 18.";
    }

    // Username validation
    if (!preg_match("/^(?=.*[a-zA-Z])(?=.*[0-9]).{5,}$/", $username)) {
        $errors[] = "Username must be at least 5 characters long and include both letters and numbers.";
    }

    // Permission validation
    if (empty($permission)) {
        $errors[] = "Please select a role.";
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    // Password validation
    if (!preg_match("/^(?=.*[A-Z])(?=.{8,})/", $password)) {
        $errors[] = "Password must be at least 8 characters long and include at least one capital letter.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match!";
    }

    // If there are errors, create a Bootstrap alert container
    if (!empty($errors)) {
        $errorMessages .= '<div class="alert alert-danger" role="alert">';
        foreach ($errors as $error) {
            $errorMessages .= "<p>$error</p>";
        }
        $errorMessages .= '</div>';
    } else {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Try to create the user and handle duplicate errors
        try {
            User::CreateUser($name, $permission, $age, $username, $email, $hashedPassword);
            // Set the success message if the user is created successfully
            $successMessage = '<div class="alert alert-success" role="alert">User created successfully!</div>';
        } catch (PDOException $e) {
            // Check for duplicate entry error
            if ($e->getCode() == 23000) { 
                $errorMessages .= '<div class="alert alert-danger" role="alert">Error: Username or Email already exists. Please choose another.</div>';
            } else {
                $errorMessages .= '<div class="alert alert-danger" role="alert">An unexpected error occurred: ' . $e->getMessage() . '</div>';
            }
        }
    }
}
?>

<!doctype html>
<html lang="en" data-bs-theme="auto">
<head>
    <title>Sign up</title>
    <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/sign-in/">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <style>
        /* Center the form container and limit its width */
        .form-container {
            max-width: 400px; /* Set a maximum width */
            margin: auto; /* Center horizontally */
            padding: 20px; /* Add some padding */
            border: solid 1px;
            border-radius: 1rem;
            box-shadow: 0 0 0 10px rgba(0, 0, 0, 0.05);
        }

        /* Styling for the error message */
        .error-message {
            color: red;
            font-size: 0.9rem;
        }

        /* Ensure form-floating elements have no extra spacing */
        .form-floating {
            margin-bottom: 10px; /* Ensure small gaps between fields */
        }
    </style>

    <link href="css/signin.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center py-4 bg-body-tertiary">

<main class="form-container">
    <form id="form" action="/process_signup.php" method="post" enctype="multipart/form-data">
        <h1 class="h3 mb-3 fw-normal text-center">Sign up</h1>

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

        <!-- Select Role -->
        <div class="form-floating">
            <select id="floatingInput" class="form-select" name="permission" aria-label="Default select example" required>
                <option selected>Select role</option>
                <option value="driver">Driver</option>
                <option value="client">Client</option>
            </select>
            <label for="floatingInput">Select role</label>
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

        <!-- Submit Button -->
        <button class="btn btn-primary w-100 py-2" type="submit">Submit</button>
        <br><br>
        <p class="text-body-secondary" style="font-size:15.6px">Already have an account? <a href="login.php">Login now</a>.</p>
    </form>
</main>
</body>
</html>

