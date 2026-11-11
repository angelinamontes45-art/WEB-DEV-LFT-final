<?php

/*
|--------------------------------------------------------------------------
| LOAD DATABASE / HELPERS
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../Includes/db.php';


/*
|--------------------------------------------------------------------------
| START SESSION
|--------------------------------------------------------------------------
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| CHECK IF USER IS LOGGED IN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['user_id'])) {

    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

$connection = db();


/*
|--------------------------------------------------------------------------
| GET STAFF ACCOUNT
|--------------------------------------------------------------------------
*/

$statement = $connection->prepare(
    'SELECT
        id,
        name,
        email,
        role
     FROM users
     WHERE id = ?
     LIMIT 1'
);

$statement->execute([
    (int) $_SESSION['user_id']
]);

$staff = $statement->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| VALIDATE STAFF ACCOUNT
|--------------------------------------------------------------------------
*/

if (!$staff) {

    $_SESSION = [];

    session_destroy();

    header('Location: login.php?error=account_not_found');
    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK ROLE
|--------------------------------------------------------------------------
*/

if (
    strtolower(
        trim(
            $staff['role'] ?? ''
        )
    ) !== 'staff'
) {

    $_SESSION = [];

    session_destroy();

    header('Location: login.php?error=unauthorized');
    exit;
}


/*
|--------------------------------------------------------------------------
| REFRESH STAFF SESSION DATA
|--------------------------------------------------------------------------
*/

$_SESSION['user_id'] =
    (int) $staff['id'];

$_SESSION['name'] =
    $staff['name'];

$_SESSION['email'] =
    $staff['email'];

$_SESSION['role'] =
    'staff';