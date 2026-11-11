```php
<?php

require '../Includes/db.php';

$user = requireUser();
$connection = db();

/*
|--------------------------------------------------------------------------
| GET BOOKING ID
|--------------------------------------------------------------------------
*/

$bookingId = (int)($_GET['booking'] ?? 0);

if ($bookingId <= 0) {
    die('Invalid booking.');
}


/*
|--------------------------------------------------------------------------
| GET BOOKING AND CUSTOMER INFORMATION
|--------------------------------------------------------------------------
*/

$statement = $connection->prepare(
    'SELECT
        bookings.*,
        users.name,
        users.email
     FROM bookings
     JOIN users
        ON users.id = bookings.user_id
     WHERE bookings.id = ?
       AND bookings.user_id = ?
     LIMIT 1'
);

$statement->execute([
    $bookingId,
    $user['id']
]);

$booking = $statement->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die('Receipt not found.');
}


/*
|--------------------------------------------------------------------------
| DEFAULT PAYMENT VALUES
|--------------------------------------------------------------------------
*/

$paymentMethod = $booking['payment_method'] ?? '';

$paymentStatus = $booking['payment_status'] ?? 'Unpaid';

$paymentReference = $booking['payment_reference'] ?? '';

$totalAmount = isset($booking['total_amount'])
    ? (float)$booking['total_amount']
    : 0;


/*
|--------------------------------------------------------------------------
| FORMAT BOOKING NUMBER
|--------------------------------------------------------------------------
*/

$bookingNumber = str_pad(
    (string)$booking['id'],
    6,
    '0',
    STR_PAD_LEFT
);


/*
|--------------------------------------------------------------------------
| FORMAT DURATION
|--------------------------------------------------------------------------
*/

$durationMinutes = (int)$booking['duration_minutes'];

$hours = $durationMinutes / 60;

$formattedHours = rtrim(
    rtrim(
        number_format($hours, 1),
        '0'
    ),
    '.'
);


/*
|--------------------------------------------------------------------------
| PAGE CONFIG
|--------------------------------------------------------------------------
*/

$pageTitle = 'Receipt | LFT Dumaguete';
$currentPage = 'RECEIPT';

require '../Includes/header.php';

?>

<main>

<section class="auth-section">

<div class="receipt-wrapper">


    <!-- ================================================================
         RECEIPT
    ================================================================= -->

    <div
        class="receipt-card"
        id="receipt"
    >


        <!-- ============================================================
             HEADER
        ============================================================= -->

        <div class="receipt-header">

            <div class="receipt-logo">

                <!--
                If you have a logo, you can use:

                <img
                    src="../assets/images/logo.png"
                    alt="LFT Dumaguete"
                >
                -->

                <h1>
                    LFT DUMAGUETE
                </h1>

            </div>


            <p class="receipt-subtitle">
                Curated Workspaces
            </p>


            <div class="receipt-title">
                BOOKING RECEIPT
            </div>

        </div>


        <!-- ============================================================
             BOOKING NUMBER
        ============================================================= -->

        <div class="receipt-booking-number">

            <span>
                Booking Number
            </span>

            <strong>
                #<?= e($bookingNumber) ?>
            </strong>

        </div>


        <!-- ============================================================
             PAYMENT STATUS
        ============================================================= -->

        <div class="receipt-status-area">

            <?php if ($paymentStatus === 'Paid'): ?>

                <div class="receipt-status status-paid">

                    <i class="fa-solid fa-circle-check"></i>

                    PAYMENT VERIFIED

                </div>

            <?php elseif (
                $paymentStatus === 'Pending Verification'
            ): ?>

                <div class="receipt-status status-pending">

                    <i class="fa-solid fa-clock"></i>

                    PAYMENT PENDING VERIFICATION

                </div>

            <?php else: ?>

                <div class="receipt-status status-unpaid">

                    <i class="fa-solid fa-circle-exclamation"></i>

                    PAYMENT UNPAID

                </div>

            <?php endif; ?>

        </div>


        <!-- ============================================================
             CUSTOMER DETAILS
        ============================================================= -->

        <div class="receipt-section">

            <h3>
                Customer Information
            </h3>


            <div class="receipt-row">

                <span>
                    Customer Name
                </span>

                <strong>
                    <?= e($booking['name']) ?>
                </strong>

            </div>


            <div class="receipt-row">

                <span>
                    Email
                </span>

                <strong>
                    <?= e($booking['email']) ?>
                </strong>

            </div>

        </div>


        <!-- ============================================================
             BOOKING DETAILS
        ============================================================= -->

        <div class="receipt-section">

            <h3>
                Booking Details
            </h3>


            <div class="receipt-row">

                <span>
                    Workspace
                </span>

                <strong>
                    <?= e($booking['space']) ?>
                </strong>

            </div>


            <div class="receipt-row">

                <span>
                    Booking Type
                </span>

                <strong>

                    <?= ($booking['booking_type'] ?? '') === 'walk-in'
                        ? 'Walk-in'
                        : 'Reservation'
                    ?>

                </strong>

            </div>


            <div class="receipt-row">

                <span>
                    Date
                </span>

                <strong>

                    <?= date(
                        'F j, Y',
                        strtotime($booking['visit_date'])
                    ) ?>

                </strong>

            </div>


            <div class="receipt-row">

                <span>
                    Start Time
                </span>

                <strong>

                    <?= date(
                        'g:i A',
                        strtotime($booking['visit_time'])
                    ) ?>

                </strong>

            </div>


            <div class="receipt-row">

                <span>
                    Duration
                </span>

                <strong>

                    <?= e($formattedHours) ?>

                    hour<?= $hours == 1 ? '' : 's' ?>

                </strong>

            </div>


            <div class="receipt-row">

                <span>
                    Guests
                </span>

                <strong>
                    <?= (int)$booking['guest_count'] ?>
                </strong>

            </div>


            <?php if (!empty($booking['notes'])): ?>

                <div class="receipt-row receipt-notes">

                    <span>
                        Notes
                    </span>

                    <strong>
                        <?= nl2br(
                            e($booking['notes'])
                        ) ?>
                    </strong>

                </div>

            <?php endif; ?>

        </div>


        <!-- ============================================================
             PAYMENT DETAILS
        ============================================================= -->

        <div class="receipt-section">

            <h3>
                Payment Details
            </h3>


            <div class="receipt-row">

                <span>
                    Payment Method
                </span>

                <strong>

                    <?php if (
                        $paymentMethod === 'GCash'
                    ): ?>

                        <i class="fa-solid fa-mobile-screen-button"></i>

                        GCash

                    <?php elseif (
                        $paymentMethod === 'Pay at Counter'
                    ): ?>

                        <i class="fa-solid fa-cash-register"></i>

                        Pay at Counter

                    <?php else: ?>

                        Not Selected

                    <?php endif; ?>

                </strong>

            </div>


            <?php if (
                $paymentMethod === 'GCash'
            ): ?>

                <div class="receipt-row">

                    <span>
                        GCash Reference
                    </span>

                    <strong>

                        <?= $paymentReference !== ''
                            ? e($paymentReference)
                            : '-'
                        ?>

                    </strong>

                </div>

            <?php endif; ?>


            <div class="receipt-row">

                <span>
                    Payment Status
                </span>

                <strong>

                    <?= e($paymentStatus) ?>

                </strong>

            </div>


            <?php if (
                !empty($booking['paid_at'])
            ): ?>

                <div class="receipt-row">

                    <span>
                        Paid At
                    </span>

                    <strong>

                        <?= date(
                            'F j, Y g:i A',
                            strtotime($booking['paid_at'])
                        ) ?>

                    </strong>

                </div>

            <?php endif; ?>

        </div>


        <!-- ============================================================
             TOTAL
        ============================================================= -->

        <div class="receipt-total">

            <div>

                <span>
                    TOTAL AMOUNT
                </span>

                <small>
                    Philippine Peso
                </small>

            </div>


            <strong>

                ₱<?= number_format(
                    $totalAmount,
                    2
                ) ?>

            </strong>

        </div>


        <!-- ============================================================
             PAYMENT MESSAGE
        ============================================================= -->

        <?php if (
            $paymentMethod === 'Pay at Counter'
            && $paymentStatus !== 'Paid'
        ): ?>

            <div class="receipt-message counter-message">

                <div class="message-icon">

                    <i class="fa-solid fa-store"></i>

                </div>


                <div>

                    <strong>
                        PAY AT COUNTER
                    </strong>

                    <p>

                        Please present this booking receipt
                        at the LFT counter and pay

                        <strong>
                            ₱<?= number_format(
                                $totalAmount,
                                2
                            ) ?>
                        </strong>.

                    </p>


                    <p>

                        Your payment status will remain
                        <strong>Unpaid</strong>
                        until LFT staff receives and confirms
                        your payment.

                    </p>

                </div>

            </div>


        <?php elseif (
            $paymentMethod === 'GCash'
            && $paymentStatus === 'Pending Verification'
        ): ?>

            <div class="receipt-message pending-message">

                <div class="message-icon">

                    <i class="fa-solid fa-clock"></i>

                </div>


                <div>

                    <strong>
                        PAYMENT SUBMITTED
                    </strong>

                    <p>

                        Your GCash payment has been submitted.

                    </p>

                    <p>

                        LFT staff will verify your
                        GCash reference number before
                        confirming the payment.

                    </p>

                </div>

            </div>


        <?php elseif (
            $paymentStatus === 'Paid'
        ): ?>

            <div class="receipt-message paid-message">

                <div class="message-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>


                <div>

                    <strong>
                        PAYMENT VERIFIED
                    </strong>

                    <p>

                        Your payment has been successfully
                        verified.

                    </p>

                    <p>

                        Thank you for choosing
                        LFT Dumaguete Curated Workspaces.

                    </p>

                </div>

            </div>


        <?php else: ?>

            <div class="receipt-message unpaid-message">

                <div class="message-icon">

                    <i class="fa-solid fa-circle-exclamation"></i>

                </div>


                <div>

                    <strong>
                        PAYMENT REQUIRED
                    </strong>

                    <p>

                        Your booking has not yet been paid.

                    </p>

                </div>

            </div>

        <?php endif; ?>


        <!-- ============================================================
             RECEIPT FOOTER
        ============================================================= -->

        <div class="receipt-footer">

            <p>
                LFT Dumaguete Curated Workspaces
            </p>

            <p>
                Dumaguete City, Negros Oriental
            </p>


            <div class="receipt-thank-you">

                Thank you for choosing LFT.

            </div>

        </div>


    </div>


    <!-- ================================================================
         ACTION BUTTONS
    ================================================================= -->

    <div class="receipt-actions">


        <button
            type="button"
            class="btn btn-green"
            onclick="window.print()"
        >

            <i class="fa-solid fa-print"></i>

            PRINT RECEIPT

        </button>


        <?php if (
            $paymentStatus === 'Unpaid'
            && $paymentMethod !== 'Pay at Counter'
        ): ?>

            <a
                href="../Payment/index.php?booking=<?= (int)$bookingId ?>"
                class="btn"
            >

                <i class="fa-solid fa-credit-card"></i>

                PAY NOW

            </a>

        <?php endif; ?>


        <a
            href="../Dashboard/index.php"
            class="btn"
        >

            <i class="fa-solid fa-house"></i>

            BACK TO DASHBOARD

        </a>


    </div>


</div>

</section>

</main>


<style>

/* ================================================================
   RECEIPT WRAPPER
================================================================ */

.receipt-wrapper {

    width: 100%;
    max-width: 760px;

    margin: 0 auto;

}


/* ================================================================
   RECEIPT CARD
================================================================ */

.receipt-card {

    width: 100%;

    background: #ffffff;

    border: 1px solid #dddddd;

    border-radius: 18px;

    padding: 35px;

    box-sizing: border-box;

}


/* ================================================================
   HEADER
================================================================ */

.receipt-header {

    text-align: center;

    padding-bottom: 25px;

    border-bottom: 2px dashed #dedede;

}


.receipt-logo h1 {

    margin: 0;

    font-size: 28px;

    letter-spacing: 1px;

}


.receipt-logo img {

    display: block;

    max-width: 140px;

    margin: 0 auto 15px;

}


.receipt-subtitle {

    margin: 5px 0 22px;

    color: #777777;

}


.receipt-title {

    display: inline-block;

    padding: 7px 15px;

    border-radius: 20px;

    background: #f3f6f3;

    font-size: 13px;

    font-weight: 700;

    letter-spacing: 2px;

}


/* ================================================================
   BOOKING NUMBER
================================================================ */

.receipt-booking-number {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    padding: 22px 0;

    border-bottom: 1px solid #eeeeee;

}


.receipt-booking-number span {

    color: #666666;

}


.receipt-booking-number strong {

    font-size: 18px;

}


/* ================================================================
   STATUS
================================================================ */

.receipt-status-area {

    padding: 20px 0 5px;

}


.receipt-status {

    width: 100%;

    text-align: center;

    padding: 13px 15px;

    border-radius: 10px;

    font-weight: 700;

    box-sizing: border-box;

}


.status-paid {

    background: #eaf8ee;

    color: #177239;

}


.status-pending {

    background: #fff6dd;

    color: #8c6200;

}


.status-unpaid {

    background: #fff0f0;

    color: #a53b3b;

}


/* ================================================================
   RECEIPT SECTIONS
================================================================ */

.receipt-section {

    padding: 25px 0;

    border-bottom: 1px solid #eeeeee;

}


.receipt-section h3 {

    margin: 0 0 15px;

    font-size: 14px;

    letter-spacing: 1.2px;

    text-transform: uppercase;

}


/* ================================================================
   RECEIPT ROW
================================================================ */

.receipt-row {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 25px;

    padding: 8px 0;

}


.receipt-row span {

    color: #696969;

}


.receipt-row strong {

    max-width: 60%;

    text-align: right;

    word-break: break-word;

}


.receipt-notes strong {

    line-height: 1.5;

}


/* ================================================================
   TOTAL
================================================================ */

.receipt-total {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 30px 0;

}


.receipt-total span {

    display: block;

    font-size: 16px;

    font-weight: 700;

}


.receipt-total small {

    display: block;

    margin-top: 3px;

    color: #777777;

}


.receipt-total > strong {

    font-size: 34px;

}


/* ================================================================
   MESSAGE BOX
================================================================ */

.receipt-message {

    display: flex;

    align-items: flex-start;

    gap: 15px;

    padding: 18px;

    border-radius: 12px;

    line-height: 1.5;

}


.receipt-message p {

    margin: 7px 0 0;

}


.message-icon {

    flex-shrink: 0;

    width: 42px;

    height: 42px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

}


.counter-message {

    background: #fff8e7;

}


.counter-message .message-icon {

    background: #fff0c3;

}


.pending-message {

    background: #fff8e7;

}


.pending-message .message-icon {

    background: #fff0c3;

}


.paid-message {

    background: #eaf8ee;

}


.paid-message .message-icon {

    background: #d4efdb;

}


.unpaid-message {

    background: #fff0f0;

}


.unpaid-message .message-icon {

    background: #f7dada;

}


/* ================================================================
   FOOTER
================================================================ */

.receipt-footer {

    margin-top: 30px;

    padding-top: 25px;

    border-top: 2px dashed #dedede;

    text-align: center;

    color: #777777;

    font-size: 13px;

}


.receipt-footer p {

    margin: 4px 0;

}


.receipt-thank-you {

    margin-top: 20px;

    font-weight: 700;

    color: #333333;

}


/* ================================================================
   BUTTONS
================================================================ */

.receipt-actions {

    display: flex;

    align-items: center;

    justify-content: center;

    flex-wrap: wrap;

    gap: 12px;

    margin-top: 20px;

}


/* ================================================================
   MOBILE
================================================================ */

@media (max-width: 650px) {

    .receipt-card {

        padding: 22px;

    }


    .receipt-booking-number {

        flex-direction: column;

        align-items: flex-start;

        gap: 5px;

    }


    .receipt-row {

        flex-direction: column;

        gap: 3px;

    }


    .receipt-row strong {

        max-width: 100%;

        text-align: left;

    }


    .receipt-total {

        flex-direction: column;

        align-items: flex-start;

        gap: 8px;

    }


    .receipt-total > strong {

        font-size: 30px;

    }


    .receipt-message {

        flex-direction: column;

    }


    .receipt-actions {

        flex-direction: column;

    }


    .receipt-actions .btn {

        width: 100%;

        text-align: center;

    }

}


/* ================================================================
   PRINT
================================================================ */

@media print {

    body * {

        visibility: hidden;

    }


    #receipt,
    #receipt * {

        visibility: visible;

    }


    #receipt {

        position: absolute;

        left: 0;

        top: 0;

        width: 100%;

        max-width: none;

        border: none;

        border-radius: 0;

        padding: 25px;

    }


    .site-header,
    .site-footer,
    .receipt-actions {

        display: none !important;

    }


    .auth-section {

        padding: 0 !important;

        margin: 0 !important;

    }

}

</style>


<?php

require '../Includes/footer.php';

?>
```
