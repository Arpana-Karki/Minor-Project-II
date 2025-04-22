<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Easy Living - Welcome</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;700&display=swap" rel="stylesheet"/>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body, html {
      height: 100%;
      font-family: 'Poppins', sans-serif;
      background: #0f0f1a;
      overflow-x: hidden;
    }

    .hero {
      background: url('image/background-scaled.jpg') no-repeat center center/cover;
      height: 100vh;
      position: relative;
    }

    .overlay {
      background: linear-gradient(135deg, rgba(0,0,0,0.6), rgba(20,30,48,0.6));
      width: 100%;
      height: 100%;
      position: absolute;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      z-index: 2;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 30px 60px;
      animation: slideInDown 1s ease-out;
    }

    .logo {
      font-size: 32px;
      font-weight: 700;
      background: linear-gradient(45deg, #ff6a88, #ff9a8b);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    nav {
      display: flex;
      align-items: center;
    }

    nav a {
      color: #fff;
      margin-left: 30px;
      text-decoration: none;
      font-weight: 500;
      font-size: 16px;
      position: relative;
      transition: color 0.3s ease;
    }

    nav a::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: -5px;
      left: 0;
      background: #ff9a8b;
      transition: width 0.3s ease;
    }

    nav a:hover::after {
      width: 100%;
    }

    nav a:hover {
      color: #ff9a8b;
    }

    .content {
      text-align: center;
      margin: auto;
      color: #fff;
      padding: 0 20px;
      animation: fadeInUp 1.2s ease-out;
    }

    .content h1 {
      font-size: 64px;
      font-weight: 700;
      margin-bottom: 20px;
      background: linear-gradient(45deg, #ff6a88, #ff9a8b);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    .content p {
      font-size: 18px;
      max-width: 700px;
      margin: 0 auto;
      color: #e0e0e0;
      line-height: 1.8;
    }

    .action {
      margin-top: 40px;
      position: relative;
      display: inline-block;
    }

    .action button {
      background: linear-gradient(45deg, #ff6a88, #ff9a8b);
      color: white;
      border: none;
      padding: 14px 35px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 50px;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 15px rgba(255, 106, 136, 0.4);
    }

    .action button:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 30px rgba(255, 106, 136, 0.5);
    }

    .dropdown {
      display: none;
      position: absolute;
      top: 110%;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(15px);
      border-radius: 10px;
      min-width: 220px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
      border: 1px solid rgba(255, 255, 255, 0.15);
      z-index: 100;
    }

    .dropdown a {
      display: block;
      padding: 12px 20px;
      color: #fff;
      font-size: 14px;
      text-decoration: none;
      transition: background 0.3s;
    }

    .dropdown a:hover {
      background: linear-gradient(45deg, #ff6a88, #ff9a8b);
    }

    footer {
      text-align: center;
      color: #ccc;
      padding: 20px;
      font-size: 14px;
      background: rgba(0, 0, 0, 0.3);
    }

    canvas#particles {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: 1;
    }

    @keyframes slideInDown {
      from { transform: translateY(-80px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    @keyframes fadeInUp {
      from { transform: translateY(40px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    @media (max-width: 768px) {
      header { flex-direction: column; padding: 20px; }
      nav { margin-top: 15px; }
      nav a { margin: 0 10px; font-size: 14px; }
      .content h1 { font-size: 40px; }
      .content p { font-size: 16px; }
      .action button { padding: 12px 30px; font-size: 14px; }
    }
  </style>
</head>
<body>
<div class="hero">
  <canvas id="particles"></canvas>
  <div class="overlay">
    <header>
      <div class="logo">Easy Living</div>
      <nav>
        <a href="index.php">Home</a>
        <a href="services.php">Services</a>
        <a href="about.php">About</a>
        <a href="contact.php">Contact</a>
      </nav>
    </header>

    <div class="content">
      <h1>Welcome to Easy Living</h1>
      <p>Your trusted platform for reliable home services. Fast, professional, and stress-free solutions at your fingertips.</p>
      <div class="action">
        <button id="loginBtn"><i class="fas fa-sign-in-alt"></i> Login / Sign Up</button>
        <div class="dropdown" id="loginDropdown">
          <a href="admin/login.php">Login as Admin</a>
          <a href="customer_login.php">Login as Customer</a>
        </div>
      </div>
    </div>

    <footer>
      ©️ <?php echo date("Y"); ?> Easy Living. All rights reserved.
    </footer>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const canvas = document.getElementById('particles');
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    let particlesArray = [];
    const numberOfParticles = 60;

    class Particle {
      constructor() {
        this.x = Math.random() * canvas.width;
        this.y = Math.random() * canvas.height;
        this.size = Math.random() * 3 + 1;
        this.speedX = Math.random() * 1.5 - 0.75;
        this.speedY = Math.random() * 1.5 - 0.75;
      }

      update() {
        this.x += this.speedX;
        this.y += this.speedY;
        if (this.size > 0.2) this.size -= 0.02;
        if (this.x < 0 || this.x > canvas.width) this.speedX *= -1;
        if (this.y < 0 || this.y > canvas.height) this.speedY *= -1;
      }

      draw() {
        ctx.fillStyle = 'rgba(255, 106, 136, 0.7)';
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
        ctx.fill();
      }
    }

    function initParticles() {
      particlesArray = [];
      for (let i = 0; i < numberOfParticles; i++) {
        particlesArray.push(new Particle());
      }
    }

    function animateParticles() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      for (let i = 0; i < particlesArray.length; i++) {
        particlesArray[i].update();
        particlesArray[i].draw();
      }
      requestAnimationFrame(animateParticles);
    }

    initParticles();
    animateParticles();

    window.addEventListener('resize', () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
      initParticles();
    });

    const loginBtn = document.getElementById('loginBtn');
    const loginDropdown = document.getElementById('loginDropdown');

    loginBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      loginDropdown.style.display = loginDropdown.style.display === 'block' ? 'none' : 'block';
    });

    document.addEventListener('click', (e) => {
      if (!loginDropdown.contains(e.target) && e.target !== loginBtn) {
        loginDropdown.style.display = 'none';
      }
    });
  });
</script>
</body>
</html>
