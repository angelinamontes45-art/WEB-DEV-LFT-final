<?php

require_once __DIR__ . '/_guard.php';

$currentStaffPage = 'checkins';
$pageTitle = 'Check-ins | LFT Staff';

$connection = db();
$today = appToday();

$message = $_GET['message'] ?? '';


/*
|--------------------------------------------------------------------------
| CHECK IN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrf();

    $bookingId =
        (int)($_POST['booking_id'] ?? 0);

    $statement = $connection->prepare(
        'SELECT *
         FROM bookings
         WHERE id = ?
         LIMIT 1'
    );

    $statement->execute([$bookingId]);

    $booking =
        $statement->fetch(PDO::FETCH_ASSOC);


    if (!$booking) {

        $message = 'Booking not found.';

    } elseif (
        ($booking['payment_status'] ?? 'Unpaid')
        !== 'Paid'
    ) {

        $message =
            'Payment must be confirmed before check-in.';

    } else {

        $now =
            appNow()->format('Y-m-d H:i:s');

        $update = $connection->prepare(
            'UPDATE bookings
             SET status = "Checked in",
                 checked_in_at = ?,
                 updated_at = ?
             WHERE id = ?'
        );

        $update->execute([
            $now,
            $now,
            $bookingId
        ]);

        header(
            'Location: checkins.php?message=' .
            urlencode('Customer checked in successfully.')
        );

        exit;
    }
}


/*
|--------------------------------------------------------------------------
| TODAY BOOKINGS
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
     WHERE bookings.visit_date = ?
     ORDER BY bookings.visit_time ASC'
);

$statement->execute([$today]);

$bookings =
    $statement->fetchAll(PDO::FETCH_ASSOC);


require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';

$staffName =
    $staff['name'] ?? 'Staff';

$staffInitial =
    strtoupper(substr($staffName, 0, 1));

?>

<div class="staff-main">

<header class="staff-top-header">

    <div class="staff-header-left">

        <button
            type="button"
            class="staff-menu-button"
            id="staffMenuButton"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <h1>Check-ins</h1>

    </div>

    <div class="staff-user">

        <div class="staff-avatar">
            <?= e($staffInitial) ?>
        </div>

        <div class="staff-user-details">
            <strong><?= e($staffName) ?></strong>
            <span>LFT Staff</span>
        </div>

    </div>

</header>


<main class="staff-content">

    <?php if ($message !== ''): ?>

        <div style="
            padding:14px 18px;
            background:#fff6dd;
            border-radius:10px;
            margin-bottom:20px;
        ">
            <?= e($message) ?>
        </div>

    <?php endif; ?>


    <div class="staff-panel">

        <div class="staff-panel-header">

            <div>
                <h2>Today's Check-ins</h2>

                <small>
                    <?= date(
                        'F j, Y',
                        strtotime($today)
                    ) ?>
                </small>
            </div>

        </div>


        <div class="staff-table-wrap">

            <table class="staff-table">

                <thead>

                <tr>
                    <th>Booking</th>
                    <th>Customer</th>
                    <th>Space</th>
                    <th>Time</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                </thead>

                <tbody>

                <?php if (!$bookings): ?>

                    <tr>
                        <td
                            colspan="7"
                            style="text-align:center;"
                        >
                            No bookings scheduled today.
                        </td>
                    </tr>

                <?php endif; ?>


                <?php foreach ($bookings as $booking): ?>

                    <tr>

                        <td>
                            #<?= str_pad(
                                (string)$booking['id'],
                                6,
                                '0',
                                STR_PAD_LEFT
                            ) ?>
                        </td>

                        <td>
                            <strong>
                                <?= e($booking['name']) ?>
                            </strong>
                        </td>

                        <td>
                            <?= e($booking['space']) ?>
                        </td>

                        <td>
                            <?= date(
                                'g:i A',
                                strtotime($booking['visit_time'])
                            ) ?>
                        </td>

                        <td>
                            <?= e(
                                $booking['payment_status']
                                ?? 'Unpaid'
                            ) ?>
                        </td>

                        <td>
                            <?= e(
                                $booking['status']
                                ?? 'Pending'
                            ) ?>
                        </td>

                        <td>

                            <?php if (
                                ($booking['status'] ?? '')
                                === 'Checked in'
                            ): ?>

                                <strong>
                                    <i class="fa-solid fa-check"></i>
                                    Checked In
                                </strong>

                            <?php elseif (
                                ($booking['payment_status'] ?? '')
                                === 'Paid'
                            ): ?>

                                <form method="post">

                                    <?= csrfField() ?>

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= (int)$booking['id'] ?>"
                                    >

                                    <button
                                        type="submit"
                                        class="staff-btn staff-btn-primary"
                                    >
                                        <i class="fa-solid fa-right-to-bracket"></i>
                                        Check In
                                    </button>

                                </form>

                            <?php else: ?>

                                <span>
                                    Payment required
                                </span>

                            <?php endif; ?>

                        </td>

                    </tr>

                <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </div>

</main>

</div>


<script>
document
    .getElementById('staffMenuButton')
    ?.addEventListener('click', function () {

        document
            .getElementById('staffSidebar')
            ?.classList.toggle('open');

    });
</script>

</body>
</html>