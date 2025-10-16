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

    <link rel="stylesheet" type="text/css" href="../css/admin.css">
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
                    <p class="menu-label">
                        Genel
                    </p>
                    <ul class="menu-list">
                        <li><a class="is-active ">Dashboard</a></li>

                    </ul>


                </aside>
            </div>
            <div class="column is-9">

                <section class="hero is-info welcome is-small">
                    <div class="hero-body">
                        <div class="container">
                            <h1 class="title">
                                Merhaba, Admin
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
                                        <tbody>
                                            <tr>
                                                <td width="5%"><i class="bi bi-bus-front"></i></td>
                                                <td>Lorum ipsum dolem aire</td>
                                                <td class="level-right"><a class="button is-small is-primary"
                                                        href="#">Action</a></td>
                                            </tr>
                                            <tr>
                                                <td width="5%"><i class="fa fa-bell-o"></i></td>
                                                <td>Lorum ipsum dolem aire</td>
                                                <td class="level-right"><a class="button is-small is-primary"
                                                        href="#">Action</a></td>
                                            </tr>
                                            <tr>
                                                <td width="5%"><i class="fa fa-bell-o"></i></td>
                                                <td>Lorum ipsum dolem aire</td>
                                                <td class="level-right"><a class="button is-small is-primary"
                                                        href="#">Action</a></td>
                                            </tr>
                                            <tr>
                                                <td width="5%"><i class="fa fa-bell-o"></i></td>
                                                <td>Lorum ipsum dolem aire</td>
                                                <td class="level-right"><a class="button is-small is-primary"
                                                        href="#">Action</a></td>
                                            </tr>
                                            <tr>
                                                <td width="5%"><i class="fa fa-bell-o"></i></td>
                                                <td>Lorum ipsum dolem aire</td>
                                                <td class="level-right"><a class="button is-small is-primary"
                                                        href="#">Action</a></td>
                                            </tr>
                                            <tr>
                                                <td width="5%"><i class="fa fa-bell-o"></i></td>
                                                <td>Lorum ipsum dolem aire</td>
                                                <td class="level-right"><a class="button is-small is-primary"
                                                        href="#">Action</a></td>
                                            </tr>
                                            <tr>
                                                <td width="5%"><i class="fa fa-bell-o"></i></td>
                                                <td>Lorum ipsum dolem aire</td>
                                                <td class="level-right"><a class="button is-small is-primary"
                                                        href="#">Action</a></td>
                                            </tr>
                                            <tr>
                                                <td width="5%"><i class="fa fa-bell-o"></i></td>
                                                <td>Lorum ipsum dolem aire</td>
                                                <td class="level-right"><a class="button is-small is-primary"
                                                        href="#">Action</a></td>
                                            </tr>
                                            <tr>
                                                <td width="5%"><i class="fa fa-bell-o"></i></td>
                                                <td>Lorum ipsum dolem aire</td>
                                                <td class="level-right"><a class="button is-small is-primary"
                                                        href="#">Action</a></td>
                                            </tr>
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
                                <a href="#" class="card-header-icon" aria-label="more options">
                                    <span class="icon">
                                        <i class="fa fa-angle-down" aria-hidden="true"></i>
                                    </span>
                                </a>
                            </header>
                            <div class="card-content">

                                <div class="field">
                                    <label class="label">Nereden</label>
                                    <div class="control">
                                        <div class="select">
                                            <select>
                                                <option>Select dropdown</option>
                                                <option>With options</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="field">
                                    <label class="label">Nereye</label>
                                    <div class="control">
                                        <div class="select">
                                            <select>
                                                <option>Select dropdown</option>
                                                <option>With options</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="field">
                                    <label class="label">Fiyat</label>
                                    <div class="control">
                                        <input class="input" type="number">
                                    </div>
                                </div>

                                <label class="label">Tarih</label>
                                <div class="control">
                                    <input class="input" type="date">
                                </div>
                                                            <label class="label">Saat</label>
                            <div class="control">
                                <input class="input" type="time">
                            </div>
                            </div>
                            
                        </div>
                        




                        <div class="field is-grouped">
                            <div class="control">
                                <button class="button is-link">Oluştur</button>
                            </div>

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
    </div>
    <script async type="text/javascript" src="../js/bulma.js"></script>
</body>

</html>