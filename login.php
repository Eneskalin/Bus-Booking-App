
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link rel="stylesheet" href="./css/login.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>

</head>
<body>
  
  <div class="wrapper">
      <div class="title-text">
        <div class="title login">Giriş Yap</div>
        <div class="title signup">Kayıt Ol</div>
      </div>
      <div class="form-container">
        <div class="slide-controls">
          <input type="radio" name="slide" id="login" checked>
          <input type="radio" name="slide" id="signup">
          <label for="login" class="slide login">Giriş Yap</label>
          <label for="signup" class="slide signup">Kayıt Ol</label>
          <div class="slider-tab"></div>
        </div>
        <div class="form-inner">
          <!-- Giris yap -->
          <form action="" method="post" class="login">
            <input type="hidden" name="action" value="login">
            <div class="field">
              <input id="login-mail" type="text" name="email" placeholder="Email Adresi" required>
            </div>
            <div class="field">
              <input id="login-password" type="password" name="password" placeholder="Parola" required>
            </div>
            <div class="field btn">
              <div class="btn-layer"></div>
              <input type="submit" value="Giriş Yap">
            </div>
            <div class="signup-link">Üye değil misin? <a href="">Kayıt Ol</a></div>
          </form>
          <!-- Kayit ol -->
          <form action="" method="post" class="signup">
            <input type="hidden" name="action" value="signup">
            <div class="field">
              <input id="signup-full_name" type="text" name="full_name" placeholder="İsim Soyisim" required>
            </div>
            <div class="field">
              <input id="signup-email" type="text" name="email" placeholder="Email Adresi" required>
            </div>
            <div class="field">
              <input id="signup-password" type="password" name="password" placeholder="Parola" required>
            </div>
            <div class="field">
              <input id="signup-password_confirm" type="password" name="password_confirm" placeholder="Parolayı Onayla" required>
            </div>
            <div class="field btn">
              <div class="btn-layer"></div>
              <input type="submit" value="Kayıt Ol">
            </div>
          </form>
        </div>
      </div>
    </div>

</body>

<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script src="./js/script.js"></script>
</html>
