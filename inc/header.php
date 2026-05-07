   <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    ?>
   <header id="header" class="header fixed-top">

       <div class="topbar d-flex align-items-center dark-background">
           <div class="container d-flex justify-content-center justify-content-md-between">
               <div class="contact-info d-flex align-items-center">
                   <i class="bi bi-envelope d-flex align-items-center"><a
                           href="mailto:contact@example.com">contact@example.com</a></i>
                   <i class="bi bi-phone d-flex align-items-center ms-4"><span>+1 5589 55488 55</span></i>
               </div>
               <div class="social-links d-none d-md-flex align-items-center">
                   <a href="#!" class="twitter"><i class="bi bi-twitter-x"></i></a>
                   <a href="#!" class="facebook"><i class="bi bi-facebook"></i></a>
                   <a href="#!" class="instagram"><i class="bi bi-instagram"></i></a>
                   <a href="#!" class="linkedin"><i class="bi bi-linkedin"></i></a>
               </div>
           </div>
       </div><!-- End Top Bar -->

       <div class="branding d-flex align-items-cente">

           <div class="container position-relative d-flex align-items-center justify-content-between">
               <a href="index.html" class="logo d-flex align-items-center">
                   <!-- Uncomment the line below if you also wish to use an image logo -->
                   <!-- <img src="assets/img/logo.webp" alt=""> -->
                   <h1 class="sitename "><img src="assets/img/logo.webp" alt=""></h1>
               </a>

               <nav id="navmenu" class="navmenu"> 
                   <ul>
                       <li><a href="index" class="<?= ($current_page == 'index.php') ? 'active' : '' ?>">Home</a></li>

                       <li><a href="about" class="<?= ($current_page == 'about.php') ? 'active' : '' ?>">About</a></li>

                       <li><a href="services" class="<?= ($current_page == 'services.php') ? 'active' : '' ?>">Services</a></li>

                       <li class="dropdown">
                           <a href="#" class="<?= in_array($current_page, ['department-details.html', 'service-details.html', 'appointment.html', 'testimonials.html', 'faq.html', 'gallery.html', 'terms.html', 'privacy.html', '404.html']) ? 'active' : '' ?>">
                               <span>More Pages</span>
                               <i class="bi bi-chevron-down toggle-dropdown"></i>
                           </a>

                           <ul>
                               <!-- <li><a href="department-details.html">Department Details</a></li>
                               <li><a href="service-details.html">Service Details</a></li> -->
                               <li><a href="appointment.html">Appointment</a></li>
                               <li><a href="testimonials.php">Testimonials</a></li>
                               <li><a href="faq.php">Frequently Asked Questions</a></li>
                               <!-- <li><a href="gallery.html">Gallery</a></li> -->
                               <li><a href="terms.php">Terms</a></li>
                               <li><a href="privacy.php">Privacy</a></li>
                               <!-- <li><a href="404.html">404</a></li> -->
                           </ul>
                       </li>

                       <li><a href="contact" class="<?= ($current_page == 'contact.php') ? 'active' : '' ?>">Contact</a></li>
                       <a href="./admin/dashboard/auth-login" class="btn p-2 ms-3 me-3" style="background:#175cdd; color:#fff; border-radius:6px;">
                           ADMIN
                       </a>
                   </ul>
                   <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
               </nav>

           </div>

       </div>

   </header>