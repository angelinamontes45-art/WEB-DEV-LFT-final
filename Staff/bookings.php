<?php

/*
|--------------------------------------------------------------------------
| DEVELOPMENT ERROR DISPLAY
|--------------------------------------------------------------------------
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


/*
|--------------------------------------------------------------------------
| STAFF AUTH
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/_guard.php';


/*
|--------------------------------------------------------------------------
| PAGE SETTINGS
|--------------------------------------------------------------------------
*/

$currentStaffPage = 'bookings';

$pageTitle = 'Bookings | LFT Staff';


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

$connection = db();


/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

$statusFilter = trim($_GET['status'] ?? '');

$paymentFilter = trim($_GET['payment'] ?? '');

$search = trim($_GET['search'] ?? '');


/*
|--------------------------------------------------------------------------
| ALLOWED FILTER VALUES
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    '',
    'Pending',
    'Confirmed',
    'Checked in',
    'Completed',
    'Cancelled'
];

$allowedPaymentStatuses = [
    '',
    'Unpaid',
    'Pending Verification',
    'Paid',
    'Rejected'
];


if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = '';
}

if (!in_array($paymentFilter, $allowedPaymentStatuses, true)) {
    $paymentFilter = '';
}


/*
|--------------------------------------------------------------------------
| BUILD QUERY
|--------------------------------------------------------------------------
*/

$sql = '
    SELECT
        bookings.*,
        users.name AS customer_name,
        users.email AS customer_email
    FROM bookings
    JOIN users
        ON users.id = bookings.user_id
    WHERE 1 = 1
';

$params = [];


/*
|--------------------------------------------------------------------------
| BOOKING STATUS FILTER
|--------------------------------------------------------------------------
*/

if ($statusFilter !== '') {

    $sql .= '
        AND bookings.status = ?
    ';

    $params[] = $statusFilter;
}


/*
|--------------------------------------------------------------------------
| PAYMENT STATUS FILTER
|--------------------------------------------------------------------------
*/

if ($paymentFilter !== '') {

    $sql .= '
        AND bookings.payment_status = ?
    ';

    $params[] = $paymentFilter;
}


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $sql .= '
        AND (
            users.name LIKE ?
            OR users.email LIKE ?
            OR bookings.space LIKE ?
            OR CAST(bookings.id AS CHAR) LIKE ?
        )
    ';

    $searchValue = '%' . $search . '%';

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
}


/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$sql .= '
    ORDER BY
        bookings.visit_date DESC,
        bookings.visit_time DESC,
        bookings.id DESC
';


/*
|--------------------------------------------------------------------------
| EXECUTE
|--------------------------------------------------------------------------
*/

$statement = $connection->prepare($sql);

$statement->execute($params);

$bookings = $statement->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| COUNTS
|--------------------------------------------------------------------------
*/

$totalBookings = (int) $connection
    ->query(
        'SELECT COUNT(*)
         FROM bookings'
    )
    ->fetchColumn();


$pendingBookings = (int) $connection
    ->query(
        'SELECT COUNT(*)
         FROM bookings
         WHERE status = "Pending"'
    )
    ->fetchColumn();


$confirmedBookings = (int) $connection
    ->query(
        'SELECT COUNT(*)
         FROM bookings
         WHERE status = "Confirmed"'
    )
    ->fetchColumn();


$today = appToday();

$todayStatement = $connection->prepare(
    'SELECT COUNT(*)
     FROM bookings
     WHERE visit_date = ?'
);

$todayStatement->execute([$today]);

$todayBookings = (int) $todayStatement->fetchColumn();


/*
|--------------------------------------------------------------------------
| STAFF DETAILS
|--------------------------------------------------------------------------
*/

$staffName = $staff['name'] ?? 'Staff';

$staffInitial = strtoupper(
    substr(
        trim($staffName),
        0,
        1
    )
);


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/header.php';

?>

<?php require_once __DIR__ . '/sidebar.php'; ?>


<div class="staff-main">

    <!-- ============================================================
         TOP HEADER
    ============================================================= -->

    <header class="staff-top-header">

        <div class="staff-header-left">

            <button
                type="button"
                class="staff-menu-button"
                id="staffMenuButton"
                aria-label="Open sidebar"
            >
                <i class="fa-solid fa-bars"></i>
            </button>

            <div>

                <h1>
                    Bookings
                </h1>

                <p>
                    Manage customer workspace reservations.
                </p>

            </div>

        </div>


        <div class="staff-user">

            <div class="staff-avatar">

                <?= e($staffInitial) ?>

            </div>

            <div class="staff-user-details">

                <strong>
                    <?= e($staffName) ?>
                </strong>

                <span>
                    LFT Staff
                </span>

            </div>

        </div>

    </header>


    <!-- ============================================================
         CONTENT
    ============================================================= -->

    <main class="staff-content">


        <!-- ========================================================
             PAGE HEADER
        ========================================================= -->

        <div class="bookings-heading">

            <div>

                <h2>
                    Booking Management
                </h2>

                <p>
                    View booking details, payment status and customer reservations.
                </p>

            </div>

        </div>


        <!-- ========================================================
             STATS
        ========================================================= -->

        <div class="staff-stats-grid">

            <div class="staff-stat-card">

                <div class="staff-stat-card-top">

                    <div>

                        <p>
                            Total Bookings
                        </p>

                        <h2>
                            <?= $totalBookings ?>
                        </h2>

                    </div>

                    <div class="staff-stat-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>

                </div>

            </div>


            <div class="staff-stat-card">

                <div class="staff-stat-card-top">

                    <div>

                        <p>
                            Today's Bookings
                        </p>

                        <h2>
                            <?= $todayBookings ?>
                        </h2>

                    </div>

                    <div class="staff-stat-icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>

                </div>

            </div>


            <div class="staff-stat-card">

                <div class="staff-stat-card-top">

                    <div>

                        <p>
                            Pending
                        </p>

                        <h2>
                            <?= $pendingBookings ?>
                        </h2>

                    </div>

                    <div class="staff-stat-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>

                </div>

            </div>


            <div class="staff-stat-card">

                <div class="staff-stat-card-top">

                    <div>

                        <p>
                            Confirmed
                        </p>

                        <h2>
                            <?= $confirmedBookings ?>
                        </h2>

                    </div>

                    <div class="staff-stat-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>

                </div>

            </div>

        </div>


        <!-- ========================================================
             FILTERS
        ========================================================= -->

        <div class="staff-panel booking-filter-panel">

            <form
                method="get"
                action="bookings.php"
                class="booking-filter-form"
            >

                <div class="booking-search">

                    <i class="fa-solid fa-magnifying-glass"></i>

                    <input
                        type="text"
                        name="search"
                        value="<?= e($search) ?>"
                        placeholder="Search customer, email, space or booking ID..."
                    >

                </div>


                <div class="booking-filter-field">

                    <label>
                        Booking Status
                    </label>

                    <select name="status">

                        <option value="">
                            All statuses
                        </option>

                        <?php foreach (
                            [
                                'Pending',
                                'Confirmed',
                                'Checked in',
                                'Completed',
                                'Cancelled'
                            ] as $status
                        ): ?>

                            <option
                                value="<?= e($status) ?>"
                                <?= $statusFilter === $status
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                <?= e($status) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="booking-filter-field">

                    <label>
                        Payment
                    </label>

                    <select name="payment">

                        <option value="">
                            All payments
                        </option>

                        <?php foreach (
                            [
                                'Unpaid',
                                'Pending Verification',
                                'Paid',
                                'Rejected'
                            ] as $paymentStatus
                        ): ?>

                            <option
                                value="<?= e($paymentStatus) ?>"
                                <?= $paymentFilter === $paymentStatus
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                <?= e($paymentStatus) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <button
                    type="submit"
                    class="staff-btn staff-btn-primary booking-filter-button"
                >
                    <i class="fa-solid fa-filter"></i>

                    Filter
                </button>


                <a
                    href="bookings.php"
                    class="staff-btn staff-btn-light booking-filter-button"
                >
                    <i class="fa-solid fa-rotate-left"></i>

                    Reset
                </a>

            </form>

        </div>


        <!-- ========================================================
             BOOKINGS TABLE
        ========================================================= -->

        <div class="staff-panel">

            <div class="staff-panel-header">

                <div>

                    <h2>
                        All Bookings
                    </h2>

                    <p>
                        <?= count($bookings) ?>
                        booking<?= count($bookings) === 1 ? '' : 's' ?> found
                    </p>

                </div>

            </div>


            <div class="staff-table-wrap">

                <table class="staff-table bookings-table">

                    <thead>

                        <tr>

                            <th>
                                Booking
                            </th>

                            <th>
                                Customer
                            </th>

                            <th>
                                Space
                            </th>

                            <th>
                                Schedule
                            </th>

                            <th>
                                Guests
                            </th>

                            <th>
                                Amount
                            </th>

                            <th>
                                Payment
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (!$bookings): ?>

                        <tr>

                            <td
                                colspan="9"
                                class="staff-empty-table"
                            >

                                <i class="fa-regular fa-calendar-xmark"></i>

                                <strong>
                                    No bookings found
                                </strong>

                                <span>
                                    Try changing your filters or search.
                                </span>

                            </td>

                        </tr>

                    <?php endif; ?>


                    <?php foreach ($bookings as $booking): ?>


                        <?php

                        $bookingStatus =
                            $booking['status']
                            ?? 'Pending';

                        $paymentStatus =
                            $booking['payment_status']
                            ?? 'Unpaid';

                        $bookingClass = strtolower(
                            str_replace(
                                ' ',
                                '-',
                                $bookingStatus
                            )
                        );

                        $paymentClass = strtolower(
                            str_replace(
                                ' ',
                                '-',
                                $paymentStatus
                            )
                        );

                        $totalAmount = (float) (
                            $booking['total_amount']
                            ?? 0
                        );

                        $guestCount = (int) (
                            $booking['guest_count']
                            ?? 1
                        );

                        ?>


                        <tr>


                            <!-- BOOKING -->

                            <td>

                                <strong class="booking-number">

                                    #<?= str_pad(
                                        (string) $booking['id'],
                                        6,
                                        '0',
                                        STR_PAD_LEFT
                                    ) ?>

                                </strong>

                                <?php if (
                                    !empty(
                                        $booking['booking_type']
                                    )
                                ): ?>

                                    <span class="booking-type-text">

                                        <?= e(
                                            $booking['booking_type']
                                        ) ?>

                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- CUSTOMER -->

                            <td>

                                <div class="customer-cell">

                                    <div class="customer-small-avatar">

                                        <?= e(
                                            strtoupper(
                                                substr(
                                                    trim(
                                                        $booking['customer_name']
                                                        ?? 'C'
                                                    ),
                                                    0,
                                                    1
                                                )
                                            )
                                        ) ?>

                                    </div>


                                    <div class="customer-info">

                                        <strong>

                                            <?= e(
                                                $booking['customer_name']
                                                ?? 'Customer'
                                            ) ?>

                                        </strong>

                                        <span>

                                            <?= e(
                                                $booking['customer_email']
                                                ?? ''
                                            ) ?>

                                        </span>

                                    </div>

                                </div>

                            </td>


                            <!-- SPACE -->

                            <td>

                                <strong>

                                    <?= e(
                                        $booking['space']
                                        ?? '—'
                                    ) ?>

                                </strong>

                            </td>


                            <!-- SCHEDULE -->

                            <td>

                                <div class="booking-schedule">

                                    <strong>

                                        <?php if (
                                            !empty(
                                                $booking['visit_date']
                                            )
                                        ): ?>

                                            <?= date(
                                                'M j, Y',
                                                strtotime(
                                                    $booking['visit_date']
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </strong>


                                    <span>

                                        <?php if (
                                            !empty(
                                                $booking['visit_time']
                                            )
                                        ): ?>

                                            <?= date(
                                                'g:i A',
                                                strtotime(
                                                    $booking['visit_time']
                                                )
                                            ) ?>

                                        <?php else: ?>

                                            —

                                        <?php endif; ?>

                                    </span>

                                </div>

                            </td>


                            <!-- GUESTS -->

                            <td>

                                <span class="guest-count">

                                    <i class="fa-solid fa-user-group"></i>

                                    <?= $guestCount ?>

                                </span>

                            </td>


                            <!-- AMOUNT -->

                            <td>

                                <strong class="booking-amount">

                                    ₱<?= number_format(
                                        $totalAmount,
                                        2
                                    ) ?>

                                </strong>

                            </td>


                            <!-- PAYMENT -->

                            <td>

                                <span
                                    class="
                                        staff-status-badge
                                        payment-<?= e(
                                            $paymentClass
                                        ) ?>
                                    "
                                >

                                    <?= e(
                                        $paymentStatus
                                    ) ?>

                                </span>


                                <?php if (
                                    !empty(
                                        $booking['payment_method']
                                    )
                                ): ?>

                                    <small class="payment-method">

                                        <?= e(
                                            $booking['payment_method']
                                        ) ?>

                                    </small>

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <span
                                    class="
                                        staff-status-badge
                                        booking-<?= e(
                                            $bookingClass
                                        ) ?>
                                    "
                                >

                                    <?= e(
                                        $bookingStatus
                                    ) ?>

                                </span>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <a
                                    href="view.php?id=<?= (int) $booking['id'] ?>"
                                    class="staff-btn staff-btn-primary staff-btn-small"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                    View

                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </main>

</div>


<style>

/* ================================================================
   PAGE HEADING
================================================================ */

.bookings-heading {

    margin-bottom: 25px;

}


.bookings-heading h2 {

    margin: 0;

    color: #153e32;

    font-size: 24px;

}


.bookings-heading p {

    margin:
        7px
        0
        0;

    color: #7b8782;

    font-size: 13px;

}


/* ================================================================
   FILTER
================================================================ */

.booking-filter-panel {

    margin-top: 25px;

    margin-bottom: 25px;

    padding: 20px;

}


.booking-filter-form {

    display: grid;

    grid-template-columns:
        minmax(260px, 1fr)
        180px
        190px
        auto
        auto;

    gap: 12px;

    align-items: end;

}


/* SEARCH */

.booking-search {

    position: relative;

}


.booking-search i {

    position: absolute;

    top: 50%;

    left: 14px;

    transform:
        translateY(-50%);

    color: #88928e;

    font-size: 13px;

}


.booking-search input {

    width: 100%;

    height: 44px;

    padding:
        0
        14px
        0
        39px;

    border:
        1px solid
        #dfe6e3;

    border-radius: 10px;

    outline: none;

    background: #ffffff;

    color: #29473e;

    font-size: 12px;

}


.booking-search input:focus {

    border-color: #3c7a67;

    box-shadow:
        0 0 0 3px
        rgba(34, 104, 81, 0.08);

}


/* FILTER FIELD */

.booking-filter-field {

    display: flex;

    flex-direction: column;

    gap: 6px;

}


.booking-filter-field label {

    color: #61706b;

    font-size: 10px;

    font-weight: 700;

}


.booking-filter-field select {

    width: 100%;

    height: 44px;

    padding:
        0
        11px;

    border:
        1px solid
        #dfe6e3;

    border-radius: 10px;

    outline: none;

    background: #ffffff;

    color: #394a44;

    font-size: 11px;

}


.booking-filter-button {

    min-height: 44px;

}


/* ================================================================
   TABLE
================================================================ */

.bookings-table {

    min-width: 1100px;

}


.booking-number {

    display: block;

    color: #184d3d;

    font-size: 12px;

}


.booking-type-text {

    display: block;

    margin-top: 4px;

    color: #929c98;

    font-size: 9px;

}


/* CUSTOMER */

.customer-cell {

    display: flex;

    align-items: center;

    gap: 9px;

}


.customer-small-avatar {

    width: 35px;

    height: 35px;

    min-width: 35px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #e8f1ee;

    color: #174d3d;

    font-size: 12px;

    font-weight: 800;

}


.customer-info {

    min-width: 0;

}


.customer-info strong {

    display: block;

    max-width: 150px;

    overflow: hidden;

    color: #34463f;

    font-size: 11px;

    white-space: nowrap;

    text-overflow: ellipsis;

}


.customer-info span {

    display: block;

    max-width: 150px;

    margin-top: 3px;

    overflow: hidden;

    color: #929b98;

    font-size: 9px;

    white-space: nowrap;

    text-overflow: ellipsis;

}


/* SCHEDULE */

.booking-schedule strong {

    display: block;

    color: #3b4a45;

    font-size: 11px;

}


.booking-schedule span {

    display: block;

    margin-top: 3px;

    color: #89938f;

    font-size: 9px;

}


/* GUEST */

.guest-count {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    color: #53635d;

    font-size: 10px;

}


/* AMOUNT */

.booking-amount {

    color: #1d6049;

    font-size: 11px;

}


/* PAYMENT METHOD */

.payment-method {

    display: block;

    margin-top: 5px;

    color: #8d9693;

    font-size: 8px;

}


/* ================================================================
   STATUS BADGES
================================================================ */

.staff-status-badge {

    display: inline-flex;

    align-items: center;

    min-height: 27px;

    padding:
        0
        9px;

    border-radius: 50px;

    background: #eff2f1;

    color: #58635f;

    font-size: 9px;

    font-weight: 700;

    white-space: nowrap;

}


/* PAYMENT */

.payment-paid {

    background: #e3f4e8;

    color: #287344;

}


.payment-unpaid {

    background: #f8e8e8;

    color: #a34141;

}


.payment-pending-verification {

    background: #fff3d6;

    color: #916716;

}


.payment-rejected {

    background: #fde2e2;

    color: #a42f2f;

}


/* BOOKING */

.booking-pending {

    background: #fff2d6;

    color: #946915;

}


.booking-confirmed {

    background: #e3f4e8;

    color: #287344;

}


.booking-checked-in {

    background: #e2eefc;

    color: #285e98;

}


.booking-completed {

    background: #edeafa;

    color: #66529b;

}


.booking-cancelled {

    background: #f5e5e5;

    color: #a14343;

}


/* ================================================================
   EMPTY
================================================================ */

.staff-empty-table {

    padding:
        55px
        20px !important;

    text-align: center;

}


.staff-empty-table i {

    display: block;

    margin-bottom: 10px;

    color: #acb4b1;

    font-size: 30px;

}


.staff-empty-table strong {

    display: block;

    color: #44524d;

    font-size: 13px;

}


.staff-empty-table span {

    display: block;

    margin-top: 5px;

    color: #969e9b;

    font-size: 10px;

}


/* ================================================================
   BUTTON
================================================================ */

.staff-btn-small {

    min-height: 32px;

    padding:
        0
        10px;

    gap: 5px;

    font-size: 9px;

}


/* ================================================================
   RESPONSIVE
================================================================ */

@media (
    max-width: 1150px
) {

    .booking-filter-form {

        grid-template-columns:
            1fr
            1fr;

    }


    .booking-search {

        grid-column:
            1 / -1;

    }

}


@media (
    max-width: 700px
) {

    .booking-filter-form {

        grid-template-columns:
            1fr;

    }


    .booking-search {

        grid-column: auto;

    }


    .booking-filter-button {

        width: 100%;

        justify-content: center;

    }

}

</style>


</body>

</html>