<?php

require '../Includes/db.php';

$user = requireUser();
$connection = db();
$errors = [];

$bookingId = (int)($_GET['booking'] ?? $_POST['booking_id'] ?? 0);

if ($bookingId <= 0) {
    die('Invalid booking.');
}

/*
|--------------------------------------------------------------------------
| GET BOOKING
|--------------------------------------------------------------------------
*/

$statement = $connection->prepare(
    'SELECT *
     FROM bookings
     WHERE id = ?
     AND user_id = ?
     LIMIT 1'
);

$statement->execute([
    $bookingId,
    $user['id']
]);

$booking = $statement->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    die('Booking not found.');
}

/*
|--------------------------------------------------------------------------
| PROCESS PAYMENT OPTION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrf();

    $paymentMethod = trim($_POST['payment_method'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | GCASH
    |--------------------------------------------------------------------------
    */

    if ($paymentMethod === 'GCash') {

        $paymentReference = trim(
            $_POST['payment_reference'] ?? ''
        );

        if ($paymentReference === '') {

            $errors[] = 'Please enter your GCash reference number.';

        } elseif (strlen($paymentReference) < 6) {

            $errors[] = 'Please enter a valid GCash reference number.';

        } elseif (strlen($paymentReference) > 100) {

            $errors[] = 'Payment reference is too long.';
        }

        if (!$errors) {

            $now = appNow()->format('Y-m-d H:i:s');

            $update = $connection->prepare(
                'UPDATE bookings
                 SET
                    payment_method = "GCash",
                    payment_reference = ?,
                    payment_status = "Pending Verification",
                    updated_at = ?
                 WHERE id = ?
                 AND user_id = ?'
            );

            $update->execute([
                $paymentReference,
                $now,
                $bookingId,
                $user['id']
            ]);

            header(
                'Location: ../Receipt/index.php?booking='
                . $bookingId
            );

            exit;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | PAY AT COUNTER
    |--------------------------------------------------------------------------
    */

    elseif ($paymentMethod === 'Counter') {

        $now = appNow()->format('Y-m-d H:i:s');

        $update = $connection->prepare(
            'UPDATE bookings
             SET
                payment_method = "Pay at Counter",
                payment_reference = NULL,
                payment_status = "Unpaid",
                updated_at = ?
             WHERE id = ?
             AND user_id = ?'
        );

        $update->execute([
            $now,
            $bookingId,
            $user['id']
        ]);

        header(
            'Location: ../Receipt/index.php?booking='
            . $bookingId
            . '&counter=1'
        );

        exit;
    }

    else {

        $errors[] = 'Please choose a payment method.';
    }
}


$pageTitle = 'Payment | LFT Dumaguete';
$currentPage = 'PAYMENT';

require '../Includes/header.php';

?>

<main>

<section class="auth-section">

<div class="booking-card payment-card">

    <p class="section-label">
        PAYMENT
    </p>

    <h1>
        Choose how you want to pay
    </h1>

    <p>
        Hi <?= e($user['name']) ?>.
        Review your booking and choose your payment method.
    </p>


    <?php foreach ($errors as $error): ?>

        <div class="form-error">
            <?= e($error) ?>
        </div>

    <?php endforeach; ?>


    <!-- ==============================================================
         BOOKING SUMMARY
    ============================================================== -->

    <div class="payment-booking-summary">

        <div class="summary-row">

            <span>Booking #</span>

            <strong>
                <?= str_pad(
                    (string)$booking['id'],
                    6,
                    '0',
                    STR_PAD_LEFT
                ) ?>
            </strong>

        </div>


        <div class="summary-row">

            <span>Workspace</span>

            <strong>
                <?= e($booking['space']) ?>
            </strong>

        </div>


        <div class="summary-row">

            <span>Date</span>

            <strong>
                <?= date(
                    'F j, Y',
                    strtotime($booking['visit_date'])
                ) ?>
            </strong>

        </div>


        <div class="summary-row">

            <span>Time</span>

            <strong>
                <?= date(
                    'g:i A',
                    strtotime($booking['visit_time'])
                ) ?>
            </strong>

        </div>


        <div class="summary-row">

            <span>Duration</span>

            <strong>

                <?php
                $hours =
                    (int)$booking['duration_minutes'] / 60;
                ?>

                <?= rtrim(
                    rtrim(
                        number_format($hours, 1),
                        '0'
                    ),
                    '.'
                ) ?>

                hour<?= $hours == 1 ? '' : 's' ?>

            </strong>

        </div>


        <div class="summary-row">

            <span>Guests</span>

            <strong>
                <?= (int)$booking['guest_count'] ?>
            </strong>

        </div>

    </div>


    <!-- ==============================================================
         AMOUNT
    ============================================================== -->

    <div class="payment-total-box">

        <span>
            TOTAL AMOUNT
        </span>

        <strong>
            ₱<?= number_format(
                (float)$booking['total_amount'],
                2
            ) ?>
        </strong>

    </div>


    <!-- ==============================================================
         PAYMENT METHOD
    ============================================================== -->

    <div class="payment-method-title">

        <h2>
            Payment Method
        </h2>

        <p>
            Select how you would like to pay.
        </p>

    </div>


    <div class="payment-method-grid">


        <!-- GCASH -->

        <button
            type="button"
            class="payment-method-card"
            id="gcashOption"
        >

            <div class="payment-icon">
                <i class="fa-solid fa-qrcode"></i>
            </div>

            <div>

                <strong>
                    GCash
                </strong>

                <p>
                    Scan our QR code and pay online.
                </p>

            </div>

        </button>


        <!-- COUNTER -->

        <button
            type="button"
            class="payment-method-card"
            id="counterOption"
        >

            <div class="payment-icon">
                <i class="fa-solid fa-cash-register"></i>
            </div>

            <div>

                <strong>
                    Pay at Counter
                </strong>

                <p>
                    Pay when you arrive at LFT.
                </p>

            </div>

        </button>

    </div>


    <!-- ==============================================================
         GCASH PAYMENT
    ============================================================== -->

    <div
        id="gcashSection"
        class="payment-section"
        style="display:none;"
    >

        <div class="payment-section-header">

            <h2>
                Pay with GCash
            </h2>

            <p>
                Scan the QR code and pay the exact amount.
            </p>

        </div>


        <div class="gcash-qr-container">

            <img
                src="../assets/images/gcash-qr.jpg"
                alt="LFT Dumaguete GCash QR"
                class="gcash-qr-image"
            >

        </div>


        <div class="qr-amount">

            Amount to send

            <strong>

                ₱<?= number_format(
                    (float)$booking['total_amount'],
                    2
                ) ?>

            </strong>

        </div>


        <form
            method="post"
            class="tour-form"
        >

            <?= csrfField() ?>

            <input
                type="hidden"
                name="booking_id"
                value="<?= (int)$bookingId ?>"
            >

            <input
                type="hidden"
                name="payment_method"
                value="GCash"
            >


            <label for="payment_reference">

                GCash Reference Number

            </label>


            <input
                id="payment_reference"
                name="payment_reference"
                type="text"
                maxlength="100"
                placeholder="Enter GCash reference number"
                required
            >


            <small class="payment-help">

                Enter the reference number shown
                in your GCash transaction receipt.

            </small>


            <button
                type="submit"
                class="btn btn-green"
            >

                <i class="fa-solid fa-receipt"></i>

                SUBMIT PAYMENT

            </button>

        </form>

    </div>


    <!-- ==============================================================
         PAY AT COUNTER
    ============================================================== -->

    <div
        id="counterSection"
        class="payment-section counter-section"
        style="display:none;"
    >

        <div class="counter-icon">

            <i class="fa-solid fa-cash-register"></i>

        </div>


        <h2>
            Pay at Counter
        </h2>


        <p>

            You can pay

            <strong>
                ₱<?= number_format(
                    (float)$booking['total_amount'],
                    2
                ) ?>
            </strong>

            directly at the LFT counter.

        </p>


        <div class="counter-notice">

            <i class="fa-solid fa-circle-info"></i>

            Your booking will remain
            <strong>Unpaid</strong>
            until LFT staff receives and confirms
            your payment at the counter.

        </div>


        <form method="post">

            <?= csrfField() ?>

            <input
                type="hidden"
                name="booking_id"
                value="<?= (int)$bookingId ?>"
            >

            <input
                type="hidden"
                name="payment_method"
                value="Counter"
            >


            <button
                type="submit"
                class="btn btn-green"
            >

                <i class="fa-solid fa-store"></i>

                CONFIRM PAY AT COUNTER

            </button>

        </form>

    </div>


    <a
        href="../Dashboard/index.php"
        class="text-link"
    >
        Pay later
    </a>


</div>

</section>

</main>


<style>

/* ================================================================
   PAYMENT PAGE
================================================================ */

.payment-card {
    max-width: 760px;
    margin: auto;
}

.payment-booking-summary {
    margin: 25px 0;
    padding: 20px;
    border: 1px solid #e5e5e5;
    border-radius: 14px;
    background: #ffffff;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    padding: 10px 0;
    border-bottom: 1px solid #eeeeee;
}

.summary-row:last-child {
    border-bottom: none;
}

.summary-row span {
    color: #666;
}

.summary-row strong {
    text-align: right;
}


/* TOTAL */

.payment-total-box {
    padding: 25px;
    text-align: center;
    border-radius: 14px;
    background: #f4f8f4;
    margin-bottom: 30px;
}

.payment-total-box span {
    display: block;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 1px;
}

.payment-total-box strong {
    display: block;
    margin-top: 5px;
    font-size: 38px;
}


/* PAYMENT METHODS */

.payment-method-title {
    margin-bottom: 15px;
}

.payment-method-title h2 {
    margin-bottom: 5px;
}

.payment-method-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.payment-method-card {
    width: 100%;
    border: 2px solid #e4e4e4;
    background: white;
    padding: 20px;
    border-radius: 14px;
    cursor: pointer;
    text-align: left;

    display: flex;
    align-items: center;
    gap: 15px;

    transition: 0.2s;
}

.payment-method-card:hover {
    border-color: #28643c;
}

.payment-method-card.active {
    border-color: #28643c;
    background: #f4f8f4;
}

.payment-method-card strong {
    display: block;
    font-size: 17px;
}

.payment-method-card p {
    margin: 4px 0 0;
    color: #666;
    font-size: 14px;
}

.payment-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: #f3f3f3;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 20px;
}


/* PAYMENT SECTION */

.payment-section {
    margin: 25px 0;
    padding: 25px;
    border: 1px solid #e4e4e4;
    border-radius: 14px;
}

.payment-section-header {
    text-align: center;
}


/* GCASH */

.gcash-qr-container {
    width: 100%;
    max-width: 300px;
    margin: 20px auto;
    padding: 15px;

    background: white;
    border: 1px solid #ddd;
    border-radius: 15px;
}

.gcash-qr-image {
    display: block;
    width: 100%;
    max-width: 270px;
    height: auto;
    margin: auto;
}

.qr-amount {
    text-align: center;
    margin: 20px 0;
}

.qr-amount strong {
    display: block;
    margin-top: 5px;
    font-size: 28px;
}

.payment-help {
    display: block;
    margin-bottom: 15px;
    color: #777;
}


/* COUNTER */

.counter-section {
    text-align: center;
}

.counter-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;

    display: flex;
    align-items: center;
    justify-content: center;

    margin: 0 auto 15px;

    background: #f4f8f4;
    font-size: 28px;
}

.counter-section h2 {
    margin-bottom: 10px;
}

.counter-notice {
    padding: 15px;
    margin: 20px 0;

    background: #fff7e5;

    border-radius: 10px;

    line-height: 1.5;
}


/* MOBILE */

@media (max-width: 600px) {

    .payment-method-grid {
        grid-template-columns: 1fr;
    }

    .summary-row {
        flex-direction: column;
        gap: 3px;
    }

    .summary-row strong {
        text-align: left;
    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const gcashOption =
            document.getElementById('gcashOption');

        const counterOption =
            document.getElementById('counterOption');

        const gcashSection =
            document.getElementById('gcashSection');

        const counterSection =
            document.getElementById('counterSection');


        /*
        |--------------------------------------------------------------------------
        | GCASH
        |--------------------------------------------------------------------------
        */

        gcashOption.addEventListener(
            'click',
            function () {

                gcashSection.style.display =
                    'block';

                counterSection.style.display =
                    'none';

                gcashOption.classList.add(
                    'active'
                );

                counterOption.classList.remove(
                    'active'
                );

                gcashSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

            }
        );


        /*
        |--------------------------------------------------------------------------
        | PAY AT COUNTER
        |--------------------------------------------------------------------------
        */

        counterOption.addEventListener(
            'click',
            function () {

                counterSection.style.display =
                    'block';

                gcashSection.style.display =
                    'none';

                counterOption.classList.add(
                    'active'
                );

                gcashOption.classList.remove(
                    'active'
                );

                counterSection.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });

            }
        );

    }
);

</script>


<?php require '../Includes/footer.php'; ?>