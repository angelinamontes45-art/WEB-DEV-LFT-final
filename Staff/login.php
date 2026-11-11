
<?php

require '../Includes/db.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$connection = db();
$errors = [];

/*
|--------------------------------------------------------------------------
| IF ALREADY LOGGED IN AS STAFF
|--------------------------------------------------------------------------
*/

if (
    isset($_SESSION['user_id']) &&
    isset($_SESSION['role']) &&
    $_SESSION['role'] === 'staff'
) {
    header('Location: index.php');
    exit;
}


/*
|--------------------------------------------------------------------------
| PROCESS LOGIN
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($email === '') {
        $errors[] = 'Please enter your email address.';
    }

    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    /*
    |--------------------------------------------------------------------------
    | FIND STAFF ACCOUNT
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        $statement = $connection->prepare(
            'SELECT *
             FROM users
             WHERE email = ?
             AND role = "staff"
             LIMIT 1'
        );

        $statement->execute([
            $email
        ]);

        $staff = $statement->fetch(PDO::FETCH_ASSOC);

        if (
            !$staff ||
            !password_verify(
                $password,
                $staff['password_hash']
            )
        ) {

            $errors[] =
                'Invalid staff email or password.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | SECURITY
            |--------------------------------------------------------------------------
            */

            session_regenerate_id(true);

            /*
            |--------------------------------------------------------------------------
            | SAVE STAFF SESSION
            |--------------------------------------------------------------------------
            */

            $_SESSION['user_id'] =
                (int)$staff['id'];

            $_SESSION['name'] =
                $staff['name'];

            $_SESSION['email'] =
                $staff['email'];

            $_SESSION['role'] =
                'staff';

            /*
            |--------------------------------------------------------------------------
            | REDIRECT TO STAFF DASHBOARD
            |--------------------------------------------------------------------------
            */

            header(
                'Location: index.php'
            );

            exit;
        }
    }
}


$pageTitle = 'Staff Login | LFT Dumaguete';
$currentPage = '';

require '../Includes/header.php';

?>

<main>

<section class="auth-section">

<div class="staff-login-container">


    <div class="staff-login-card">


        <!-- HEADER -->

        <div class="staff-login-header">

            <div class="staff-login-icon">

                <i class="fa-solid fa-user-tie"></i>

            </div>


            <p class="section-label">
                STAFF PORTAL
            </p>


            <h1>
                Staff Login
            </h1>


            <p>
                Sign in using your authorized
                LFT staff account.
            </p>

        </div>


        <!-- ERRORS -->

        <?php foreach ($errors as $error): ?>

            <div class="form-error">

                <i class="fa-solid fa-circle-exclamation"></i>

                <?= e($error) ?>

            </div>

        <?php endforeach; ?>


        <!-- LOGIN FORM -->

        <form
            method="post"
            class="tour-form staff-login-form"
        >

            <?= csrfField() ?>


            <label for="email">
                Staff Email
            </label>


            <div class="input-icon-wrapper">

                <i class="fa-solid fa-envelope"></i>

                <input
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    placeholder="staff@lftdumaguete.com"
                    value="<?= e($_POST['email'] ?? '') ?>"
                    required
                >

            </div>


            <label for="password">
                Password
            </label>


            <div class="input-icon-wrapper">

                <i class="fa-solid fa-lock"></i>

                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    placeholder="Enter your password"
                    required
                >


                <button
                    type="button"
                    class="password-toggle"
                    id="passwordToggle"
                    aria-label="Show password"
                >

                    <i
                        class="fa-solid fa-eye"
                        id="passwordIcon"
                    ></i>

                </button>

            </div>


            <button
                type="submit"
                class="btn btn-green staff-login-button"
            >

                <i class="fa-solid fa-right-to-bracket"></i>

                SIGN IN AS STAFF

            </button>


        </form>


        <div class="staff-login-footer">

            <a href="../index.php">

                <i class="fa-solid fa-arrow-left"></i>

                Back to LFT website

            </a>

        </div>


    </div>


</div>

</section>

</main>


<style>

/* ================================================================
   STAFF LOGIN
================================================================ */

.staff-login-container {
    width: 100%;
    max-width: 500px;
    margin: 0 auto;
}

.staff-login-card {
    background: #ffffff;
    border: 1px solid #e4e4e4;
    border-radius: 20px;
    padding: 35px;
}

.staff-login-header {
    text-align: center;
    margin-bottom: 30px;
}

.staff-login-header h1 {
    margin: 8px 0;
}

.staff-login-header p {
    color: #666666;
    line-height: 1.5;
}

.staff-login-icon {
    width: 75px;
    height: 75px;

    margin: 0 auto 20px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #eef4ef;

    font-size: 30px;
}


/* ================================================================
   INPUT
================================================================ */

.staff-login-form label {
    display: block;
    margin: 15px 0 7px;
    font-weight: 600;
}

.input-icon-wrapper {
    position: relative;
}

.input-icon-wrapper > i {
    position: absolute;

    left: 15px;
    top: 50%;

    transform: translateY(-50%);

    color: #777777;

    pointer-events: none;
}

.input-icon-wrapper input {
    width: 100%;

    padding-left: 45px;
    padding-right: 45px;

    box-sizing: border-box;
}


/* ================================================================
   PASSWORD
================================================================ */

.password-toggle {
    position: absolute;

    top: 50%;
    right: 10px;

    transform: translateY(-50%);

    width: 40px;
    height: 40px;

    border: none;
    background: transparent;

    cursor: pointer;

    color: #666666;
}


/* ================================================================
   BUTTON
================================================================ */

.staff-login-button {
    width: 100%;

    margin-top: 25px;

    display: flex;
    align-items: center;
    justify-content: center;

    gap: 8px;
}


/* ================================================================
   FOOTER
================================================================ */

.staff-login-footer {
    margin-top: 25px;

    padding-top: 20px;

    border-top: 1px solid #eeeeee;

    text-align: center;
}

.staff-login-footer a {
    text-decoration: none;
}


/* ================================================================
   MOBILE
================================================================ */

@media (max-width: 600px) {

    .staff-login-card {
        padding: 24px;
    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const password =
            document.getElementById(
                'password'
            );

        const toggle =
            document.getElementById(
                'passwordToggle'
            );

        const icon =
            document.getElementById(
                'passwordIcon'
            );


        toggle.addEventListener(
            'click',
            function () {

                if (
                    password.type === 'password'
                ) {

                    password.type = 'text';

                    icon.classList.remove(
                        'fa-eye'
                    );

                    icon.classList.add(
                        'fa-eye-slash'
                    );

                } else {

                    password.type =
                        'password';

                    icon.classList.remove(
                        'fa-eye-slash'
                    );

                    icon.classList.add(
                        'fa-eye'
                    );
                }

            }
        );

    }
);

</script>


<?php require '../Includes/footer.php'; ?>

