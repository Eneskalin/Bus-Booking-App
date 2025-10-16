<!DOCTYPE html>
<html>


<?php require_once 'inc/header.php' ?>


<body>

    <?php require_once 'inc/navbar.php'; ?>

    <section class="hero is-medium mt-2 is-light">
        <div class="hero-body">
            <div class="columns">
                <div class="column is-8">
                    <p class="subtitle is-size-2">Gezsen</p>
                    <h1 class="is-size-1 title ">Kapadokya</h1>
                </div>
                <div class="column ">
                    <img class="map" src="./Group 8.svg" alt="">

                </div>
            </div>

        </div>
    </section>

    <section class="section  ">
        <div class="container">
            <div class="search-card">
                <form action="search.php" method="GET">
                    <div class="columns is-multiline is-vcentered">


                        <div class="column is-3">
                            <div class="field">
                                <label class="label has-text-white">
                                    <i class="bi bi-geo-alt-fill mr-2"></i>
                                    Nereden </label>
                                <div class="control">
                                    <div class="select is-fullwidth is-rounded">
                                        <select name="from" id="from" class="has-background-dark	" required>
                                            <option value="">Kalkış yerini seçin</option>
                                            <option value="goreme"> Göreme(Nevşehir)</option>
                                            <option value="avanos">Avanos(Nevşehir)</option>
                                            <option value="kayseri">Kayseri (Merkez)</option>
                                            <option value="konya">Konya (Merkez)</option>
                                            <option value="ihlara">Ihlara(Aksaray)</option>
                                            <option value="guzelyurt">Güzelyurt(Aksaray)</option>
                                            <option value="uchisar">Uçhisar(Nevşehir)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="column is-1 has-text-centered">
                            <div class="swap-icon">
                                <i class="bi bi-arrow-left-right has-text-white is-size-4"></i>
                            </div>
                        </div>

                        <div class="column is-3">
                            <div class="field">
                                <label class="label has-text-white">
                                    <i class="bi bi-geo-alt-fill mr-2"></i>
                                    Nereye
                                </label>
                                <div class="control">
                                    <div class="select is-fullwidth is-rounded ">
                                        <select name="to" id="to" class="has-background-dark	" required>
                                            <option value="">Varış yerini seçin</option>
                                            <option value="goreme"> Göreme(Nevşehir)</option>
                                            <option value="avanos">Avanos(Nevşehir)</option>
                                            <option value="kayseri">Kayseri (Merkez)</option>
                                            <option value="konya">Konya (Merkez)</option>
                                            <option value="ihlara">Ihlara(Aksaray)</option>
                                            <option value="guzelyurt">Güzelyurt(Aksaray)</option>
                                            <option value="uchisar">Uçhisar(Nevşehir)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="column is-2">
                            <div class="field">
                                <label class="label has-text-white">
                                    <i class="bi bi-calendar3 mr-2"></i>
                                    Tarih
                                </label>
                                <div class="control">
                                    <input type="date" name="date"
                                        class="input date is-rounded has-text-white has-background-dark	" required>
                                </div>
                            </div>
                        </div>

                        <div class="column is-2">
                            <div class="field">
                                <label class="label has-text-white">
                                    <i class="bi bi-person-fill mr-2"></i>
                                    Yolcu
                                </label>
                                <div class="control">
                                    <div class="select is-fullwidth is-rounded">
                                        <select name="passengers" class="has-background-dark	" required>
                                            <option value="1">1 Yolcu</option>
                                            <option value="2">2 Yolcu</option>
                                            <option value="3">3 Yolcu</option>
                                            <option value="4">4 Yolcu</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="column is-1">
                            <div class="field">
                                <label class="label">&nbsp;</label>
                                <div class="control">
                                    <button type="submit" class="button is-light is-fullwidth is-rounded search-btn">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <div class="container is-justify-content-center	 columns">
        <a href="https://aksaray.goturkiye.com/tr-tr/anasayfa" target="_blank">
            <div class="column box" id="aksaray">
                <h3 class="subtitle is-size-2">Gezsen</h3>
                <h2 class="title">Aksaray</h2>
            </div>
        </a>

        <a href="https://nevsehir.goturkiye.com/tr-tr/anasayfa" target="_blank">
            <div class="column box ml-6" id="nevsehir">
                <h3 class="subtitle is-size-2">Gezsen</h3>
                <h2 class="title">Nevsehir</h2>
            </div>
        </a>
        <a href="https://nigde.goturkiye.com/tr-tr/anasayfa" target="_blank">
            <div class="column box ml-6" id="nigde">
                <h3 class="subtitle is-size-2">Gezsen</h3>
                <h2 class="title">Nigde</h2>
            </div>
        </a>
        <a href="https://kayseri.goturkiye.com/tr-tr/anasayfa" target="_blank">
            <div class="column box ml-6" id="kayseri">
                <h3 class="subtitle is-size-2">Gezsen</h3>
                <h2 class="title">Kayseri</h2>
            </div>
        </a>


    </div>


    <?php require_once 'inc/footer.php'; ?>
</body>
<script src="./js/navbar.js"></script>

</html>