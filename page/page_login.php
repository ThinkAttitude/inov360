<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RH360 - Login</title>

  <!-- CSS Files -->
  <link rel="stylesheet" href="../css/global.css">
  <link rel="stylesheet" href="../css/page_login.css">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="login-container">
    <div class="login-box">
      <!-- Brand Section -->
      <div class="login-brand">
        <div class="logo">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </div>
        <h1>RH360</h1>
        <p>A gestão de recursos humanos, simplificada.</p>
        <div class="brand-divider"></div>
      </div>

      <!-- Login Form -->
      <form id="loginForm" class="login-form" action="../api/login.php" method="POST">
        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-wrapper">
            <input type="email" id="email" name="email" class="form-input" required autocomplete="email">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
              <polyline points="22,6 12,13 2,6"></polyline>
            </svg>
          </div>
        </div>

        <div class="form-group">
          <label for="password">Palavra-passe</label>
          <div class="input-wrapper">
            <input type="password" id="password" name="password" class="form-input" required autocomplete="current-password">
            <svg class="input-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
              <circle cx="12" cy="16" r="1"></circle>
              <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
            </svg>
            <button type="button" id="passwordToggle" class="password-toggle" tabindex="-1">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </button>
          </div>
        </div>

        <button type="submit" id="submitBtn" class="login-submit">
          <span class="btn-text">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
              <polyline points="10,17 15,12 10,7"></polyline>
              <line x1="15" y1="12" x2="3" y2="12"></line>
            </svg>
            Entrar
          </span>
          <div class="spinner"></div>
        </button>
      </form>
    </div>
  </div>

  <!-- JavaScript -->
  <script src="../js/page_login.js"></script>
</body>
</html>