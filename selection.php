<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Easy Living - Welcome</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            height: 100%;
            font-family: 'Poppins', sans-serif;
            overflow-x: hidden;
        }

        .hero {
            background: url('image/background-scaled.jpg') no-repeat center center/cover;
            height: 100vh;
            position: relative;
        }

        .overlay {
            background: rgba(0, 0, 0, 0.6);
            width: 100%;
            height: 100%;
            position: absolute;
            top: 0; left: 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 30px 60px;
        }

        .logo {
            font-size: 30px;
            font-weight: bold;
            color: #fff;
        }

        nav a {
            color: #fff;
            margin-left: 30px;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }

        nav a:hover {
            color: #ffb366;
        }

        .content {
            text-align: center;
            margin-top: auto;
            padding-bottom: 150px;
            color: #fff;
        }

        .content h1 {
            font-size: 60px;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .content p {
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .action {
            position: relative;
            margin-top: 40px;
            display: inline-block;
        }

        .action button {
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            color: white;
            border: none;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .action button:hover {
            background: linear-gradient(to right, #feb47b, #ff7e5f);
        }

        .dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border-radius: 10px;
            min-width: 200px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            z-index: 999;
        }

        .dropdown a {
            color: #333;
            padding: 12px 20px;
            text-decoration: none;
            display: block;
            transition: background 0.3s;
        }

        .dropdown a:hover {
            background:rgb(243, 202, 172);
        }

        .action:hover .dropdown {
            display: block;
        }

        footer {
            text-align: center;
            color: #ddd;
            padding: 20px;
            font-size: 14px;
        }

        @media (max-width: 768px) {
            header {
                flex-direction: column;
                text-align: center;
            }

            nav {
                margin-top: 15px;
            }

            .content h1 {
                font-size: 38px;
            }
        }
    </style>
</head>
<body>
    <div class="hero">
        <div class="overlay">
            <header>
                <div class="logo">Easy Living</div>
                <nav>
                    <a href="#">Home</a>
                    <a href="#">Services</a>
                    <a href="#">About</a>
                    <a href="#">Contact</a>
                </nav>
            </header>

            <div class="content">
                <h1>Welcome to Easy Living</h1>
                <p>Your trusted platform for reliable home services. Fast, professional, and stress-free solutions at your fingertips.</p>

                <div class="action">
                    <button><i class="fas fa-sign-in-alt"></i> Login / Sign Up</button>
                    <div class="dropdown">
                        <a href="admin_login.php">Login as Admin</a>
                        <a href="customer_login.php">Login as Customer</a>
                    </div>
                </div>
            </div>

            <footer>
                &copy; <?php echo date("Y"); ?> Easy Living. All rights reserved.
            </footer>
        </div>
    </div>
</body>
</html>
