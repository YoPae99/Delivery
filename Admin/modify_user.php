<?php
session_start();
require_once __DIR__ . '/../Classes/Admin.php';
use DELIVERY\Admin\Admin;
use DELIVERY\Database\Database;

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['UserId'])) {
    $userId = $_POST['UserId'];

    // Fetch the user's current details
    $db = new Database();
    $conn = $db->getStarted();
    if ($conn) {
        $stmt = $conn->prepare("SELECT Name, Age, Username FROM user WHERE ID = :userId");
        $stmt->bindParam(':userId', $userId);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Check if the form is submitted to update the user details
    if (isset($_POST['update'])) {
        // Trim inputs
        $name = trim($_POST['Name']);
        $age = trim($_POST['Age']);
        $username = trim($_POST['Username']);

        // Server-side validation
        if (!preg_match('/^[A-Z][a-zA-Z\s]*$/', $name)) {
            $errors[] = "Name must start with a capital letter and contain only letters and spaces.";
        }
        if (!is_numeric($age) || $age < 18) {
            $errors[] = "Age must be a number greater than 18.";
        }
        if (!preg_match('/[a-zA-Z]/', $username) || is_numeric($username)) {
            $errors[] = "Username must contain letters and cannot be only numbers.";
        }

        // If no errors, proceed with the update
        if (empty($errors)) {
            $updateStmt = $conn->prepare("UPDATE user SET Name = :name, Age = :age, Username = :username WHERE ID = :userId");
            $updateStmt->bindParam(':name', $name);
            $updateStmt->bindParam(':age', $age);
            $updateStmt->bindParam(':username', $username);
            $updateStmt->bindParam(':userId', $userId);
            if ($updateStmt->execute()) {
                $successMessage = "User updated successfully!";
            } else {
                $errors[] = "Failed to update user.";
            }
        }
    }


} else {
    header('Location: display_alluser.php'); // Redirect if no user ID is provided
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <title>Modify User</title>
    <script>
        function validateForm() {
    let name = document.forms["updateForm"]["Name"].value.trim(); // Trim spaces
    let age = document.forms["updateForm"]["Age"].value;
    let username = document.forms["updateForm"]["Username"].value;
    let errors = [];

    // Name validation
    let nameRegex = /^[A-Z][a-zA-Z\s]*$/;
    if (!nameRegex.test(name)) {
        errors.push("Name must start with a capital letter and contain only letters and spaces.");
    }

    // Age validation
    if (age < 18 || isNaN(age)) {
        errors.push("Age must be a number greater than 18.");
    }

    // Username validation
    let usernameRegex = /[a-zA-Z]/;
    if (!usernameRegex.test(username) || !isNaN(username)) {
        errors.push("Username must contain letters and cannot be only numbers.");
    }

    // Display errors or submit the form
    if (errors.length > 0) {
        alert(errors.join("\n"));
        return false;
    }
    return true;
}

    </script>
</head>
<body>
    <div class="container mt-5">
        <h2>Modify User</h2>

        <?php if (!empty($errors)): ?>
    <pre><?php print_r($errors); ?></pre>  <!-- This will show the entire errors array -->
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <p><?php echo $error; ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if ($successMessage): ?>
    <div class="alert alert-success">
        <p><?php echo $successMessage; ?></p>
    </div>
<?php endif; ?>


        <form name="updateForm" method="post" action="" onsubmit="return validateForm()">
            <input type="hidden" name="UserId" value="<?php echo htmlspecialchars($userId); ?>">
            <div class="mb-3">
                <label for="Name" class="form-label">Name</label>
                <input type="text" class="form-control" name="Name" value="<?php echo htmlspecialchars($user['Name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="Age" class="form-label">Age</label>
                <input type="number" class="form-control" name="Age" value="<?php echo htmlspecialchars($user['Age']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="Username" class="form-label">Username</label>
                <input type="text" class="form-control" name="Username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
            </div>
            <button type="submit" name="update" class="btn btn-primary">Update</button>
        </form>
        <form action="display_allusers.php" method="get" >
            <button type="submit" class="btn btn-secondary mt-3">Go Back</button>
        </form>
    </div>
</body>
</html>
