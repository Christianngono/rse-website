<?php if (isset($_GET['message']) || isset($_GET['error'])): ?>
  <div class="alerts">
    <?php if (isset($_GET['message'])): ?>
      <div class="alert-success" id="alert-message">
        <span><?= htmlspecialchars($_GET['message']) ?></span>
        <button onclick="document.getElementById('alert-message').style.display='none'">×</button>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
      <div class="alert-error" id="alert-error">
        <span><?= htmlspecialchars($_GET['error']) ?></span>
        <button onclick="document.getElementById('alert-error').style.display='none'">×</button>
      </div>
    <?php endif; ?>
  </div>
<?php endif; ?>