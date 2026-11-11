<?php

require_once __DIR__ . '/_guard.php';

$currentStaffPage = 'payments';
$pageTitle = 'Payments | LFT Staff';

$connection = db();

$message = $_GET['message'] ?? '';

/*
|--------------------------------------------------------------------------
| VERIFY / REJECT PAYMENT
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrf();

    $bookingId = (int)($_POST['booking_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $statement = $connection->prepare(
        'SELECT *
         FROM bookings
         WHERE id = ?
         LIMIT 1'
    );

    $statement->execute([$bookingId]);

    $booking = $statement->fetch(PDO::FETCH_ASSOC);

    if ($booking) {

        if ($action === 'paid') {

            $now = appNow()->format('Y-m-d H:i:s');

            $update = $connection->prepare(
                'UPDATE bookings
                 SET payment_status = "Paid",
                     paid_at = ?,
                     updated_at = ?
                 WHERE id = ?'
            );

            $update->execute([
                $now,
                $now,
                $bookingId
            ]);

            header(
                'Location: payments.php?message=' .
                urlencode('Payment verified successfully.')
            );
            exit;
        }

        if ($action === 'reject') {

            $update = $connection->prepare(
                'UPDATE bookings
                 SET payment_status = "Rejected",
                     paid_at = NULL,
                     updated_at = ?
                 WHERE id = ?'
            );

            $update->execute([
                appNow()->format('Y-m-d H:i:s'),
                $bookingId
            ]);

            header(
                'Location: payments.php?message=' .
                urlencode('Payment rejected.')
            );
            exit;
        }
    }
}


/*
|--------------------------------------------------------------------------
| GET PAYMENTS
|--------------------------------------------------------------------------
*/

$statement = $connection->query(
    'SELECT
        bookings.*,
        users.name,
        users.email
     FROM bookings
     JOIN users
        ON users.id = bookings.user_id
     ORDER BY
        CASE
            WHEN bookings.payment_status = "Pending Verification" THEN 1
            WHEN bookings.payment_status = "Unpaid" THEN 2
            WHEN bookings.payment_status = "Paid" THEN 3
            ELSE 4
        END,
        bookings.id DESC'
);

$payments = $statement->fetchAll(PDO::FETCH_ASSOC);

require_once __DIR__ . '/header.php';
require_once __DIR__ . '/sidebar.php';

$staffName = $staff['name'] ?? 'Staff';
$staffInitial = strtoupper(substr($staffName, 0, 1));

?>

<div class="staff-main">

<header class="staff-top-header">

    <div class="staff-header-left">

        <button
            class="staff-menu-button"
            id="staffMenuButton"
            type="button"
        >
            <i class="fa-solid fa-bars"></i>
        </button>

        <h1>Payments</h1>

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
            background:#eaf8ee;
            color:#176b37;
            padding:14px 18px;
            border-radius:10px;
            margin-bottom:20px;
        ">
            <?= e($message) ?>
        </div>

    <?php endif; ?>


    <div class="staff-panel">

        <div class="staff-panel-header">

            <div>
                <h2>Payment Management</h2>
                <small>
                    Verify GCash and counter payments
                </small>
            </div>

        </div>


        <div class="staff-table-wrap">

            <table class="staff-table">

                <thead>

                <tr>
                    <th>Booking</th>
                    <th>Customer</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                </thead>

                <tbody>

                <?php if (!$payments): ?>

                    <tr>
                        <td colspan="7" style="text-align:center;">
                            No payments found.
                        </td>
                    </tr>

                <?php endif; ?>


                <?php foreach ($payments as $payment): ?>

                    <tr>

                        <td>
                            #<?= str_pad(
                                (string)$payment['id'],
                                6,
                                '0',
                                STR_PAD_LEFT
                            ) ?>
                        </td>

                        <td>
                            <strong>
                                <?= e($payment['name']) ?>
                            </strong>

                            <br>

                            <small>
                                <?= e($payment['email']) ?>
                            </small>
                        </td>

                        <td>
                            <?= e(
                                $payment['payment_method']
                                ?? 'Not selected'
                            ) ?>
                        </td>

                        <td>
                            <?= e(
                                $payment['payment_reference']
                                ?? '-'
                            ) ?>
                        </td>

                        <td>
                            <strong>
                                ₱<?= number_format(
                                    (float)($payment['total_amount'] ?? 0),
                                    2
                                ) ?>
                            </strong>
                        </td>

                        <td>
                            <?= e(
                                $payment['payment_status']
                                ?? 'Unpaid'
                            ) ?>
                        </td>

                        <td>

                            <?php if (
                                ($payment['payment_status'] ?? '')
                                !== 'Paid'
                            ): ?>

                                <form
                                    method="post"
                                    style="display:inline;"
                                >

                                    <?= csrfField() ?>

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= (int)$payment['id'] ?>"
                                    >

                                    <button
                                        name="action"
                                        value="paid"
                                        class="staff-btn staff-btn-primary"
                                        type="submit"
                                    >
                                        <i class="fa-solid fa-check"></i>
                                        Paid
                                    </button>

                                </form>

                            <?php endif; ?>


                            <?php if (
                                ($payment['payment_status'] ?? '')
                                === 'Pending Verification'
                            ): ?>

                                <form
                                    method="post"
                                    style="display:inline;"
                                >

                                    <?= csrfField() ?>

                                    <input
                                        type="hidden"
                                        name="booking_id"
                                        value="<?= (int)$payment['id'] ?>"
                                    >

                                    <button
                                        name="action"
                                        value="reject"
                                        class="staff-btn staff-btn-light"
                                        type="submit"
                                    >
                                        Reject
                                    </button>

                                </form>

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