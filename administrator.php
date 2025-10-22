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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">

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
                         <li><a href="#users">Kullanicilar</a></li>
                        <li><a href="#kupon">Kupon</a></li>
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
                                    Firmalar
                                </p>

                            </header>
                            <div class="card-table">
                                <div class="content">
                                    <table class="table is-fullwidth is-striped">
                                        <tbody id="companies">


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
                                    Firma Ekle
                                </p>
                            </header>
                            <div class="card-content" id="addTrip">



                                <div class="field">
                                    <label class="label">İsim</label>
                                    <div class="control">
                                        <input type="text" id="company_name" class="input"  >
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Logo Url</label>
                                    <div class="control">
                                        <input class="input" id="logo_url" type="text">
                                    </div>
                                </div>


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

                        <div class="columns" id="kupon">
            <div class="column is-7">

                <div class="card-table">
                    <div class="content">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th width="5%"></th>
                                    <th>Kullanicilar</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="users">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="column is-5">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            Yetkilendir
                        </p>
                    </header>
                    <div class="card-content" id="addTrip">
                        <div class="field">
                            <label class="label">Kullanıcı ID</label>
                            <div class="control">
                                <input class="input" id="user_id" type="number" min="1" max="100"
                                    placeholder="Örn: 10">
                            </div>
                        </div>
                        <label class="label">Firma ID</label>
                        <div class="control">
                            <input class="input" id="companyId" type="number" min="1" max="100" placeholder="Örn: 10">
                        </div>
                    </div>



                </div>
                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" id="authorizeBtn">Yetkilendir</button>
                    </div>

                </div>
            </div>











        </div>

        <div class="columns" id="kupon">
            <div class="column is-7">

                <div class="card-table">
                    <div class="content">
                        <table class="table is-fullwidth is-striped">
                            <thead>
                                <tr>
                                    <th width="5%"></th>
                                    <th>Kupon Bilgileri</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="coupons">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="column is-5">
                <div class="card">
                    <header class="card-header">
                        <p class="card-header-title">
                            Kupon Ekle
                        </p>
                    </header>
                    <div class="card-content" id="addTrip">
                            <div class="field">
                            <label class="label">Kod</label>
                            <div class="control">
                                <input class="input" id="code" type="text" min="1" max="100"
                                    placeholder="Örn: ABCD1234">
                            </div>
                        </div>
                        <div class="field">
                            <label class="label">İndirim Oranı (%)</label>
                            <div class="control">
                                <input class="input" id="discount" type="number" min="1" max="100"
                                    placeholder="Örn: 10">
                            </div>
                        </div>
                        
                        <label class="label">Kupon adeti</label>
                        <div class="control">
                            <input class="input" id="usageLimit" type="number" min="1" max="100" placeholder="Örn: 10">
                        </div>
                        <div class="field">
                            <label class="label">Geçerlilik Tarihi</label>
                            <div class="control">
                                <input class="input" id="couponExpireDate" type="date" required>
                            </div>
                        </div>
                    </div>



                </div>
                <div class="field is-grouped">
                    <div class="control">
                        <button class="button is-link" id="couponBtn">Üret</button>
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
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script src="./js/navbar.js"></script>
<script src="./js/administrator.js"></script>
<script>

    document.addEventListener('DOMContentLoaded', () => {
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