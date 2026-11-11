<?php

/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION GUARD
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../Includes/db.php';


/*
|--------------------------------------------------------------------------
| SESSION
|--------------------------------------------------------------------------
*/

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (empty($_SESSION['user_id'])) {

    header('Location: login.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

$connection = db();


/*
|--------------------------------------------------------------------------
| GET CURRENT ADMIN
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

$admin = $statement->fetch(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| ACCOUNT CHECK
|--------------------------------------------------------------------------
*/

if (!$admin) {

    $_SESSION = [];

    session_destroy();

    header(
        'Location: login.php?error=account_not_found'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ADMIN ROLE CHECK
|--------------------------------------------------------------------------
*/

if (
    strtolower(
        trim(
            $admin['role'] ?? ''
        )
    ) !== 'admin'
) {

    $_SESSION = [];

    session_destroy();

    header(
        'Location: login.php?error=unauthorized'
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| REFRESH SESSION DATA
|--------------------------------------------------------------------------
*/

$_SESSION['user_id'] =
    (int) $admin['id'];

$_SESSION['name'] =
    $admin['name'];

$_SESSION['email'] =
    $admin['email'];

$_SESSION['role'] =
    'admin';


/*
|--------------------------------------------------------------------------
| USER ALIAS
|--------------------------------------------------------------------------
|
| Some existing Admin pages use $user instead of $admin.
|
*/

$user = $admin;


/*
|--------------------------------------------------------------------------
| ADMIN REDIRECT HELPER
|--------------------------------------------------------------------------
*/

if (!function_exists('adminRedirect')) {

    function adminRedirect(
        string $section = '',
        string $query = ''
    ): never {

        $allowedSections = [
            '',
            'bookings',
            'payments',
            'customers',
            'staff',
            'memberships',
            'spaces',
            'amenities',
            'events',
            'messages',
            'settings'
        ];


        if (
            !in_array(
                $section,
                $allowedSections,
                true
            )
        ) {

            $section = '';

        }


        if ($section === '') {

            $target =
                __DIR__ . '/index.php';

            /*
             * Header needs URL, not filesystem path.
             */

            $url = '/WEB-DEV-LFT/Admin/index.php';

        } else {

            $url =
                '/WEB-DEV-LFT/Admin/'
                . $section
                . '/index.php';

        }


        if ($query !== '') {

            $url .=
                '?'
                . ltrim(
                    $query,
                    '?'
                );

        }


        header('Location: ' . $url);

        exit;
    }

}