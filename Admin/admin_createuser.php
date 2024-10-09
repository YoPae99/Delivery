<?php
// Include necessary files
require_once __DIR__ . '/../Classes/User.php';
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
            // Optionally, redirect to another page or reset the form
            // header("Location: login.php");
            // exit();
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
    <title>Create User</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3">
    <link href="/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .form-container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 0.5rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
            margin-bottom: 1.5rem;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: background-color 0.3s;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <main style="margin-top: 20px;" class="form-container">
        <form id="form" action="" method="post" enctype="multipart/form-data">
            <h1 class="h3 mb-3 fw-normal">Create User</h1>

            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <!-- Display error messages -->
            <?php if (!empty($errorMessages)) echo $errorMessages; ?>

            <!-- Display success message -->
            <?php if (!empty($successMessage)) echo $successMessage; ?>

            <!-- Name -->
            <div class="form-floating">
                <input type="text" class="form-control" id="floatingInput" placeholder="Name" name="name" required>
                <label for="floatingInput">Name</label>
            </div>

            <!-- Select Role -->
            <div class="form-floating">
                <select id="floatingInput" class="form-select" name="permission" aria-label="Default select example" required>
                    <option selected disabled>Select role</option>
                    <option value="driver">Driver</option>
                    <option value="client">Client</option>
                </select>
                <label for="floatingInput">Role</label>
            </div>

            <!-- Age -->
            <div class="form-floating">
                <input type="number" min="18" class="form-control" id="ageInput" placeholder="Age" name="age" required>
                <label for="ageInput">Age</label>
            </div>

            <!-- Username -->
            <div class="form-floating">
                <input type="text" class="form-control" id="usernameInput" placeholder="Username" name="username" required>
                <label for="usernameInput">Username</label>
            </div>

            <!-- Email -->
            <div class="form-floating">
                <input type="email" class="form-control" id="emailInput" placeholder="name@example.com" name="email" required>
                <label for="emailInput">Email address</label>
            </div>

            <!-- Password -->
            <div class="form-floating">
                <input type="password" class="form-control" id="passwordInput" placeholder="Create new password" name="password" required>
                <label for="passwordInput">Create new password</label>
            </div>

            <!-- Confirm Password -->
            <div class="form-floating">
                <input type="password" class="form-control" id="confirmPasswordInput" placeholder="Confirm your password" name="confirmpassword" required>
                <label for="confirmPasswordInput">Confirm your password</label>
            </div>

            <!-- Submit Button -->
            <button class="btn btn-primary w-100 py-2" type="submit">Create</button>

            <!-- Go Back Button -->
            <a href="display_allusers.php" class="btn btn-secondary w-100 py-2 mt-2">Go Back</a>
        </form>
    </main>
</body>
</html>
