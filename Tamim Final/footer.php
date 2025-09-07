</main>
    <footer class="border-top mt-5 py-3">
      <div class="container text-center text-muted small">
        &copy; <span id="year"></span> ShopLite • PHP + MySQL
      </div>
    </footer>
    <script>
      document.getElementById('year').textContent = new Date().getFullYear();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <?php
      // Optional page JS
      if (!empty($pageJs) && is_array($pageJs)) {
        foreach ($pageJs as $src) {
          echo '<script defer src="' . htmlspecialchars($src) . '"></script>' . PHP_EOL;
        }
      }
    ?>
  </body>
</html>
