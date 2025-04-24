<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login Selection | Easy Living</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      font-family: 'Poppins', sans-serif;
      box-sizing: border-box;
    }

    body {
      background: linear-gradient(to right, #74ebd5, #ACB6E5);
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .selection-container {
      background: white;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
      width: 400px;
      text-align: center;
      animation: fadeIn 1s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: scale(0.95);
      }
      to {
        opacity: 1;
        transform: scale(1);
      }
    }

    h2 {
      margin-bottom: 30px;
      color: #2f3542;
    }

    .select-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(to right, #74ebd5, #ACB6E5);
      color: white;
      border: none;
      padding: 12px 20px;
      border-radius: 30px;
      font-size: 16px;
      margin: 15px 0;
      cursor: pointer;
      width: 100%;
      transition: 0.3s ease;
    }

    .select-btn:hover {
      background: linear-gradient(to right, #ACB6E5, #74ebd5);
      transform: scale(1.03);
    }

    .select-btn i {
      margin-right: 10px;
    }
  </style>
</head>
<body>

  <div class="selection-container">
    <h2>Choose Login Option</h2>
    
    <button class="select-btn" onclick="location.href='admin/login.php'">
      <i class="fas fa-user-shield"></i> Login as Admin
    </button>
    
    <button class="select-btn" onclick="location.href='customer_login.php'">
      <i class="fas fa-user"></i> Login as Customer
    </button>
    
    <button class="select-btn" onclick="location.href='customer_signup.php'">
      <i class="fas fa-user-plus"></i> Sign Up as Customer
    </button>
  </div>

</body>
</html>
