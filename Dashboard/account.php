<?php

require '../Includes/db.php';

$user = requireUser();
$connection = db();

$errors = [];
$success = '';

/*
|--------------------------------------------------------------------------
| GET CURRENT USER
|--------------------------------------------------------------------------
*/

$statement = $connection->prepare(
    'SELECT *
     FROM users
     WHERE id = ?
     LIMIT 1'
);

$statement->execute([
    $user['id']
]);

$account = $statement->fetch(PDO::FETCH_ASSOC);

if (!$account) {
    die('User account not found.');
}


/*
|--------------------------------------------------------------------------
| HANDLE FORM SUBMISSION
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    verifyCsrf();

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    /*
    |--------------------------------------------------------------------------
    | VALIDATE NAME
    |--------------------------------------------------------------------------
    */

    if ($name === '') {

        $errors[] = 'Please enter your name.';

    } elseif (strlen($name) > 100) {

        $errors[] = 'Name is too long.';
    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE EMAIL
    |--------------------------------------------------------------------------
    */

    if ($email === '') {

        $errors[] = 'Please enter your email address.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = 'Please enter a valid email address.';
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK IF EMAIL IS ALREADY USED
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        $emailCheck = $connection->prepare(
            'SELECT id
             FROM users
             WHERE email = ?
             AND id != ?
             LIMIT 1'
        );

        $emailCheck->execute([
            $email,
            $user['id']
        ]);

        if ($emailCheck->fetch()) {

            $errors[] =
                'That email address is already being used.';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CURRENT PROFILE IMAGE
    |--------------------------------------------------------------------------
    */

    $profileImage =
        $account['profile_image'] ?? null;


    /*
    |--------------------------------------------------------------------------
    | PROFILE IMAGE UPLOAD
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['profile_image']) &&
        $_FILES['profile_image']['error']
            !== UPLOAD_ERR_NO_FILE
    ) {

        $image = $_FILES['profile_image'];


        /*
        |--------------------------------------------------------------------------
        | CHECK UPLOAD ERROR
        |--------------------------------------------------------------------------
        */

        if ($image['error'] !== UPLOAD_ERR_OK) {

            $errors[] =
                'There was a problem uploading your image.';

        } else {

            /*
            |--------------------------------------------------------------------------
            | MAXIMUM FILE SIZE = 5 MB
            |--------------------------------------------------------------------------
            */

            $maxFileSize = 5 * 1024 * 1024;

            if ($image['size'] > $maxFileSize) {

                $errors[] =
                    'Profile image must be 5 MB or smaller.';
            }


            /*
            |--------------------------------------------------------------------------
            | CHECK REAL IMAGE TYPE
            |--------------------------------------------------------------------------
            */

            if (!$errors) {

                $fileInfo = new finfo(
                    FILEINFO_MIME_TYPE
                );

                $mimeType = $fileInfo->file(
                    $image['tmp_name']
                );


                $allowedTypes = [

                    'image/jpeg' => 'jpg',

                    'image/png' => 'png',

                    'image/webp' => 'webp'

                ];


                if (!isset(
                    $allowedTypes[$mimeType]
                )) {

                    $errors[] =
                        'Only JPG, PNG, and WebP images are allowed.';

                } else {

                    $extension =
                        $allowedTypes[$mimeType];


                    /*
                    |--------------------------------------------------------------------------
                    | CREATE UPLOAD DIRECTORY
                    |--------------------------------------------------------------------------
                    */

                    $uploadDirectory =
                        dirname(__DIR__)
                        . '/assets/uploads/profiles/';


                    if (!is_dir(
                        $uploadDirectory
                    )) {

                        if (
                            !mkdir(
                                $uploadDirectory,
                                0755,
                                true
                            )
                        ) {

                            $errors[] =
                                'Unable to create profile image folder.';
                        }
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CREATE RANDOM SAFE FILE NAME
                    |--------------------------------------------------------------------------
                    */

                    if (!$errors) {

                        try {

                            $randomName =
                                bin2hex(
                                    random_bytes(16)
                                );

                        } catch (Exception $exception) {

                            $randomName =
                                uniqid(
                                    'profile_',
                                    true
                                );
                        }


                        $newFileName =
                            'user_'
                            . (int)$user['id']
                            . '_'
                            . $randomName
                            . '.'
                            . $extension;


                        $destination =
                            $uploadDirectory
                            . $newFileName;


                        /*
                        |--------------------------------------------------------------------------
                        | MOVE IMAGE
                        |--------------------------------------------------------------------------
                        */

                        if (
                            move_uploaded_file(
                                $image['tmp_name'],
                                $destination
                            )
                        ) {

                            /*
                            |--------------------------------------------------------------------------
                            | DELETE OLD PROFILE IMAGE
                            |--------------------------------------------------------------------------
                            */

                            if (
                                !empty($profileImage)
                            ) {

                                $oldImage =
                                    $uploadDirectory
                                    . basename(
                                        $profileImage
                                    );


                                if (
                                    is_file($oldImage)
                                ) {

                                    unlink(
                                        $oldImage
                                    );
                                }
                            }


                            $profileImage =
                                $newFileName;

                        } else {

                            $errors[] =
                                'Unable to save the profile image.';
                        }
                    }
                }
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE USER
    |--------------------------------------------------------------------------
    */

    if (!$errors) {

        $update = $connection->prepare(
            'UPDATE users
             SET
                name = ?,
                email = ?,
                profile_image = ?
             WHERE id = ?'
        );


        $update->execute([

            $name,

            $email,

            $profileImage,

            $user['id']

        ]);


        $success =
            'Your account has been updated successfully.';


        /*
        |--------------------------------------------------------------------------
        | REFRESH USER INFORMATION
        |--------------------------------------------------------------------------
        */

        $statement->execute([
            $user['id']
        ]);

        $account =
            $statement->fetch(
                PDO::FETCH_ASSOC
            );
    }
}


/*
|--------------------------------------------------------------------------
| PROFILE IMAGE URL
|--------------------------------------------------------------------------
*/

if (!empty($account['profile_image'])) {

    $profileImageUrl =
        '../assets/uploads/profiles/'
        . rawurlencode(
            basename(
                $account['profile_image']
            )
        );

} else {

    $profileImageUrl = '';
}


/*
|--------------------------------------------------------------------------
| USER INITIAL
|--------------------------------------------------------------------------
*/

$userInitial = strtoupper(
    substr(
        trim($account['name'] ?? 'U'),
        0,
        1
    )
);


$pageTitle = 'My Account | LFT Dumaguete';
$currentPage = 'ACCOUNT';

require '../Includes/header.php';

?>


<main>

<section class="auth-section">

<div class="account-container">


    <!-- ================================================================
         ACCOUNT HEADER
    ================================================================= -->

    <div class="account-heading">

        <p class="section-label">
            MY ACCOUNT
        </p>

        <h1>
            Manage your profile
        </h1>

        <p>
            Update your personal information
            and profile picture.
        </p>

    </div>


    <!-- ================================================================
         SUCCESS MESSAGE
    ================================================================= -->

    <?php if ($success !== ''): ?>

        <div class="success-message">

            <i class="fa-solid fa-circle-check"></i>

            <?= e($success) ?>

        </div>

    <?php endif; ?>


    <!-- ================================================================
         ERROR MESSAGES
    ================================================================= -->

    <?php foreach ($errors as $error): ?>

        <div class="form-error">

            <?= e($error) ?>

        </div>

    <?php endforeach; ?>


    <!-- ================================================================
         PROFILE FORM
    ================================================================= -->

    <form
        method="post"
        enctype="multipart/form-data"
        class="account-card"
    >

        <?= csrfField() ?>


        <!-- ============================================================
             PROFILE PICTURE
        ============================================================= -->

        <div class="profile-picture-section">


            <div
                class="profile-picture"
                id="profilePicture"
            >


                <?php if (
                    $profileImageUrl !== ''
                ): ?>

                    <img
                        src="<?= e($profileImageUrl) ?>"
                        alt="Profile picture"
                        id="profilePreview"
                    >


                    <span
                        id="profileInitial"
                        style="display:none;"
                    >
                        <?= e($userInitial) ?>
                    </span>


                <?php else: ?>


                    <img
                        src=""
                        alt="Profile preview"
                        id="profilePreview"
                        style="display:none;"
                    >


                    <span id="profileInitial">

                        <?= e($userInitial) ?>

                    </span>


                <?php endif; ?>


            </div>


            <div class="profile-upload-info">


                <h2>
                    Profile Picture
                </h2>


                <p>
                    Upload a JPG, PNG, or WebP image.
                    Maximum file size is 5 MB.
                </p>


                <label
                    for="profile_image"
                    class="btn upload-button"
                >

                    <i class="fa-solid fa-camera"></i>

                    CHOOSE IMAGE

                </label>


                <input
                    id="profile_image"
                    name="profile_image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    hidden
                >


                <span
                    class="selected-file"
                    id="selectedFile"
                >

                    No new image selected

                </span>


            </div>


        </div>


        <!-- ============================================================
             ACCOUNT DETAILS
        ============================================================= -->

        <div class="account-form-section">


            <h2>
                Personal Information
            </h2>


            <div class="form-group">


                <label for="name">
                    Full Name
                </label>


                <input
                    id="name"
                    name="name"
                    type="text"
                    maxlength="100"
                    value="<?= e(
                        $account['name']
                    ) ?>"
                    required
                >


            </div>


            <div class="form-group">


                <label for="email">
                    Email Address
                </label>


                <input
                    id="email"
                    name="email"
                    type="email"
                    maxlength="255"
                    value="<?= e(
                        $account['email']
                    ) ?>"
                    required
                >


            </div>


        </div>


        <!-- ============================================================
             SAVE
        ============================================================= -->

        <div class="account-actions">


            <button
                type="submit"
                class="btn btn-green"
            >

                <i class="fa-solid fa-floppy-disk"></i>

                SAVE CHANGES

            </button>


            <a
                href="index.php"
                class="btn"
            >

                CANCEL

            </a>


        </div>


    </form>


</div>

</section>

</main>


<style>

/* ================================================================
   ACCOUNT PAGE
================================================================ */

.account-container {

    width: 100%;

    max-width: 800px;

    margin: 0 auto;

}


.account-heading {

    margin-bottom: 25px;

}


.account-heading h1 {

    margin-bottom: 8px;

}


/* ================================================================
   ACCOUNT CARD
================================================================ */

.account-card {

    width: 100%;

    background: #ffffff;

    border: 1px solid #e5e5e5;

    border-radius: 18px;

    padding: 30px;

    box-sizing: border-box;

}


/* ================================================================
   PROFILE PICTURE
================================================================ */

.profile-picture-section {

    display: flex;

    align-items: center;

    gap: 25px;

    padding-bottom: 30px;

    margin-bottom: 30px;

    border-bottom: 1px solid #eeeeee;

}


.profile-picture {

    width: 130px;

    height: 130px;

    min-width: 130px;

    border-radius: 50%;

    overflow: hidden;

    background: #edf3ed;

    border: 4px solid #ffffff;

    box-shadow: 0 0 0 1px #dddddd;

    display: flex;

    align-items: center;

    justify-content: center;

}


.profile-picture img {

    width: 100%;

    height: 100%;

    object-fit: cover;

}


.profile-picture span {

    font-size: 45px;

    font-weight: 700;

    color: #315c3a;

}


.profile-upload-info {

    flex: 1;

}


.profile-upload-info h2 {

    margin: 0 0 8px;

}


.profile-upload-info p {

    color: #777777;

    margin: 0 0 15px;

    line-height: 1.5;

}


.upload-button {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    cursor: pointer;

}


.selected-file {

    display: block;

    margin-top: 10px;

    font-size: 13px;

    color: #777777;

}


/* ================================================================
   FORM
================================================================ */

.account-form-section h2 {

    margin-bottom: 20px;

}


.form-group {

    margin-bottom: 20px;

}


.form-group label {

    display: block;

    margin-bottom: 7px;

    font-weight: 600;

}


.form-group input {

    width: 100%;

    box-sizing: border-box;

}


/* ================================================================
   ACTIONS
================================================================ */

.account-actions {

    display: flex;

    gap: 12px;

    flex-wrap: wrap;

    margin-top: 30px;

}


/* ================================================================
   MOBILE
================================================================ */

@media (max-width: 600px) {

    .account-card {

        padding: 22px;

    }


    .profile-picture-section {

        flex-direction: column;

        text-align: center;

    }


    .profile-upload-info {

        width: 100%;

    }


    .account-actions {

        flex-direction: column;

    }


    .account-actions .btn {

        width: 100%;

        text-align: center;

        justify-content: center;

    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const input =
            document.getElementById(
                'profile_image'
            );

        const preview =
            document.getElementById(
                'profilePreview'
            );

        const initial =
            document.getElementById(
                'profileInitial'
            );

        const selectedFile =
            document.getElementById(
                'selectedFile'
            );


        input.addEventListener(
            'change',
            function () {

                if (
                    !this.files ||
                    !this.files[0]
                ) {

                    return;
                }


                const file =
                    this.files[0];


                /*
                |--------------------------------------------------------------------------
                | CHECK FILE SIZE IN BROWSER
                |--------------------------------------------------------------------------
                */

                const maxSize =
                    5 * 1024 * 1024;


                if (
                    file.size > maxSize
                ) {

                    alert(
                        'Image must be 5 MB or smaller.'
                    );

                    this.value = '';

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | SHOW FILE NAME
                |--------------------------------------------------------------------------
                */

                selectedFile.textContent =
                    file.name;


                /*
                |--------------------------------------------------------------------------
                | PREVIEW IMAGE
                |--------------------------------------------------------------------------
                */

                const reader =
                    new FileReader();


                reader.onload =
                    function (event) {

                        preview.src =
                            event.target.result;

                        preview.style.display =
                            'block';

                        initial.style.display =
                            'none';

                    };


                reader.readAsDataURL(
                    file
                );

            }
        );

    }
);

</script>


<?php

require '../Includes/footer.php';

?>