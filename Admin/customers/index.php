<?php

/*
|--------------------------------------------------------------------------
| ADMIN GUARD
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../_guard.php';


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

$connection = db();


/*
|--------------------------------------------------------------------------
| SEARCH
|--------------------------------------------------------------------------
*/

$search = trim($_GET['q'] ?? '');


/*
|--------------------------------------------------------------------------
| CONDITIONS
|--------------------------------------------------------------------------
*/

$conditions = [
    "users.role = 'customer'"
];

$params = [];


if ($search !== '') {

    $conditions[] = '
        (
            users.name LIKE ?
            OR users.email LIKE ?
        )
    ';

    $term = '%' . $search . '%';

    $params[] = $term;
    $params[] = $term;
}


/*
|--------------------------------------------------------------------------
| CUSTOMER QUERY
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        users.id,
        users.name,
        users.email,
        users.created_at,

        COUNT(bookings.id) AS booking_count,

        SUM(
            CASE
                WHEN bookings.status IN (
                    'Pending',
                    'Confirmed',
                    'Checked in'
                )
                THEN 1
                ELSE 0
            END
        ) AS active_count,

        MAX(bookings.created_at) AS last_booking_at

    FROM users

    LEFT JOIN bookings
        ON bookings.user_id = users.id

    WHERE " . implode(' AND ', $conditions) . "

    GROUP BY
        users.id,
        users.name,
        users.email,
        users.created_at

    ORDER BY
        users.created_at DESC
";


$statement = $connection->prepare($sql);

$statement->execute($params);

$customers = $statement->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/


// TOTAL CUSTOMERS

$totalCustomers = (int) $connection
    ->query(
        "
        SELECT COUNT(*)
        FROM users
        WHERE role = 'customer'
        "
    )
    ->fetchColumn();


// CUSTOMERS WITH BOOKINGS

$customersWithBookings = (int) $connection
    ->query(
        "
        SELECT COUNT(DISTINCT users.id)

        FROM users

        INNER JOIN bookings
            ON bookings.user_id = users.id

        WHERE users.role = 'customer'
        "
    )
    ->fetchColumn();


// NEW CUSTOMERS THIS MONTH
// MySQL / MariaDB compatible

$newCustomersThisMonth = (int) $connection
    ->query(
        "
        SELECT COUNT(*)

        FROM users

        WHERE role = 'customer'

        AND created_at >= DATE_FORMAT(
            CURDATE(),
            '%Y-%m-01'
        )
        "
    )
    ->fetchColumn();


$stats = [

    'customers' => $totalCustomers,

    'withBookings' => $customersWithBookings,

    'newThisMonth' => $newCustomersThisMonth

];


/*
|--------------------------------------------------------------------------
| PAGE TITLE
|--------------------------------------------------------------------------
*/

$pageTitle = 'Customers | LFT Admin';

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?= e($pageTitle) ?>
    </title>


    <!-- MAIN CSS -->

    <link
        rel="stylesheet"
        href="../../assets/css/style.css"
    >

    <link
        rel="stylesheet"
        href="../../assets/css/portal.css"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

</head>


<body>


<main class="admin-app">


    <!-- ============================================================
         SIDEBAR
    ============================================================= -->

    <?php require_once __DIR__ . '/../sidebar.php'; ?>


    <!-- ============================================================
         MAIN CONTENT
    ============================================================= -->

    <div class="admin-main">


        <!-- ========================================================
             TOP BAR
        ========================================================= -->

        <header class="admin-topbar">


            <div>

                <p class="section-label">
                    COMMUNITY
                </p>

                <h1>
                    Customers
                </h1>

                <p>
                    See registered members and their booking activity.
                </p>

            </div>


            <?php require_once __DIR__ . '/../profile.php'; ?>


        </header>



        <!-- ========================================================
             KPI CARDS
        ========================================================= -->

        <section class="admin-kpis">


            <!-- TOTAL CUSTOMERS -->

            <article>

                <i class="fa-solid fa-users"></i>

                <span>
                    Total customers
                </span>

                <strong>
                    <?= $stats['customers'] ?>
                </strong>

                <small>
                    Registered accounts
                </small>

            </article>



            <!-- CUSTOMERS WITH BOOKINGS -->

            <article>

                <i class="fa-regular fa-calendar-check"></i>

                <span>
                    Customers with bookings
                </span>

                <strong>
                    <?= $stats['withBookings'] ?>
                </strong>

                <small>
                    Have used reservations
                </small>

            </article>



            <!-- NEW THIS MONTH -->

            <article>

                <i class="fa-solid fa-user-plus"></i>

                <span>
                    New this month
                </span>

                <strong>
                    <?= $stats['newThisMonth'] ?>
                </strong>

                <small>
                    Recent registrations
                </small>

            </article>


        </section>



        <!-- ========================================================
             CUSTOMER DIRECTORY
        ========================================================= -->

        <section class="admin-widget booking-panel">


            <!-- HEADER -->

            <div class="widget-heading">


                <div>

                    <h2>
                        Customer Directory
                    </h2>

                    <p>
                        <?= count($customers) ?>
                        customer<?= count($customers) === 1 ? '' : 's' ?>
                        shown
                    </p>

                </div>


                <a href="../staff/">

                    <i class="fa-solid fa-users-gear"></i>

                    Manage staff accounts

                </a>


            </div>



            <!-- ====================================================
                 SEARCH FORM
            ===================================================== -->

            <form
                class="booking-filters"
                method="get"
                action="index.php"
            >


                <label for="customer-search">
                    Search
                </label>


                <input
                    id="customer-search"
                    name="q"
                    type="text"
                    value="<?= e($search) ?>"
                    placeholder="Name or email"
                >


                <button
                    class="btn btn-green"
                    type="submit"
                >

                    <i class="fa-solid fa-magnifying-glass"></i>

                    SEARCH

                </button>


                <?php if ($search !== ''): ?>

                    <a
                        class="text-link"
                        href="index.php"
                    >

                        <i class="fa-solid fa-xmark"></i>

                        Clear

                    </a>

                <?php endif; ?>


            </form>



            <!-- ====================================================
                 CUSTOMER TABLE
            ===================================================== -->

            <div class="booking-table">


                <!-- TABLE HEADER -->

                <div class="message-table-head">

                    <span>
                        Customer
                    </span>

                    <span>
                        Email
                    </span>

                    <span>
                        Joined
                    </span>

                    <span>
                        Bookings
                    </span>

                    <span>
                        Active
                    </span>

                    <span>
                        Action
                    </span>

                </div>



                <!-- EMPTY STATE -->

                <?php if (!$customers): ?>


                    <div class="empty-state">

                        <i class="fa-solid fa-users-slash"></i>

                        <h3>
                            No customers found
                        </h3>

                        <p>
                            Try searching using another name or email.
                        </p>

                    </div>


                <?php else: ?>


                    <?php foreach ($customers as $customer): ?>


                        <div class="message-table-row">


                            <!-- CUSTOMER NAME -->

                            <strong>

                                <?= e(
                                    $customer['name']
                                    ?? 'Customer'
                                ) ?>

                            </strong>



                            <!-- EMAIL -->

                            <span>

                                <?= e(
                                    $customer['email']
                                    ?? ''
                                ) ?>

                            </span>



                            <!-- JOIN DATE -->

                            <span>

                                <?php if (
                                    !empty(
                                        $customer['created_at']
                                    )
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

                            </span>



                            <!-- BOOKINGS -->

                            <span>

                                <?= (int) (
                                    $customer['booking_count']
                                    ?? 0
                                ) ?>

                            </span>



                            <!-- ACTIVE BOOKINGS -->

                            <span>

                                <?= (int) (
                                    $customer['active_count']
                                    ?? 0
                                ) ?>

                            </span>



                            <!-- ACTION -->

                            <a
                                class="text-link"
                                href="../bookings/?q=<?= urlencode(
                                    $customer['email'] ?? ''
                                ) ?>"
                            >

                                View bookings

                                <i class="fa-solid fa-arrow-right"></i>

                            </a>


                        </div>


                    <?php endforeach; ?>


                <?php endif; ?>


            </div>


        </section>


    </div>


</main>


</body>

</html>