<?php

require_once __DIR__ . '/_guard.php';

$currentStaffPage = 'customers';
$pageTitle = 'Customers | LFT Staff';

$connection = db();


/*
|--------------------------------------------------------------------------
| CUSTOMERS
|--------------------------------------------------------------------------
*/

$statement = $connection->query(
    'SELECT
        users.id,
        users.name,
        users.email,
        users.created_at,
        COUNT(bookings.id) AS total_bookings,
        MAX(bookings.visit_date) AS last_booking
     FROM users
     LEFT JOIN bookings
        ON bookings.user_id = users.id
     WHERE users.role = "customer"
     GROUP BY
        users.id,
        users.name,
        users.email,
        users.created_at
     ORDER BY users.id DESC'
);

$customers =
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

        <h1>Customers</h1>

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

    <div class="staff-panel">

        <div class="staff-panel-header">

            <div>

                <h2>
                    Customer Directory
                </h2>

                <small>
                    <?= count($customers) ?>
                    registered customers
                </small>

            </div>

        </div>


        <div class="staff-table-wrap">

            <table class="staff-table">

                <thead>

                <tr>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Total Bookings</th>
                    <th>Last Booking</th>
                    <th>Joined</th>
                </tr>

                </thead>


                <tbody>

                <?php if (!$customers): ?>

                    <tr>

                        <td
                            colspan="5"
                            style="text-align:center;"
                        >
                            No customers found.
                        </td>

                    </tr>

                <?php endif; ?>


                <?php foreach ($customers as $customer): ?>

                    <tr>

                        <td>

                            <strong>
                                <?= e($customer['name']) ?>
                            </strong>

                        </td>

                        <td>
                            <?= e($customer['email']) ?>
                        </td>

                        <td>
                            <?= (int)$customer['total_bookings'] ?>
                        </td>

                        <td>

                            <?php if (
                                !empty($customer['last_booking'])
                            ): ?>

                                <?= date(
                                    'M j, Y',
                                    strtotime(
                                        $customer['last_booking']
                                    )
                                ) ?>

                            <?php else: ?>

                                —

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php if (
                                !empty($customer['created_at'])
                            ): ?>

                                <?= date(
                                    'M j, Y',
                                    strtotime(
                                        $customer['created_at']
                                    )
                                ) ?>

                            <?php else: ?>

                                —

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