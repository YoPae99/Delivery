<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Something went wrong</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f7f7f7;
        }
        .error-container {
            text-align: center;
            padding: 40px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .error-title {
            font-size: 60px;
            color: #dc3545;
        }
        .error-message {
            font-size: 24px;
            color: #333;
        }
        .error-description {
            margin-top: 20px;
            font-size: 18px;
            color: #666;
        }
        .btn-back {
            margin-top: 30px;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            padding: 10px 20px;
            text-decoration: none;
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
        footer {
            position: absolute;
            bottom: 10px;
            font-size: 14px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <h1 class="error-title">Oops!</h1>
        <p class="error-message">Something went wrong.</p>
        <p class="error-description">We’re sorry for the inconvenience. Please try again later or contact support if the issue persists.</p>
        <a href="javascript:history.back()" class="btn-back">Go Back</a>
    </div>

    <footer>&copy; <?php echo date('Y'); ?> Rindra Fast Delivery</footer>
</body>
</html>
