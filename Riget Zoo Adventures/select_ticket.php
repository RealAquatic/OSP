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

    // Persist and resolve booking date: prefer GET, fall back to session
    if (isset($_GET['date']) && $_GET['date'] !== '') {
        $date = htmlspecialchars($_GET['date']);
        $_SESSION['booking_date'] = $date;
    } elseif (isset($_SESSION['booking_date'])) {
        $date = $_SESSION['booking_date'];
    } else {
        $date = '';
    }

    // Persist ticket selections if provided in the query string
    $ticketKeys = ['qty_adult','qty_child','qty_student','qty_under3','price_adult','price_child','price_student','price_under3'];
    foreach ($ticketKeys as $k) {
        if (isset($_GET[$k])) {
            $_SESSION['ticket'][$k] = $_GET[$k];
        }
    }
    // Ensure prices exist in session (defaults)
    if (!isset($_SESSION['ticket']['price_adult'])) $_SESSION['ticket']['price_adult'] = '15.00';
    if (!isset($_SESSION['ticket']['price_child'])) $_SESSION['ticket']['price_child'] = '9.00';
    if (!isset($_SESSION['ticket']['price_student'])) $_SESSION['ticket']['price_student'] = '6.00';
    if (!isset($_SESSION['ticket']['price_under3'])) $_SESSION['ticket']['price_under3'] = '0.00';
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
        <h>Select Ticket Type</h>

        <form action="Payment.php" method="get">
            <input type="hidden" name="date" value="<?php echo $date; ?>">
            <input type="hidden" name="type" value="ticket">
            <div class="Dashboard">
                <div class="Dashboard-left">
                    <div class="BoardTitle">Ticket Types</div>
                    <table class="TicketTable">
                        <thead>
                            <tr>
                                <th class="type-col">Ticket Type</th>
                                <th class="price-col">Price</th>
                                <th class="qty-col">Quantity</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Adult</td>
                                <td>£<?php echo number_format((float)$_SESSION['ticket']['price_adult'],2); ?></td>
                                <td class="qty-col">
                                    <select name="qty_adult" class="dashboard-select">
                                        <?php for ($i=0;$i<=10;$i++): ?>
                                            <option value="<?php echo $i; ?>" <?php if(isset($_SESSION['ticket']['qty_adult']) && $_SESSION['ticket']['qty_adult']==$i) echo 'selected'; ?>><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Child</td>
                                <td>£<?php echo number_format((float)$_SESSION['ticket']['price_child'],2); ?></td>
                                <td class="qty-col">
                                    <select name="qty_child" class="dashboard-select">
                                        <?php for ($i=0;$i<=10;$i++): ?>
                                            <option value="<?php echo $i; ?>" <?php if(isset($_SESSION['ticket']['qty_child']) && $_SESSION['ticket']['qty_child']==$i) echo 'selected'; ?>><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Student</td>
                                <td>£<?php echo number_format((float)$_SESSION['ticket']['price_student'],2); ?></td>
                                <td class="qty-col">
                                    <select name="qty_student" class="dashboard-select">
                                        <?php for ($i=0;$i<=10;$i++): ?>
                                            <option value="<?php echo $i; ?>" <?php if(isset($_SESSION['ticket']['qty_student']) && $_SESSION['ticket']['qty_student']==$i) echo 'selected'; ?>><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Under 3</td>
                                <td>£<?php echo number_format((float)$_SESSION['ticket']['price_under3'],2); ?></td>
                                <td class="qty-col">
                                    <select name="qty_under3" class="dashboard-select">
                                        <?php for ($i=0;$i<=10;$i++): ?>
                                            <option value="<?php echo $i; ?>" <?php if(isset($_SESSION['ticket']['qty_under3']) && $_SESSION['ticket']['qty_under3']==$i) echo 'selected'; ?>><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>

                <div class="PricingPanel">
                    <h4>Selection Summary</h4>
                    <p>Select quantities for each ticket type and continue to payment.</p>
                    <p><strong>Date:</strong> <?php echo $date ? $date : 'Not selected'; ?></p>
                </div>
            </div>
            <div class="continue-row" style="margin:1.5vh 5vw 3vh 5vw;">
                <button type="submit" class="continue-btn">Continue to Payment</button>
            </div>
        </form>
    </div>

    <script>
        (function(){
            // Validate ticket form: require at least one ticket quantity > 0
            const form = document.querySelector('form[action="Payment.php"]');
            if (!form) return;
            form.addEventListener('submit', function(e){
                const qtyNames = ['qty_adult','qty_child','qty_student','qty_under3'];
                let total = 0;
                qtyNames.forEach(function(n){
                    const el = form.querySelector('[name="'+n+'"]');
                    if (el) total += parseInt(el.value || '0', 10);
                });
                if (total <= 0) {
                    e.preventDefault();
                    alert('Please select at least one ticket to continue.');
                    return false;
                }
            });
        })();
    </script>

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