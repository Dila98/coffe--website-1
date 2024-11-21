<?php
session_start();
try {
    $conn = new PDO("mysql:host=localhost;dbname=coffee_shop", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Coffee Website</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <!-- Header / Navbar -->
    <header>
      <nav class="navbar">
        <a href="#" class="nav-logo">
          <h2 class="logo-text">☕ Coffee</h2>
        </a>

        <ul class="nav-menu">
          <button id="menu-close-button" class="fas fa-times"></button>

          <li class="nav-item">
            <a href="#" class="nav-link">Home</a>
          </li>
          <li class="nav-item">
            <a href="#about" class="nav-link">About</a>
          </li>
          <li class="nav-item">
            <a href="#menu" class="nav-link">Menu</a>
          </li>
          
          <li class="nav-item">
            <a href="#gallery" class="nav-link">Gallery</a>
          </li>
          <li class="nav-item">
            <a href="#contact" class="nav-link">Contact</a>
          </li>
          <?php
          if(isset($_SESSION['user_id'])) {
              echo '<li class="nav-item"><a href="./account.php" class="nav-link">My Account</a></li>';
              echo '<li class="nav-item"><a href="./logout.php" class="nav-link">Logout</a></li>';
          } else {
              echo '<li class="nav-item"><a href="./login.php" class="nav-link">Login</a></li>';
              echo '<li class="nav-item"><a href="./signup.php" class="nav-link">Sign Up</a></li>';
          }
          ?>
        </ul>

        <button id="menu-open-button" class="fas fa-bars"></button>
      </nav>
    </header>

    <main>
      <!-- Hero section -->
      <section class="hero-section">
        <div class="section-content">
          <div class="hero-details">
            <h2 class="title">Best Coffee ever</h2>
            <h3 class="subtitle">Start your day right with the perfect cup of our special coffee!</h3>
            <p class="description">Welcome to our coffee paradise, where every bean tells a story and every cup sparks joy.</p>

            <div class="buttons">
              <a href="#contact" class="button contact-us">Contact Us</a>
            </div>
          </div>
        
        </div>
      </section>

      <!-- Menu section -->
      <section class="menu-section" id="menu">
        <h2 class="section-title">Our Menu</h2>
        <div class="section-content">
          <ul class="menu-list">
            <?php
            try {
                $menu_query = $conn->query("
                    SELECT m.*, c.name as category_name 
                    FROM menu_items m 
                    JOIN categories c ON m.category_id = c.id 
                    WHERE m.is_available = 1
                ");
                $menu_items = $menu_query->fetchAll();

                foreach($menu_items as $item): ?>
                <li class="menu-item">
                    <img src="images/<?php echo $item['image_path']; ?>" alt="<?php echo $item['name']; ?>" class="menu-image" />
                    <div class="menu-details">
                        <h3 class="name"><?php echo $item['name']; ?></h3>
                        <p class="text"><?php echo $item['description']; ?></p>
                        <p class="price">LKR <?php echo number_format($item['price'], 2); ?></p>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <form action="process_order.php" method="POST" class="order-form">
                                <input type="hidden" name="menu_item_id" value="<?php echo $item['id']; ?>">
                                <input type="hidden" name="price" value="<?php echo $item['price']; ?>">
                                <div class="order-controls">
                                    <input type="number" name="quantity" value="1" min="1" max="10" class="quantity-input">
                                    <button type="submit" class="order-button">
                                        <i class="fas fa-shopping-cart"></i> Order Now
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <a href="login.php" class="login-to-order">
                                <i class="fas fa-sign-in-alt"></i> Login to Order
                            </a>
                        <?php endif; ?>
                    </div>
                </li>
                <?php endforeach;
            } catch(PDOException $e) {
                error_log("Menu items query failed: " . $e->getMessage());
            }
            ?>
          </ul>
        </div>
      </section>

      <!-- Gallery section -->
      <section class="gallery-section" id="gallery">
        <h2 class="section-title">Gallery</h2>
        <div class="section-content">
          <ul class="gallery-list">
            <li class="gallery-item">
              <img src="images/gallery-1.jpg" alt="Gallery Image" class="gallery-image" />
            </li>
            <li class="gallery-item">
              <img src="images/gallery-2.jpg" alt="Gallery Image" class="gallery-image" />
            </li>
            <li class="gallery-item">
              <img src="images/gallery-3.jpg" alt="Gallery Image" class="gallery-image" />
            </li>
            <li class="gallery-item">
              <img src="images/gallery-4.jpg" alt="Gallery Image" class="gallery-image" />
            </li>
            <li class="gallery-item">
              <img src="images/gallery-5.jpg" alt="Gallery Image" class="gallery-image" />
            </li>
            <li class="gallery-item">
              <img src="images/gallery-6.jpg" alt="Gallery Image" class="gallery-image" />
            </li>
          </ul>
        </div>
      </section>

      <!-- About section -->
      <section class="about-section" id="about">
        <div class="section-content">
          <div class="about-image-wrapper">
            <img src="images/about-image.jpg" alt="About" class="about-image" />
          </div>
          <div class="about-details">
            <h2 class="section-title">About Us</h2>
            <p class="text">At Coffee House in Colombo, SriLanka, we pride ourselves on being a go-to destination for coffee lovers and conversation seekers alike. We're dedicated to providing an exceptional coffee experience in a cozy and inviting atmosphere, where guests can relax, unwind, and enjoy their time in comfort.</p>
            <div class="social-link-list">
              <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
              <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
              <a href="#" class="social-link"><i class="fa-brands fa-x-twitter"></i></a>
            </div>
          </div>
        </div>
      </section>

      <!-- Contact section -->
      <section class="contact-section" id="contact">
        <h2 class="section-title">Contact Us</h2>
        <div class="section-content">
          <ul class="contact-info-list">
            <li class="contact-info">
              <i class="fa-solid fa-location-crosshairs"></i>
              <p> main road,colombo 3</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-envelope"></i>
              <p>info@coffeelanka.com</p>
            </li>
            <li class="contact-info">
              <i class="fa-solid fa-phone"></i>
              <p>0112345678</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-clock"></i>
              <p>Monday - Friday: 9:00 AM - 5:00 PM</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-clock"></i>
              <p>Saturday: 10:00 AM - 3:00 PM</p>
            </li>
            <li class="contact-info">
              <i class="fa-regular fa-clock"></i>
              <p>Sunday: Closed</p>
            </li>
            <li class="contact-info">
              <i class="fa-solid fa-globe"></i>
              <p>www.coffeelanka.com</p>
            </li>
          </ul>

          <form action="#" class="contact-form">
            <input type="text" placeholder="Your name" class="form-input" required />
            <input type="email" placeholder="Your email" class="form-input" required />
            <textarea placeholder="Your message" class="form-input" required></textarea>
            <button type="submit" class="button submit-button">Submit</button>
          </form>
        </div>
      </section>

      <!-- Footer section -->
      <footer class="footer-section">
        <div class="section-content">
          <p class="copyright-text">© 2024 Coffee Shop</p>

          <div class="social-link-list">
            <a href="#" class="social-link"><i class="fa-brands fa-facebook"></i></a>
            <a href="#" class="social-link"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" class="social-link"><i class="fa-brands fa-x-twitter"></i></a>
          </div>

          <p class="policy-text">
            <a href="#" class="policy-link">Privacy policy</a>
            <span class="separator">•</span>
            <a href="#" class="policy-link">Refund policy</a>
          </p>
        </div>
      </footer>
    </main>

    <!-- Linking custom script -->
    <script src="script.js"></script>
  </body>
</html>
