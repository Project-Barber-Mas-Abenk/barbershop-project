<?php
/**
 * Footer Component
 * public/client/components/footer.php
 *
 * Include at the bottom of every page, before </body>
 * JS is loaded here to keep <head> clean.
 */

$asset_root = 'assets';
?>

<!-- ── Footer ── -->
<footer class="site-footer">
  <div class="site-footer__inner">
    <div class="site-footer__brand">
      <h2>SHIFT STUDIO</h2>
      <p>Barbershop</p>
      <p class="site-footer__copy">&copy; <?= date('Y') ?> All Rights Reserved. &nbsp;|&nbsp; Shift Studio Barbershop</p>
    </div>
    <nav class="site-footer__social" aria-label="Social media links">
      <a href="#" target="_blank" rel="noopener noreferrer">Instagram</a>
      <a href="#" target="_blank" rel="noopener noreferrer">TikTok</a>
      <a href="#" target="_blank" rel="noopener noreferrer">YouTube</a>
    </nav>
  </div>
</footer>

<!-- ── Scripts ── -->
<script src="<?= $asset_root ?>/js/main.js" defer></script>
<?php if (!empty($extra_js)): ?>
  <?php foreach ((array)$extra_js as $js): ?>
    <script src="<?= $asset_root ?>/js/<?= htmlspecialchars($js) ?>" defer></script>
  <?php endforeach; ?>
<?php endif; ?>

</body>
</html>