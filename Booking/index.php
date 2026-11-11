
<?php

require '../Includes/db.php';

$user = requireUser();
$connection = db();

$errors = [];

/*
|--------------------------------------------------------------------------
| BOOKING TYPE
|--------------------------------------------------------------------------
| booking = advance reservation
| walk-in = customer is already at LFT
|--------------------------------------------------------------------------
*/

$bookingType = ($_GET['type'] ?? 'booking') === 'walk-in'
    ? 'walk-in'
    : 'booking';


/*
|--------------------------------------------------------------------------
| GET ACTIVE SPACES
|--------------------------------------------------------------------------
| IMPORTANT:
| spaces table must contain:
|
| name
| seats
| hourly_rate
| active
|--------------------------------------------------------------------------
*/

$spaces = $connection->query(
    'SELECT name, seats, hourly_rate
     FROM spaces
     WHERE active = 1
     ORDER BY name'
)->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| CREATE SPACE ARRAYS
|--------------------------------------------------------------------------
*/

$spaceCapacity = [];
$spaceRates = [];

foreach ($spaces as $row) {

    $spaceCapacity[$row['name']] = (int)$row['seats'];

    $spaceRates[$row['name']] = (float)$row['hourly_rate'];
}

$availableSpaces = array_keys($spaceCapacity);


/*
|--------------------------------------------------------------------------
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$requestedSpace = trim($_GET['space'] ?? '');

$space = in_array($requestedSpace, $availableSpaces, true)
    ? $requestedSpace
    : '';

$visitDate = $bookingType === 'walk-in'
    ? appToday()
    : '';

$visitTime = $bookingType === 'walk-in'
    ? appNow()->format('H:i')
    : '';

$durationMinutes = 60;

$guestCount = 1;

$notes = '';

$totalAmount = 0;


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrf();


    /*
    |--------------------------------------------------------------------------
    | GET FORM VALUES
    |--------------------------------------------------------------------------
    */

    $bookingType = ($_POST['booking_type'] ?? 'booking') === 'walk-in'
        ? 'walk-in'
        : 'booking';

    $space = trim($_POST['space'] ?? '');

    $durationMinutes = max(
        30,
        min(
            480,
            (int)($_POST['duration_minutes'] ?? 60)
        )
    );

    $guestCount = max(
        1,
        min(
            50,
            (int)($_POST['guest_count'] ?? 1)
        )
    );

    $notes = trim($_POST['notes'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | DATE AND TIME
    |--------------------------------------------------------------------------
    */

    if ($bookingType === 'walk-in') {

        $visitDate = appToday();

        $visitTime = appNow()->format('H:i');

    } else {

        $visitDate = trim($_POST['visit_date'] ?? '');

        $visitTime = trim($_POST['visit_time'] ?? '');
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE REQUIRED FIELDS
    |--------------------------------------------------------------------------
    */

    if (
        $space === '' ||
        $visitDate === '' ||
        $visitTime === ''
    ) {

        $errors[] =
            'Please complete the space, date, and time fields.';
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE SPACE
    |--------------------------------------------------------------------------
    */

    if (
        $space !== '' &&
        !isset($spaceCapacity[$space])
    ) {

        $errors[] =
            'Please choose an available space.';
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE GUEST CAPACITY
    |--------------------------------------------------------------------------
    */

    if (
        $space !== '' &&
        isset($spaceCapacity[$space]) &&
        $guestCount > $spaceCapacity[$space]
    ) {

        $errors[] =
            'This space supports up to '
            . $spaceCapacity[$space]
            . ' guest(s).';
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE DATE
    |--------------------------------------------------------------------------
    */

    $dateObject = DateTime::createFromFormat(
        'Y-m-d',
        $visitDate
    );

    if (
        $visitDate !== '' &&
        (
            !$dateObject ||
            $dateObject->format('Y-m-d') !== $visitDate
        )
    ) {

        $errors[] =
            'Please choose a valid date.';

    } elseif (
        $visitDate !== '' &&
        $visitDate < appToday()
    ) {

        $errors[] =
            'Choose today or a future date.';
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE TIME
    |--------------------------------------------------------------------------
    */

    $timeObject = DateTime::createFromFormat(
        'H:i',
        $visitTime
    );

    if (
        $visitTime !== '' &&
        (
            !$timeObject ||
            $timeObject->format('H:i') !== $visitTime
        )
    ) {

        $errors[] =
            'Please choose a valid time.';
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK BOOKING CONFLICT
    |--------------------------------------------------------------------------
    */

    if (
        !$errors &&
        bookingHasConflict(
            $connection,
            $space,
            $space,
            $visitDate,
            $visitTime,
            $durationMinutes
        )
    ) {

        $errors[] =
            'That space is already reserved during the selected time. '
            . 'Please choose another time or space.';
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE PRICE
    |--------------------------------------------------------------------------
    |
    | Example:
    |
    | Hourly rate = ₱450
    | Duration = 120 minutes
    |
    | 120 / 60 = 2 hours
    |
    | ₱450 × 2 = ₱900
    |
    |--------------------------------------------------------------------------
    */

    if (
        !$errors &&
        isset($spaceRates[$space])
    ) {

        $hourlyRate = $spaceRates[$space];

        $hours = $durationMinutes / 60;

        $totalAmount = round(
            $hourlyRate * $hours,
            2
        );


        /*
        |--------------------------------------------------------------------------
        | MAKE SURE PRICE IS VALID
        |--------------------------------------------------------------------------
        */

        if ($hourlyRate <= 0) {

            $errors[] =
                'This workspace does not have a valid price. '
                . 'Please contact LFT staff.';
        }

        if ($totalAmount <= 0) {

            $errors[] =
                'Unable to calculate the booking total.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE BOOKING
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        $now = appNow()->format(
            'Y-m-d H:i:s'
        );


        /*
        |--------------------------------------------------------------------------
        | WALK-IN BOOKING
        |--------------------------------------------------------------------------
        |
        | IMPORTANT:
        |
        | Do NOT immediately set walk-ins to Checked in.
        |
        | They need to pay first.
        |--------------------------------------------------------------------------
        */

        if ($bookingType === 'walk-in') {

            $statement = $connection->prepare(
                'INSERT INTO bookings (
                    user_id,
                    booking_type,
                    space,
                    space_id,
                    visit_date,
                    visit_time,
                    duration_minutes,
                    guest_count,
                    notes,
                    total_amount,
                    payment_status,
                    status,
                    updated_at
                )
                VALUES (
                    ?,
                    "walk-in",
                    ?,
                    (SELECT id FROM spaces WHERE name = ? LIMIT 1),
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    "Unpaid",
                    "Pending",
                    ?
                )'
            );


            $statement->execute([
                $user['id'],
                $space,
                $space,
                $visitDate,
                $visitTime,
                $durationMinutes,
                $guestCount,
                $notes,
                $totalAmount,
                $now
            ]);


            $bookingId =
                (int)$connection->lastInsertId();


            /*
            |--------------------------------------------------------------------------
            | SEND CUSTOMER TO PAYMENT
            |--------------------------------------------------------------------------
            */

            header(
                'Location: ../Payment/index.php?booking='
                . $bookingId
            );

            exit;
        }


        /*
        |--------------------------------------------------------------------------
        | NORMAL ADVANCE BOOKING
        |--------------------------------------------------------------------------
        */

        $statement = $connection->prepare(
            'INSERT INTO bookings (
                user_id,
                booking_type,
                space,
                space_id,
                visit_date,
                visit_time,
                duration_minutes,
                guest_count,
                notes,
                total_amount,
                payment_status,
                status,
                updated_at
            )
            VALUES (
                ?,
                "booking",
                ?,
                (SELECT id FROM spaces WHERE name = ? LIMIT 1),
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                "Unpaid",
                "Pending",
                ?
            )'
        );


        $statement->execute([
            $user['id'],
            $space,
            $visitDate,
            $visitTime,
            $durationMinutes,
            $guestCount,
            $notes,
            $totalAmount,
            $now
        ]);


        $bookingId =
            (int)$connection->lastInsertId();


        /*
        |--------------------------------------------------------------------------
        | REDIRECT TO PAYMENT PAGE
        |--------------------------------------------------------------------------
        */

        header(
            'Location: ../Payment/index.php?booking='
            . $bookingId
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle = $bookingType === 'walk-in'
    ? 'Walk-in | LFT Dumaguete'
    : 'Book a Space | LFT Dumaguete';

$currentPage = 'BOOKING';


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require '../Includes/header.php';

?>


<main>

<section class="auth-section">

<div class="booking-card">


    <!-- ================================================================
         BOOKING HEADER
    ================================================================= -->

    <div>

        <p class="section-label">

            <?= $bookingType === 'walk-in'
                ? 'WALK-IN'
                : 'RESERVE YOUR SPACE'
            ?>

        </p>


        <h1>

            <?= $bookingType === 'walk-in'
                ? 'Choose your workspace.'
                : 'Plan your next workday.'
            ?>

        </h1>


        <p>

            Hi <?= e($user['name']) ?>.

            <?php if ($bookingType === 'walk-in'): ?>

                Choose your workspace and duration.
                Your payment total will be calculated before check-in.

            <?php else: ?>

                Choose your workspace, date, time, and duration.
                Your booking price will automatically be calculated.

            <?php endif; ?>

        </p>


        <p class="booking-links">

            <a href="?type=booking">
                Book a space
            </a>

            <span>/</span>

            <a href="?type=walk-in">
                Walk-in
            </a>

        </p>

    </div>


    <!-- ================================================================
         ERRORS
    ================================================================= -->

    <?php foreach ($errors as $error): ?>

        <div class="form-error">

            <?= e($error) ?>

        </div>

    <?php endforeach; ?>


    <!-- ================================================================
         NO AVAILABLE SPACES
    ================================================================= -->

    <?php if (!$availableSpaces): ?>

        <div class="form-error">

            No spaces are currently available.
            Please contact the LFT team.

        </div>


    <?php else: ?>


    <!-- ================================================================
         BOOKING FORM
    ================================================================= -->

    <form
        class="tour-form"
        method="post"
        id="bookingForm"
    >


        <?= csrfField() ?>


        <input
            type="hidden"
            name="booking_type"
            value="<?= e($bookingType) ?>"
        >


        <!-- ============================================================
             SPACE
        ============================================================= -->

        <label for="space">
            Workspace
        </label>


        <select
            id="space"
            name="space"
            required
        >

            <option
                value=""
                data-rate="0"
            >
                Choose a workspace
            </option>


            <?php foreach ($spaces as $spaceRow): ?>

                <option
                    value="<?= e($spaceRow['name']) ?>"
                    data-rate="<?= number_format(
                        (float)$spaceRow['hourly_rate'],
                        2,
                        '.',
                        ''
                    ) ?>"
                    <?= $space === $spaceRow['name']
                        ? 'selected'
                        : ''
                    ?>
                >

                    <?= e($spaceRow['name']) ?>

                    —
                    ₱<?= number_format(
                        (float)$spaceRow['hourly_rate'],
                        2
                    ) ?>/hour

                    (up to
                    <?= (int)$spaceRow['seats'] ?>
                    guests)

                </option>

            <?php endforeach; ?>

        </select>


        <!-- ============================================================
             DATE AND TIME
        ============================================================= -->

        <?php if ($bookingType === 'booking'): ?>


            <label for="visit_date">
                Date
            </label>


            <input
                id="visit_date"
                name="visit_date"
                type="date"
                min="<?= e(appToday()) ?>"
                value="<?= e($visitDate) ?>"
                required
            >


            <label for="visit_time">
                Start time
            </label>


            <input
                id="visit_time"
                name="visit_time"
                type="time"
                value="<?= e($visitTime) ?>"
                required
            >


        <?php else: ?>


            <div class="success-message">

                <i class="fa-solid fa-location-dot"></i>

                Walk-in uses the current
                Dumaguete date and time.

            </div>


        <?php endif; ?>


        <!-- ============================================================
             DURATION
        ============================================================= -->

        <label for="duration_minutes">
            Duration
        </label>


        <select
            id="duration_minutes"
            name="duration_minutes"
            required
        >

            <option
                value="60"
                <?= $durationMinutes === 60
                    ? 'selected'
                    : ''
                ?>
            >
                1 hour
            </option>


            <option
                value="120"
                <?= $durationMinutes === 120
                    ? 'selected'
                    : ''
                ?>
            >
                2 hours
            </option>


            <option
                value="180"
                <?= $durationMinutes === 180
                    ? 'selected'
                    : ''
                ?>
            >
                3 hours
            </option>


            <option
                value="240"
                <?= $durationMinutes === 240
                    ? 'selected'
                    : ''
                ?>
            >
                4 hours
            </option>


            <option
                value="480"
                <?= $durationMinutes === 480
                    ? 'selected'
                    : ''
                ?>
            >
                8 hours
            </option>

        </select>


        <!-- ============================================================
             LIVE PRICE
        ============================================================= -->

        <div class="booking-price-box">

            <div class="booking-price-header">

                <span>
                    Booking Total
                </span>

                <strong id="bookingTotal">
                    ₱0.00
                </strong>

            </div>


            <p id="priceDetails">

                Choose a workspace to calculate
                your payment.

            </p>

        </div>


        <!-- ============================================================
             GUESTS
        ============================================================= -->

        <label for="guest_count">

            Guests

        </label>


        <input
            id="guest_count"
            name="guest_count"
            type="number"
            min="1"
            max="50"
            value="<?= (int)$guestCount ?>"
            required
        >


        <!-- ============================================================
             NOTES
        ============================================================= -->

        <label for="notes">

            Notes

            <span>
                (optional)
            </span>

        </label>


        <textarea
            id="notes"
            name="notes"
            rows="4"
            maxlength="1000"
            placeholder="Equipment needs or anything our team should know."
        ><?= e($notes) ?></textarea>


        <!-- ============================================================
             BUTTON
        ============================================================= -->

        <button
            class="btn btn-green"
            type="submit"
        >

            <i class="fa-solid fa-qrcode"></i>

            PROCEED TO PAYMENT

        </button>


        <a
            class="text-link"
            href="../Dashboard/index.php"
        >

            Back to my dashboard

        </a>


    </form>


    <?php endif; ?>


</div>

</section>

</main>


<!-- ==================================================================
     LIVE PRICE CALCULATION
=================================================================== -->

<script>

document.addEventListener('DOMContentLoaded', function () {

    const spaceSelect =
        document.getElementById('space');

    const durationSelect =
        document.getElementById('duration_minutes');

    const bookingTotal =
        document.getElementById('bookingTotal');

    const priceDetails =
        document.getElementById('priceDetails');


    /*
    |--------------------------------------------------------------------------
    | FORMAT PHILIPPINE PESO
    |--------------------------------------------------------------------------
    */

    function formatPeso(amount) {

        return '₱' + amount.toLocaleString(
            'en-PH',
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE PRICE
    |--------------------------------------------------------------------------
    */

    function calculateBookingTotal() {

        if (
            !spaceSelect ||
            !durationSelect
        ) {
            return;
        }


        const selectedOption =
            spaceSelect.options[
                spaceSelect.selectedIndex
            ];


        const hourlyRate = parseFloat(
            selectedOption.dataset.rate || 0
        );


        const durationMinutes = parseInt(
            durationSelect.value || 0,
            10
        );


        /*
        |--------------------------------------------------------------------------
        | NO SPACE SELECTED
        |--------------------------------------------------------------------------
        */

        if (
            hourlyRate <= 0 ||
            durationMinutes <= 0
        ) {

            bookingTotal.textContent =
                '₱0.00';

            priceDetails.textContent =
                'Choose a workspace to calculate your payment.';

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | COMPUTE HOURS
        |--------------------------------------------------------------------------
        */

        const hours =
            durationMinutes / 60;


        /*
        |--------------------------------------------------------------------------
        | COMPUTE TOTAL
        |--------------------------------------------------------------------------
        */

        const total =
            hourlyRate * hours;


        /*
        |--------------------------------------------------------------------------
        | DISPLAY TOTAL
        |--------------------------------------------------------------------------
        */

        bookingTotal.textContent =
            formatPeso(total);


        /*
        |--------------------------------------------------------------------------
        | DISPLAY PRICE DETAILS
        |--------------------------------------------------------------------------
        */

        const hourText =
            hours === 1
                ? 'hour'
                : 'hours';


        priceDetails.textContent =
            formatPeso(hourlyRate)
            + '/hour × '
            + hours
            + ' '
            + hourText;

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE WHEN SPACE CHANGES
    |--------------------------------------------------------------------------
    */

    spaceSelect.addEventListener(
        'change',
        calculateBookingTotal
    );


    /*
    |--------------------------------------------------------------------------
    | UPDATE WHEN DURATION CHANGES
    |--------------------------------------------------------------------------
    */

    durationSelect.addEventListener(
        'change',
        calculateBookingTotal
    );


    /*
    |--------------------------------------------------------------------------
    | CALCULATE ON PAGE LOAD
    |--------------------------------------------------------------------------
    */

    calculateBookingTotal();

});

</script>


<?php

require '../Includes/footer.php';

?>
```
