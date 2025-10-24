<nav class="navbar container has-background-dark" role="navigation" aria-label="main navigation">
    <div class="navbar-brand">
        <a class="navbar-item" href="index.php">
            <img src="./logo.png" width="100" height="200">
        </a>

        <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </a>
    </div>

    <div id="navbarBasicExample" class="navbar-menu">
        <div class="navbar-end">
            <div class="navbar-item">
                <div class="buttons">

                    <div class="navbar-item has-dropdown is-hoverable" id="profile" style="display:none;">
                        <a class="navbar-link is-size-6">
                            <i class="bi bi-person-fill is-size-5"></i>
                            <span id="name" style="margin-left: 6px;"></span>
                        </a>
                        <div class="navbar-dropdown">
                            <a class="navbar-item is-size-6" id="companyMenu" href="./admin.php">Firma Menu</a>
                            <a class="navbar-item is-size-6" id="adminMenu" href="./administrator.php">Yonetim</a>
                            <a class="navbar-item is-size-6" href="mytickets.php">Biletlerim</a>
                            <hr class="navbar-divider">
                            <a class="navbar-item is-size-6" id="logoutBtn" >Çıkış Yap</a>
                        </div>
                    </div>

                    <a class="button is-link" id="loginbtn" href="login.php">
                        Giriş Yap
                    </a>

                </div>
            </div>
        </div>
    </div>
</nav>


