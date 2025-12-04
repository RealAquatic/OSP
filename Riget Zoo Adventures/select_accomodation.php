<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RZA | Educational Visits</title>
    <link rel="stylesheet" href="CSS/selection.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Italiana&family=Just+Me+Again+Down+Here&display=swap" rel="stylesheet">
</head>
<body>
    <?php
    session_start();

    // Persist and resolve booking date: prefer explicit GET params, fall back to session
    if (isset($_GET['date']) && $_GET['date'] !== '') {
        $date = htmlspecialchars($_GET['date']);
        $_SESSION['booking_date'] = $date;
    } elseif (isset($_GET['checkin']) && $_GET['checkin'] !== '') {
        // When coming from the date picker for accommodation we receive checkin/checkout
        $date = htmlspecialchars($_GET['checkin']);
        $_SESSION['booking_date'] = $date;
    } elseif (isset($_SESSION['booking_date'])) {
        $date = $_SESSION['booking_date'];
    } else {
        $date = '';
    }

    // If ticket selections are passed in query, persist them
    $ticketKeys = ['qty_adult','qty_child','qty_student','qty_under3','price_adult','price_child','price_student','price_under3'];
    foreach ($ticketKeys as $k) {
        if (isset($_GET[$k])) {
            $_SESSION['ticket'][$k] = $_GET[$k];
        }
    }
    // If the `type` query param is not set, we will add it to the browser URL via JS so user sees ?type=accomodation
    $ensureTypeInUrl = !(isset($_GET['type']) && $_GET['type'] === 'accomodation');
    ?>
    <!-- Display -->
    
    <div class="Display">
        <div class="Header">
            <div class="TopBar">
                <div class="Logo">RZA</div>
                <div class="SiteTitle"> <a href="index.html">Riget Zoo Adventures</a></div>
            </div>
        
            <div class="BottomBar">
                <a href="booking.php">Book Your Tickets</a>
                <a href="educational.html">Educational Visits</a>
                <a href="information.html">Information</a>
                <a href="account.php">Account</a>
            </div>
        </div>
    </div>

    <!-- Content -->
     <div class="Content">
        <p>Experience Riget Zoo Adventures</p>
        <h>Select Accommodation</h>

        <form action="Payment.php" method="get">
            <input type="hidden" name="date" value="<?php echo $date; ?>">
            <input type="hidden" name="type" value="accomodation">
            <!-- Forward only ticket quantities (qty_*) so prices are not passed through -->
            <?php if (!empty($_SESSION['ticket'])): ?>
                <?php foreach ($_SESSION['ticket'] as $k => $v): ?>
                    <?php if (strpos($k, 'qty_') === 0): ?>
                        <input type="hidden" name="<?php echo htmlspecialchars($k); ?>" value="<?php echo htmlspecialchars($v); ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Forward checkin/checkout if present (accommodation flow) -->
            <?php if (isset($_GET['checkin'])): ?>
                <input type="hidden" name="checkin" value="<?php echo htmlspecialchars($_GET['checkin']); ?>">
            <?php endif; ?>
            <?php if (isset($_GET['checkout'])): ?>
                <input type="hidden" name="checkout" value="<?php echo htmlspecialchars($_GET['checkout']); ?>">
            <?php endif; ?>

            <?php if ($ensureTypeInUrl): ?>
            <script>
                (function(){
                    try {
                        const url = new URL(window.location.href);
                        url.searchParams.set('type','accomodation');
                        <?php if ($date): ?>
                        // Ensure date is visible in the URL as well
                        url.searchParams.set('date', '<?php echo $date; ?>');
                        <?php endif; ?>
                        history.replaceState(null, '', url);
                    } catch(e) { /* ignore in older browsers */ }
                })();
            </script>
            <?php endif; ?>
            <script>
                // Ensure the hidden `date` input is populated from checkin if PHP didn't pick it up
                (function(){
                    try {
                        const form = document.querySelector('form[action="Payment.php"]');
                        if (!form) return;
                        const dateInput = form.querySelector('[name="date"]');
                        if (dateInput && (!dateInput.value || dateInput.value === '')) {
                            const url = new URL(window.location.href);
                            const checkin = url.searchParams.get('checkin') || url.searchParams.get('date');
                            if (checkin) {
                                dateInput.value = checkin;
                            }
                        }

                        // On submit, ensure date exists; if not, block and ask user to pick a date
                        form.addEventListener('submit', function(e){
                            const dateVal = (form.querySelector('[name="date"]')||{value:''}).value;
                            if (!dateVal) {
                                e.preventDefault();
                                alert('Please select a check-in date before continuing.');
                                // redirect to date picker for accommodation
                                window.location.href = 'date_selection.php?type=accommodation';
                                return false;
                            }
                        });
                    } catch(err) { /* ignore */ }
                })();
            </script>
            <div class="Dashboard">
                <div class="Dashboard-left">
                    <div class="BoardTitle">Accommodation</div>
                    <table class="AccommodationTable">
                        <thead>
                            <tr>
                                <th class="type-col">Field</th>
                                <th class="price-col">Selection</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Room Type</td>
                                <td>
                                    <select name="room_type" class="dashboard-select">
                                        <option value="">-- Select room --</option>
                                        <option value="single">Single</option>
                                        <option value="double">Double</option>
                                        <option value="king">King</option>
                                        <option value="luxury">Luxury</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Adults</td>
                                <td>
                                    <select name="adults_count" class="dashboard-select">
                                        <?php for ($i=0;$i<=8;$i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Under 16</td>
                                <td>
                                    <select name="under16_count" class="dashboard-select">
                                        <?php for ($i=0;$i<=8;$i++): ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="PricingPanel">
                    <h4>Accommodation Pricing</h4>
                    <p><strong>Adults:</strong> £50.00 per night per person</p>
                    <p><strong>Under 16:</strong> £30.00 per night per person</p>
                    <p style="margin-top:0.8vh;"><strong>Room flat fees:</strong></p>
                    <p>Single: £50.00</p>
                    <p>Double: £80.00</p>
                    <p>King: £130.00</p>
                    <p>Luxury: £200.00</p>
                </div>
            </div>

            <div class="continue-row" style="margin:1.5vh 5vw 3vh 5vw;">
                <button type="submit" class="continue-btn">Continue to Payment</button>
            </div>
        </form>
    </div>

    <!-- Footer -->

    <div class="Footer">
        <img src="Images/Footer.png" class="FooterImage">
        <div class="FooterContent">
            <div class="FooterLogo">RZA</div>
            <div class="FooterInfo">
                <p>Call us: 01790-00000</p>
                <p>Email: <a href="mailto:rzasupport@gmail.com">rzasupport@gmail.com</a></p>
                <p><a href="#">Privacy Policy</a></p>
            </div>
        </div>
    </div>
</body>
</html>