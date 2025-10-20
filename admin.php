<!DOCTYPE html>
<html>
<html class="theme-light">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">
    <!-- Bulma Version 1-->
    <link rel="stylesheet" href="https://unpkg.com/bulma@1.0.4/css/bulma.min.css" />
    <script src="https://code.jquery.com/jquery-3.7.1.js"
        integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
</head>

<body>

    <!-- START NAV -->
    <?php include_once './inc/navbar.php' ?>
    <!-- END NAV -->
    <div class="container">
        <div class="columns">
            <div class="column is-3 ">
                <aside class="menu is-hidden-mobile">

                    <ul class="menu-list">
                        <li><a class="is-active ">Genel</a></li>
                    </ul>
                </aside>
            </div>
            <div class="column is-9">

                <section class="hero is-info welcome is-small">
                    <div class="hero-body">
                        <div class="container">
                            <h1 class="title">
                                Merhaba, <span id="name"></span>
                            </h1>

                        </div>
                    </div>
                </section>
                <section class="info-tiles">
                    <div class="grid has-text-centered">
                        <div class="cell">
                            <article class="box">
                                <p class="title">439</p>
                                <p class="subtitle">Gelecek Sefer</p>
                            </article>
                        </div>
                        <div class="cell">
                            <article class="box">
                                <p class="title">59k</p>
                                <p class="subtitle">Gecmiş Sefer</p>
                            </article>
                        </div>
                        <div class="cell">
                            <article class="box">
                                <p class="title">3.4k</p>
                                <p class="subtitle">Toplam Sefer</p>
                            </article>
                        </div>
                    </div>
                </section>
                <div class="columns">
                    <div class="column is-6 mt-5">
                        <div class="card events-card">
                            <header class="card-header">
                                <p class="card-header-title">
                                    Seferler
                                </p>

                            </header>
                            <div class="card-table">
                                <div class="content">
                                    <table class="table is-fullwidth is-striped">
                                        <tbody id="trips">







                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <footer class="card-footer">
                            </footer>
                        </div>
                    </div>
                    <div class="column is-6 mt-5">
                        <div class="card">
                            <header class="card-header">
                                <p class="card-header-title">
                                    Sefer Ekle
                                </p>
                            </header>
                            <div class="card-content" id="addTrip">

                                <div class="field">
                                    <label class="label">Nereden</label>
                                    <div class="control">
                                        <div class="select">
                                            <select id="from">
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

                                <div class="field">
                                    <label class="label">Nereye</label>
                                    <div class="control">
                                        <div class="select">
                                            <select id="to">
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
                                <div class="field">
                                    <label class="label">Fiyat</label>
                                    <div class="control">
                                        <input class="input" id="setPrice" type="number">
                                    </div>
                                </div>

                                <label class="label">Kalkış Tarihi</label>
                                <div class="control">
                                    <input class="input " id="setDepertureDate" type="date">
                                </div>
                                <label class="label">Varış Tarihi</label>
                                <div class="control">
                                    <input class="input " id="setArrivalDate" type="date">
                                </div>
                                <label class="label">Kalkış saati</label>
                                <div class="control">
                                    <input class="input" id="setDepertureTime" type="time">
                                </div>
                                <label class="label">Varış saati</label>
                                <div class="control">
                                    <input class="input" id="setArrivalTime" type="time">
                                </div>
                            </div>

                        </div>



                        <!-- Edit Trip Modal -->
                        <div class="modal" id="editTripModal">
                            <div class="modal-background"></div>
                            <div class="modal-card">
                                <header class="modal-card-head">
                                    <p class="modal-card-title">Sefer Düzenle</p>
                                    <button class="delete" aria-label="close"></button>
                                </header>
                                <section class="modal-card-body">
                                    <form id="editTripForm">
                                        <input type="hidden" id="editTripId" name="trip_id">

                                        <div class="field">
                                            <label class="label">Nereden</label>
                                            <div class="control">
                                                <select class="input" type="text" id="editDepartureCity"
                                                    name="departure_city" required>
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

                                        <div class="field">
                                            <label class="label">Nereye</label>
                                            <div class="control">
                                                <select class="input" type="text" id="editDestinationCity"
                                                    name="destination_city" required>
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

                                        <div class="field">
                                            <label class="label">Fiyat</label>
                                            <div class="control">
                                                <input class="input" type="number" id="editPrice" name="price" min="0"
                                                    step="0.01" required>
                                            </div>
                                        </div>

                                        <div class="field">
                                            <label class="label">Kalkış Tarihi</label>
                                            <div class="control">
                                                <input class="input" type="date" id="editDepartureDate"
                                                    name="departure_date" required>
                                            </div>
                                        </div>

                                        <div class="field">
                                            <label class="label">Kalkış Saati</label>
                                            <div class="control">
                                                <input class="input" type="time" id="editDepartureTime"
                                                    name="departure_time" required>
                                            </div>
                                        </div>

                                        <div class="field">
                                            <label class="label">Varış Saati</label>
                                            <div class="control">
                                                <input class="input" type="time" id="editArrivalTime"
                                                    name="arrival_time" required>
                                            </div>
                                        </div>


                                    </form>
                                </section>
                                <footer class="modal-card-foot">
                                    <div class="buttons">
                                        <button class="button is-success" id="saveTripBtn">Kaydet</button>
                                        <button class="button">İptal</button>
                                    </div>
                                </footer>
                            </div>
                        </div>




                        <div class="field is-grouped">
                            <div class="control">
                                <button class="button is-link" id="createBtn">Oluştur</button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
    </div>
    <?php require_once 'inc/footer.php'; ?>

</body>
<script src="./js/navbar.js"></script>
<script src="./js/admin.js"></script>
<script>

    document.addEventListener('DOMContentLoaded', () => {
        // Functions to open and close a modal
        function openModal($el) {
            $el.classList.add('is-active');
        }

        function closeModal($el) {
            $el.classList.remove('is-active');
        }

        function closeAllModals() {
            (document.querySelectorAll('.modal') || []).forEach(($modal) => {
                closeModal($modal);
            });
        }

        // Add a click event on buttons to open a specific modal
        (document.querySelectorAll('.js-modal-trigger') || []).forEach(($trigger) => {
            const modal = $trigger.dataset.target;
            const $target = document.getElementById(modal);

            $trigger.addEventListener('click', () => {
                openModal($target);
            });
        });

        // Add a click event on various child elements to close the parent modal
        (document.querySelectorAll('.modal-background, .modal-close, .modal-card-head .delete, .modal-card-foot .button') || []).forEach(($close) => {
            const $target = $close.closest('.modal');

            $close.addEventListener('click', () => {
                closeModal($target);
            });
        });

        // Add a keyboard event to close all modals
        document.addEventListener('keydown', (event) => {
            if (event.key === "Escape") {
                closeAllModals();
            }
        });
    });
</script>


</html>