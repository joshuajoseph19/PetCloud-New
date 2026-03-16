<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f3f4f6;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1f2937;
        }

        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }

        .error-code {
            font-size: 8rem;
            font-weight: 700;
            color: #ef4444;
            /* Red color for error */
            line-height: 1;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 0px rgba(0, 0, 0, 0.1);
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #111827;
        }

        .error-message {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .btn {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.75rem;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.2s, background-color 0.2s;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.5);
        }

        .btn:hover {
            background-color: #2563eb;
            transform: translateY(-2px);
        }

        .icon-box {
            font-size: 4rem;
            color: #ef4444;
            margin-bottom: 1rem;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0px);
            }
        }
    </style>
</head>

<body>
    <div class="error-container">
        <div class="icon-box">
            <i class="fa-solid fa-ban"></i>
        </div>
        <h1 class="error-code">403</h1>
        <h2 class="error-title">Access Restricted</h2>
        <p class="error-message">
            Stop right there! You don't have permission to view this page,
            or the URL you entered violates our security policies.
        </p>
        <a href="index.php" class="btn">
            <i class="fa-solid fa-house"></i> Return Home
        </a>
    </div>
</body>

</html>