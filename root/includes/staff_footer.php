<?php // includes/staff_footer.php ?>
  </main><!-- .content -->
</div><!-- .app -->

<footer>
  <div class="container">
    <div class="row">
      <div class="col-md-3 mb-4">
        <img src="<?= e(asset_url('assets/images/logo2.png')) ?>" alt="JaniKing" class="footer-logo">
        <p>Your trusted partner in commercial cleaning franchise opportunities. Building success through cleanliness.</p>
        <div class="social-icons mt-4">
          <a href="#" class="social-icon facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="#" class="social-icon twitter"><i class="fab fa-twitter"></i></a>
          <a href="#" class="social-icon linkedin"><i class="fab fa-linkedin-in"></i></a>
          <a href="#" class="social-icon instagram"><i class="fab fa-instagram"></i></a>
        </div>
      </div>

      <div class="col-md-3 mb-4">
        <h5>Quick Links</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="<?= e(asset_url('index.php')) ?>">Home</a></li>
          <li class="mb-2"><a href="<?= e(asset_url('about_us.php')) ?>">About Us</a></li>
          <li class="mb-2"><a href="<?= e(asset_url('join_us.php')) ?>">Join Us</a></li>
          <li class="mb-2"><a href="<?= e(asset_url('contact_us.php')) ?>">Contact Us</a></li>
          <li class="mb-2"><a href="<?= e(asset_url('book_appointment.php')) ?>">Book Appointment</a></li>
        </ul>
      </div>

      <div class="col-md-3 mb-4">
        <h5>Our Services</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="<?= e(asset_url('join_us.php')) ?>">Franchise Opportunities</a></li>
          <li class="mb-2"><a href="#">Commercial Cleaning</a></li>
          <li class="mb-2"><a href="#">Training Programs</a></li>
          <li class="mb-2"><a href="#">Equipment Supply</a></li>
          <li class="mb-2"><a href="#">Support Services</a></li>
        </ul>
      </div>

      <div class="col-md-3 mb-4">
        <h5>Contact Us</h5>
        <ul class="list-unstyled">
          <li class="mb-2"><i class="fas fa-map-marker-alt me-2"></i> 10 Barrack St, Sydney NSW 2000</li>
          <li class="mb-2"><i class="fas fa-phone me-2"></i> (123) 123 456</li>
          <li class="mb-2"><i class="fas fa-envelope me-2"></i> info@janiking.com</li>
          <li class="mb-2"><i class="fas fa-info-circle me-2"></i> Mon–Fri: 8AM–6PM</li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom text-center">
      <p>© <?= date('Y') ?> JaniKing Commercial Cleaning Franchise. All rights reserved.</p>
    </div>
  </div>
</footer>

<!-- Tawk.to (optional; keep or remove) -->
<script>
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
  var s1=document.createElement("script"), s0=document.getElementsByTagName("script")[0];
  s1.async=true; s1.src='https://embed.tawk.to/68af2572c7e55d19240e17a1/1j3m2kich';
  s1.charset='UTF-8'; s1.setAttribute('crossorigin','*'); s0.parentNode.insertBefore(s1,s0);
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= e(asset_url('assets/js/main.js')) ?>"></script>
</body>
</html>
