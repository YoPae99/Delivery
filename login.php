<!doctype html>
<html lang="en" data-bs-theme="auto">
  <head><script src="/docs/5.3/assets/js/color-modes.js"></script>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.122.0">
    <title>Login</title>

    <!-- <link rel="canonical" href="https://getbootstrap.com/docs/5.3/examples/sign-in/">

    

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@docsearch/css@3"> -->

<link href="css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">


    <style>
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
<form action="process_login.php" method="post" id="form">    
    <h1 style="text-align: center;" class="h3 mb-3 fw-normal">Login</h1>

    <div class="form-floating">
      <input name="email" type="email" class="form-control" id="floatingInput" placeholder="name@example.com">
      <label for="floatingInput">Email address</label>
    </div>
    <div class="form-floating">
      <input name="password" type="password" class="form-control" id="floatingPassword" placeholder="Password">
      <label for="floatingPassword">Password</label>
    </div>

   <!--Select Role to log in  -->
    <!-- <select class="form-select" aria-label="Default select example" name="permission">
  <option selected>Select role</option>
  <option value="admin">Admin</option>
  <option value="driver">Driver</option>
  <option value="client">Client</option>
  </select>
<br> -->
    <!-- <div class="form-check text-start my-3">
      <input class="form-check-input" type="checkbox" value="remember-me" id="flexCheckDefault">
      <label class="form-check-label" for="flexCheckDefault">
        Remember me
      </label>
    </div> -->
    <button class="btn btn-primary w-100 py-2" type="submit">Login</button>
    <p class="mt-5 mb-3 text-body-secondary" style="font-size:15.7px">Don't have an account? <a href="signup.php">Sign up now</a>.</p> 
  </form>
</main>
    </body>
</html> 