
<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head><script src="/docs/5.3/assets/js/color-modes.js"></script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.122.0">
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
  #form{
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
</style>

    <!-- Custom styles for this template -->
    <link href="css/signin.css" rel="stylesheet">
  </head>
  <body class="d-flex align-items-center py-4 bg-body-tertiary">

<main class="form-signin w-100 m-auto">
<div class="form-container">
  <form id="form" action="signup.php" method="post" enctype="multipart/form-data">
    
    <h1 class="h3 mb-3 fw-normal">Sign up</h1>

<!-- Name -->
    <div class="form-floating">
      <input type="text" class="form-control" id="floatingInput" placeholder="Name" name="name" required>
      <label for="floatingInput">Name</label>
    </div>
  
<!-- Age -->
    <div class="form-floating">
      <input type="number" min="1" class="form-control" id="floatingInput" placeholder="Age" name="age" required>
      <label for="floatingInput">Age</label>
    </div>

<!-- Username -->
    <div class="form-floating">
      <input type="text" class="form-control" id="floatingInput" placeholder="Username" name="username" required>
      <label for="floatingInput">Username</label>
    </div>

<!-- Email -->
    <div class="form-floating">
    <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="email" required>
    <label for="floatingInput">Email address</label>
    </div>

<!-- Password -->
    <div class="form-floating">
      <input type="password" class="form-control" id="floatingInput" placeholder="Create new password" name="password" required>
      <label for="floatingInput">Create new password</label>
    </div>

<!-- Confirm Password -->
    <div class="form-floating">
      <input type="password" class="form-control" id="floatingInput" placeholder="Confirm your password" name="confirmpassword" required>
      <label for="floatingInput">Confirm your password</label>
    </div>

    <div class="form-check text-start my-3">
      <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
      <label class="form-check-label" for="flexCheckDefault" requried>
         Agree Terms and Conditions
      </label>
    </div>

    <!-- button -->
    <div>
    <button class="btn btn-primary" style="padding-left: 104px; padding-right: 104px; text-align: center;" type="submit" value="submit" name="submit">Submit</button>
    <!-- <button class="btn btn-secondary" style="padding-left: 85px; padding-right: 85px" type="reset" name="reset">Reset</button> -->
    </div>
  </form>
  </div>
</main>
    </body>
</html>

