<?php

/*
|--------------------------------------------------------------------------
| SHOW ERRORS WHILE DEVELOPING
|--------------------------------------------------------------------------
*/

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);


/*
|--------------------------------------------------------------------------
| STAFF AUTHENTICATION
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/_guard.php';


/*
|--------------------------------------------------------------------------
| PAGE SETTINGS
|--------------------------------------------------------------------------
*/

$currentStaffPage = 'dashboard';

$pageTitle = 'Staff Dashboard | LFT Dumaguete';


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

$connection = db();


/*
|--------------------------------------------------------------------------
| TODAY
|--------------------------------------------------------------------------
*/

$today = appToday();


/*
|--------------------------------------------------------------------------
| DEFAULT VALUES
|--------------------------------------------------------------------------
*/

$todayBookings = 0;
$pendingPayments = 0;
$unpaidBookings = 0;
$checkedInToday = 0;

$recentBookings = [];


/*
|--------------------------------------------------------------------------
| TODAY'S BOOKINGS
|--------------------------------------------------------------------------
*/

$statement = $connection->prepare(
    'SELECT COUNT(*)
     FROM bookings
     WHERE visit_date = ?'
);

$statement->execute([$today]);

$todayBookings = (int) $statement->fetchColumn();


/*
|--------------------------------------------------------------------------
| PENDING PAYMENTS
|--------------------------------------------------------------------------
*/

$statement = $connection->query(
    'SELECT COUNT(*)
     FROM bookings
     WHERE payment_status = "Pending Verification"'
);

$pendingPayments = (int) $statement->fetchColumn();


/*
|--------------------------------------------------------------------------
| UNPAID BOOKINGS
|--------------------------------------------------------------------------
*/

$statement = $connection->query(
    'SELECT COUNT(*)
     FROM bookings
     WHERE payment_status = "Unpaid"'
);

$unpaidBookings = (int) $statement->fetchColumn();


/*
|--------------------------------------------------------------------------
| CHECKED IN TODAY
|--------------------------------------------------------------------------
*/

$statement = $connection->prepare(
    'SELECT COUNT(*)
     FROM bookings
     WHERE visit_date = ?
     AND status = "Checked in"'
);

$statement->execute([$today]);

$checkedInToday = (int) $statement->fetchColumn();


/*
|--------------------------------------------------------------------------
| RECENT BOOKINGS
|--------------------------------------------------------------------------
*/

$recentStatement = $connection->query(
    'SELECT
        bookings.*,
        users.name AS customer_name,
        users.email AS customer_email
     FROM bookings
     JOIN users
        ON users.id = bookings.user_id
     ORDER BY bookings.id DESC
     LIMIT 8'
);

$recentBookings = $recentStatement->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| STAFF DETAILS
|--------------------------------------------------------------------------
*/

$staffName = $staff['name'] ?? 'Staff';

$staffEmail = $staff['email'] ?? '';

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

<!-- ================================================================
     SIDEBAR
================================================================ -->

<?php require_once __DIR__ . '/sidebar.php'; ?>


<!-- ================================================================
     MAIN
================================================================ -->

<div class="staff-main">


    <!-- ============================================================
         TOP HEADER
    ============================================================= -->

    <header class="staff-top-header">

        <div class="staff-header-left">

            <!-- MOBILE MENU -->

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
                    Dashboard
                </h1>

                <p>
                    Welcome back,
                    <?= e($staffName) ?>
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
             DASHBOARD TITLE
        ========================================================= -->

        <div class="dashboard-heading">

            <div>

                <h2>
                    Staff Overview
                </h2>

                <p>
                    Monitor today's bookings, payments and check-ins.
                </p>

            </div>

            <a
                href="bookings.php"
                class="staff-btn staff-btn-primary"
            >

                <i class="fa-solid fa-calendar-plus"></i>

                View Bookings

            </a>

        </div>


        <!-- ========================================================
             STATISTICS
        ========================================================= -->

        <div class="staff-stats-grid">


            <!-- TODAY BOOKINGS -->

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


                <a href="bookings.php">
                    View bookings
                    <i class="fa-solid fa-arrow-right"></i>
                </a>

            </div>


            <!-- PENDING PAYMENT -->

            <div class="staff-stat-card">

                <div class="staff-stat-card-top">

                    <div>

                        <p>
                            Pending Payments
                        </p>

                        <h2>
                            <?= $pendingPayments ?>
                        </h2>

                    </div>


                    <div class="staff-stat-icon">

                        <i class="fa-solid fa-clock"></i>

                    </div>

                </div>


                <?php if (file_exists(__DIR__ . '/payments.php')): ?>

                    <a href="payments.php">

                        Review payments

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                <?php endif; ?>

            </div>


            <!-- UNPAID -->

            <div class="staff-stat-card">

                <div class="staff-stat-card-top">

                    <div>

                        <p>
                            Unpaid
                        </p>

                        <h2>
                            <?= $unpaidBookings ?>
                        </h2>

                    </div>


                    <div class="staff-stat-icon">

                        <i class="fa-solid fa-wallet"></i>

                    </div>

                </div>


                <?php if (file_exists(__DIR__ . '/payments.php')): ?>

                    <a href="payments.php">

                        View payments

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                <?php endif; ?>

            </div>


            <!-- CHECKED IN -->

            <div class="staff-stat-card">

                <div class="staff-stat-card-top">

                    <div>

                        <p>
                            Checked In Today
                        </p>

                        <h2>
                            <?= $checkedInToday ?>
                        </h2>

                    </div>


                    <div class="staff-stat-icon">

                        <i class="fa-solid fa-right-to-bracket"></i>

                    </div>

                </div>


                <?php if (file_exists(__DIR__ . '/checkins.php')): ?>

                    <a href="checkins.php">

                        View check-ins

                        <i class="fa-solid fa-arrow-right"></i>

                    </a>

                <?php endif; ?>

            </div>


        </div>


        <!-- ========================================================
             QUICK ACTIONS
        ========================================================= -->

        <div class="dashboard-section">

            <div class="dashboard-section-header">

                <div>

                    <h2>
                        Quick Actions
                    </h2>

                    <p>
                        Common staff management tools.
                    </p>

                </div>

            </div>


            <div class="quick-actions-grid">


                <a
                    href="bookings.php"
                    class="quick-action-card"
                >

                    <div class="quick-action-icon">

                        <i class="fa-solid fa-calendar-check"></i>

                    </div>

                    <div>

                        <strong>
                            Manage Bookings
                        </strong>

                        <span>
                            View and update reservations
                        </span>

                    </div>

                </a>


                <?php if (file_exists(__DIR__ . '/payments.php')): ?>

                    <a
                        href="payments.php"
                        class="quick-action-card"
                    >

                        <div class="quick-action-icon">

                            <i class="fa-solid fa-money-check-dollar"></i>

                        </div>

                        <div>

                            <strong>
                                Verify Payments
                            </strong>

                            <span>
                                Review GCash and counter payments
                            </span>

                        </div>

                    </a>

                <?php endif; ?>


                <?php if (file_exists(__DIR__ . '/checkins.php')): ?>

                    <a
                        href="checkins.php"
                        class="quick-action-card"
                    >

                        <div class="quick-action-icon">

                            <i class="fa-solid fa-door-open"></i>

                        </div>

                        <div>

                            <strong>
                                Customer Check-in
                            </strong>

                            <span>
                                Check paid customers into LFT
                            </span>

                        </div>

                    </a>

                <?php endif; ?>


                <?php if (file_exists(__DIR__ . '/customers.php')): ?>

                    <a
                        href="customers.php"
                        class="quick-action-card"
                    >

                        <div class="quick-action-icon">

                            <i class="fa-solid fa-users"></i>

                        </div>

                        <div>

                            <strong>
                                Customers
                            </strong>

                            <span>
                                View registered customers
                            </span>

                        </div>

                    </a>

                <?php endif; ?>


            </div>

        </div>


        <!-- ========================================================
             RECENT BOOKINGS
        ========================================================= -->

        <div class="staff-panel">


            <div class="staff-panel-header">

                <div>

                    <h2>
                        Recent Bookings
                    </h2>

                    <p>
                        Latest customer reservations.
                    </p>

                </div>


                <a
                    href="bookings.php"
                    class="staff-btn staff-btn-light"
                >

                    View All

                </a>

            </div>


            <div class="staff-table-wrap">


                <table class="staff-table">


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
                                Date
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


                    <?php if (!$recentBookings): ?>

                        <tr>

                            <td
                                colspan="8"
                                class="staff-empty-table"
                            >

                                <i class="fa-regular fa-calendar-xmark"></i>

                                <strong>
                                    No bookings found
                                </strong>

                                <span>
                                    New bookings will appear here.
                                </span>

                            </td>

                        </tr>

                    <?php endif; ?>


                    <?php foreach ($recentBookings as $booking): ?>


                        <?php

                        $paymentStatus =
                            $booking['payment_status']
                            ?? 'Unpaid';

                        $bookingStatus =
                            $booking['status']
                            ?? 'Pending';

                        $totalAmount =
                            (float) (
                                $booking['total_amount']
                                ?? 0
                            );


                        $paymentClass =
                            strtolower(
                                str_replace(
                                    ' ',
                                    '-',
                                    $paymentStatus
                                )
                            );


                        $bookingClass =
                            strtolower(
                                str_replace(
                                    ' ',
                                    '-',
                                    $bookingStatus
                                )
                            );

                        ?>


                        <tr>


                            <!-- BOOKING -->

                            <td>

                                <strong>

                                    #<?= str_pad(
                                        (string) $booking['id'],
                                        6,
                                        '0',
                                        STR_PAD_LEFT
                                    ) ?>

                                </strong>

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


                                    <div>

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

                                <?= e(
                                    $booking['space']
                                    ?? '—'
                                ) ?>

                            </td>


                            <!-- DATE -->

                            <td>

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

                            </td>


                            <!-- AMOUNT -->

                            <td>

                                <strong>

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

                                    <?= e($paymentStatus) ?>

                                </span>

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

                                    <?= e($bookingStatus) ?>

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
   DASHBOARD EXTRAS
================================================================ */

.dashboard-heading {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    margin-bottom: 25px;

}


.dashboard-heading h2 {

    margin: 0;

    color: #143c31;

    font-size: 24px;

}


.dashboard-heading p {

    margin:
        7px
        0
        0;

    color: #7a8581;

    font-size: 13px;

}


/* ================================================================
   STAT CARD LINK
================================================================ */

.staff-stat-card > a {

    margin-top: 18px;

    display: inline-flex;

    align-items: center;

    gap: 7px;

    color: #176348;

    text-decoration: none;

    font-size: 12px;

    font-weight: 700;

}


.staff-stat-card > a:hover {

    text-decoration: underline;

}


/* ================================================================
   SECTIONS
================================================================ */

.dashboard-section {

    margin-top: 25px;

    padding: 24px;

    background: #ffffff;

    border-radius: 16px;

    border: 1px solid #edf0ef;

    box-shadow:
        0 4px 18px
        rgba(0, 0, 0, 0.035);

}


.dashboard-section-header {

    display: flex;

    justify-content: space-between;

    margin-bottom: 20px;

}


.dashboard-section-header h2 {

    margin: 0;

    color: #173d32;

    font-size: 18px;

}


.dashboard-section-header p {

    margin:
        5px
        0
        0;

    color: #7e8885;

    font-size: 12px;

}


/* ================================================================
   QUICK ACTIONS
================================================================ */

.quick-actions-grid {

    display: grid;

    grid-template-columns:
        repeat(
            4,
            minmax(0, 1fr)
        );

    gap: 14px;

}


.quick-action-card {

    min-height: 110px;

    padding: 17px;

    display: flex;

    align-items: center;

    gap: 13px;

    background: #f8faf9;

    border:
        1px solid
        #edf1ef;

    border-radius: 13px;

    text-decoration: none;

    color: #173d32;

    transition:
        transform 0.18s ease,
        box-shadow 0.18s ease,
        border-color 0.18s ease;

}


.quick-action-card:hover {

    transform:
        translateY(-2px);

    border-color: #dce8e3;

    box-shadow:
        0 7px 18px
        rgba(0, 0, 0, 0.06);

}


.quick-action-icon {

    width: 45px;

    height: 45px;

    min-width: 45px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 11px;

    background: #e6f0ec;

    color: #15513f;

    font-size: 18px;

}


.quick-action-card strong {

    display: block;

    font-size: 13px;

}


.quick-action-card span {

    display: block;

    margin-top: 5px;

    color: #808b87;

    font-size: 10px;

    line-height: 1.5;

}


/* ================================================================
   CUSTOMER CELL
================================================================ */

.customer-cell {

    display: flex;

    align-items: center;

    gap: 9px;

}


.customer-small-avatar {

    width: 34px;

    height: 34px;

    min-width: 34px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #e9f1ee;

    color: #164d3d;

    font-size: 12px;

    font-weight: 800;

}


.customer-cell strong {

    display: block;

    font-size: 12px;

}


.customer-cell span {

    display: block;

    max-width: 160px;

    margin-top: 3px;

    overflow: hidden;

    color: #8a928f;

    font-size: 9px;

    white-space: nowrap;

    text-overflow: ellipsis;

}


/* ================================================================
   BADGES
================================================================ */

.staff-status-badge {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-height: 27px;

    padding:
        0
        9px;

    border-radius: 20px;

    background: #f0f2f1;

    color: #5b6562;

    font-size: 9px;

    font-weight: 700;

    white-space: nowrap;

}


/* PAYMENT */

.payment-paid {

    background: #e5f5ea;

    color: #23713d;

}


.payment-pending-verification {

    background: #fff3d9;

    color: #8c6512;

}


.payment-unpaid {

    background: #f7e6e6;

    color: #a54242;

}


.payment-rejected {

    background: #fde4e4;

    color: #a52d2d;

}


/* BOOKING STATUS */

.booking-confirmed {

    background: #e5f5ea;

    color: #23713d;

}


.booking-pending {

    background: #fff3d9;

    color: #8c6512;

}


.booking-checked-in {

    background: #e2eefc;

    color: #2b5e96;

}


.booking-completed {

    background: #ece9fa;

    color: #67549b;

}


.booking-cancelled {

    background: #f4e5e5;

    color: #a04545;

}


/* ================================================================
   EMPTY TABLE
================================================================ */

.staff-empty-table {

    padding: 55px 20px !important;

    text-align: center;

}


.staff-empty-table i {

    display: block;

    margin-bottom: 10px;

    color: #a6afac;

    font-size: 30px;

}


.staff-empty-table strong {

    display: block;

    color: #43504b;

    font-size: 13px;

}


.staff-empty-table span {

    display: block;

    margin-top: 5px;

    color: #939b98;

    font-size: 10px;

}


/* ================================================================
   BUTTON
================================================================ */

.staff-btn-small {

    min-height: 31px;

    padding:
        0
        10px;

    gap: 5px;

    font-size: 10px;

}


/* ================================================================
   RESPONSIVE
================================================================ */

@media (
    max-width: 1100px
) {

    .quick-actions-grid {

        grid-template-columns:
            repeat(
                2,
                minmax(0, 1fr)
            );

    }

}


@media (
    max-width: 700px
) {

    .dashboard-heading {

        align-items: flex-start;

        flex-direction: column;

    }


    .quick-actions-grid {

        grid-template-columns:
            1fr;

    }


    .staff-user-details {

        display: none;

    }

}

</style>


</body>

</html>