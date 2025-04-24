<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Our Services - Easy Living</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap"
      rel="stylesheet"
    />
    <style>
      /* Custom Glassmorphism Navbar */
      .navbar {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        position: sticky;
        top: 0;
        z-index: 1000;
      }

      .nav-link {
        position: relative;
        transition: color 0.3s ease;
      }

      .nav-link::after {
        content: "";
        position: absolute;
        width: 0;
        height: 2px;
        bottom: -2px;
        left: 0;
        background-color: #ff6600;
        transition: width 0.3s ease;
      }

      .nav-link:hover::after {
        width: 100%;
      }

      .nav-link:hover {
        color: #ff6600;
      }

      /* Gradient Background */
      body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        font-family: "Inter", sans-serif;
      }

      /* Custom Animations */
      @keyframes flipIn {
        0% {
          transform: rotateY(0deg);
          opacity: 1;
        }
        50% {
          transform: rotateY(90deg);
          opacity: 0.7;
        }
        100% {
          transform: rotateY(0deg);
          opacity: 1;
        }
      }

      .flip-animation:hover {
        animation: flipIn 0.6s ease-in-out;
        transform: scale(1.05);
        box-shadow: 0 8px 20px rgba(255, 102, 0, 0.3);
      }

      .search-animation {
        transform: scale(1.05);
        box-shadow: 0 4px 20px rgba(255, 102, 0, 0.5);
      }

      .promo-card {
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
      }

      .promo-card:hover {
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
      }

      /* Smooth Scroll */
      html {
        scroll-behavior: smooth;
      }

      /* Glowing Search Bar */
      .search-box:focus {
        box-shadow: 0 0 15px rgba(255, 102, 0, 0.6);
        border-color: #ff6600;
      }

      /* Parallax Tilt Effect */
      .tilt {
        transform-style: preserve-3d;
        transition: transform 0.3s ease;
      }
    </style>
  </head>
  <body class="min-h-screen text-center">
    <!-- Navbar -->
    <nav class="navbar p-4 flex justify-between items-center h-16 text-white">
      <div class="flex space-x-8">
        <a href="index.php" class="nav-link font-semibold text-lg">Home</a>
        <a href="about.html" class="nav-link font-semibold text-lg">About</a>
        <a href="services.html" class="nav-link font-semibold text-lg">Services</a>
        <a href="wishlist.html" class="nav-link font-semibold text-lg">Wishlist</a>
        <a href="cart.html" class="nav-link font-semibold text-lg">Add to Cart</a>
      </div>
    </nav>

    <!-- Search Bar -->
    <div class="flex justify-center items-center my-10 relative">
      <input
        type="text"
        id="searchInput"
        class="search-box w-4/5 max-w-lg p-4 border-2 border-orange-500 rounded-full text-lg transition-all duration-300 shadow-lg focus:outline-none focus:w-11/12 focus:shadow-xl bg-white/80"
        placeholder="Search for services..."
      />
      <button
        class="absolute right-1/10 p-2 bg-orange-500 text-white rounded-full text-lg cursor-pointer hover:bg-orange-600 hover:scale-110 transition-all duration-300"
        onclick="searchServices()"
      >
        🔍
      </button>
    </div>

    <!-- Packages & Offers Section -->
    <div class="flex justify-center gap-10 my-16">
      <div
        class="promo-card tilt relative w-96 h-72 rounded-2xl overflow-hidden shadow-2xl cursor-pointer"
        onclick="window.location.href='packages.html'"
      >
        <img
          src="./image/package.jpeg"
          alt="Packages"
          class="w-full h-full object-cover transition-opacity duration-300 hover:opacity-60"
        />
        <div
          class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white"
        >
          <h3 class="text-3xl font-bold mb-3">Exclusive Packages</h3>
          <p class="text-base mb-5">Get the best service bundles at great prices!</p>
          <button
            class="bg-orange-500 text-white border-none px-5 py-2 font-semibold rounded-lg hover:bg-orange-600 transition-colors"
          >
            View Packages
          </button>
        </div>
      </div>
      <div
        class="promo-card tilt relative w-96 h-72 rounded-2xl overflow-hidden shadow-2xl cursor-pointer"
        onclick="window.location.href='offers.html'"
      >
        <img
          src="./image/offer.jpeg"
          alt="Offers"
          class="w-full h-full object-cover transition-opacity duration-300 hover:opacity-60"
        />
        <div
          class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center text-white"
        >
          <h3 class="text-3xl font-bold mb-3">Special Offers</h3>
          <p class="text-base mb-5">Limited-time discounts on premium services!</p>
          <button
            class="bg-orange-500 text-white border-none px-5 py-2 font-semibold rounded-lg hover:bg-orange-600 transition-colors"
          >
            View Offers
          </button>
        </div>
      </div>
    </div>

    <!-- Services Section -->
    <h2 class="text-4xl font-bold my-10 text-orange-600">Our Services</h2>
    <div
      class="service-list flex flex-wrap justify-center gap-8 px-4"
      id="serviceContainer"
    >
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='cleaning.html'"
      >
        <img
          src="./image/clen.jpeg"
          alt="Cleaning"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Cleaning
        </p>
      </div>
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='gardening.html'"
      >
        <img
          src="./image/garden.jpeg"
          alt="Gardening"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Gardening
        </p>
      </div>
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='electrician.html'"
      >
        <img
          src="./image/electric.jpeg"
          alt="Electrician"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Electrician
        </p>
      </div>
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='painting.html'"
      >
        <img
          src="./image/painter.jpeg"
          alt="Painting"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Painting
        </p>
      </div>
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='laundry.html'"
      >
        <img
          src="./image/llaundery.jpeg"
          alt="Laundry"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Laundry
        </p>
      </div>
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='plumbing.html'"
      >
        <img
          src="./image/plumber.jpeg"
          alt="Plumbing"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Plumbing
        </p>
      </div>
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='cooking.html'"
      >
        <img
          src="./image/cook.jpeg"
          alt="Cook"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Cook
        </p>
      </div>
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='carpentry.html'"
      >
        <img
          src="./image/carpentry.jpeg"
          alt="Carpentry"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Carpentry
        </p>
      </div>
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='salon.html'"
      >
        <img
          src="./image/salon.jpeg"
          alt="Salon"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Salon & Beauty
        </p>
      </div>
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='decoration.html'"
      >
        <img
          src="./image/decoration.jpeg"
          alt="Decoration"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Decoration
        </p>
      </div>
      <div
        class="service-item flip-animation w-72 bg-white/90 p-5 rounded-xl shadow-lg transition-all duration-300 cursor-pointer"
        onclick="window.location.href='automobile.html'"
      >
        <img
          src="./image/automobile.jpeg"
          alt="Automobile"
          class="w-full h-52 object-cover rounded-lg"
        />
        <p
          class="mt-4 text-lg font-semibold bg-orange-500 text-white py-3 px-5 rounded-lg hover:bg-orange-600 transition-colors"
        >
          Automobile
        </p>
      </div>
    </div>

    <script>
      // Search functionality with animation
      function searchServices() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let services = document.querySelectorAll(".service-item");

        services.forEach((service) => {
          let serviceName = service.querySelector("p").innerText.toLowerCase();
          if (serviceName.includes(input)) {
            service.style.display = "block";
          } else {
            service.style.display = "none";
          }
        });

        // Trigger search animation
        let searchInput = document.getElementById("searchInput");
        searchInput.classList.add("search-animation");
        setTimeout(() => {
          searchInput.classList.remove("search-animation");
        }, 300);
      }

      // Parallax Tilt Effect for Promo Cards
      document.querySelectorAll(".tilt").forEach((card) => {
        card.addEventListener("mousemove", (e) => {
          const rect = card.getBoundingClientRect();
          const x = e.clientX - rect.left;
          const y = e.clientY - rect.top;
          const centerX = rect.width / 2;
          const centerY = rect.height / 2;
          const tiltX = (y - centerY) / 20;
          const tiltY = -(x - centerX) / 20;
          card.style.transform = `rotateX(${tiltX}deg) rotateY(${tiltY}deg) scale(1.05)`;
        });

        card.addEventListener("mouseleave", () => {
          card.style.transform = "rotateX(0deg) rotateY(0deg) scale(1)";
        });
      });
    </script>
  </body>
</html>