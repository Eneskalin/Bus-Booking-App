<!DOCTYPE html>
<html>


<?php require_once 'inc/header.php' ?>


<body>

    <?php require_once 'inc/navbar.php'; ?>

    <section class="section">
        <h1 class="title">Biletlerim</h1>
        <div class="container">
            <div class="columns">
                <div class="column is-9">
                    <div class="trip-results ">
                       
                                <div class="trip-card has-background-light">
                                    <div class="columns is-vcentered">
                                        <div class="column is-2">
                                            <div class="company-logo">
                                                <img src=""
                                                    
                                                <p class="company-name has-text-primary">
                                            </div>
                                        </div>
                                        <div class="column is-3">
                                            <div class="trip-info">
                                                <h3 class="departure-city has-text-link">
                                                <p class="departure-time has-text-link is-size-4">
                                            </div>
                                        </div>
                                        <div class="column is-2">
                                            <div class="duration-info has-text-centered">
                                                <i class="bi bi-clock-fill has-text-primary is-size-5"></i>
                                                <p class="duration has-text-primary is-size-5">
                                                   </p>
                                                <div class="route-line"></div>
                                            </div>
                                        </div>
                                        <div class="column is-3">
                                            <div class="trip-info">
                                                <h3 class="arrival-city has-text-link is-size-5">
                                                <p class="arrival-time has-text-link is-size-5">
                                            </div>
                                        </div>
                                        <div class="column is-2">
                                            <div class="price-info">
                                                <p class="price has-text-primary">
                                                <p class="price-per-person has-text-primary">Kişi başı</p>
                                                <form method="GET" action="">
                                                    <input type="hidden" name="from"
                                                        value="<?php echo htmlspecialchars($from); ?>">
                                                    <input type="hidden" name="to" value="<?php echo htmlspecialchars($to); ?>">
                                                    <input type="hidden" name="date"
                                                        value="<?php echo htmlspecialchars($date); ?>">
                                                    <input type="hidden" name="passengers"
                                                        value="<?php echo htmlspecialchars($passengers); ?>">
                                                    <input type="hidden" name="company"
                                                        value="<?php echo htmlspecialchars($company); ?>">
                                                    <button class="button is-danger is-fullwidth mt-2" type="submit">
                                                        <i class="bi bi-arrow-right mr-2"></i>
                                                        İptal Et
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <?php require_once 'inc/footer.php'; ?>
</body>

</html>