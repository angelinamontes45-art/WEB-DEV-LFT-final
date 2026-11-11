<?php

/*
|--------------------------------------------------------------------------
| STAFF SIDEBAR
|--------------------------------------------------------------------------
| Expected variables from the staff page / _guard.php:
|
| $staff['name']
| $staff['email']
| $currentStaffPage
|
*/

$currentStaffPage = $currentStaffPage ?? '';

$staffName = $staff['name'] ?? 'Staff';
$staffEmail = $staff['email'] ?? 'staff@lftdumaguete.com';

$staffInitial = strtoupper(
    substr(trim($staffName), 0, 1)
);


/*
|--------------------------------------------------------------------------
| SIDEBAR MENU
|--------------------------------------------------------------------------
*/

$staffMenu = [

    [
        'key'  => 'dashboard',
        'label' => 'Dashboard',
        'icon' => 'fa-solid fa-chart-pie',
        'href' => 'index.php'
    ],

    [
        'key'  => 'bookings',
        'label' => 'Bookings',
        'icon' => 'fa-solid fa-calendar-check',
        'href' => 'bookings.php'
    ],

    [
        'key'  => 'payments',
        'label' => 'Payments',
        'icon' => 'fa-solid fa-wallet',
        'href' => 'payments.php'
    ],

    [
        'key'  => 'checkins',
        'label' => 'Check-ins',
        'icon' => 'fa-solid fa-right-to-bracket',
        'href' => 'checkins.php'
    ],

    [
        'key'  => 'customers',
        'label' => 'Customers',
        'icon' => 'fa-solid fa-users',
        'href' => 'customers.php'
    ]

];

?>

<!-- ================================================================
     STAFF SIDEBAR
================================================================ -->

<aside
    class="staff-sidebar"
    id="staffSidebar"
>

    <!-- ============================================================
         BRAND / LOGO
    ============================================================= -->

    <div class="staff-sidebar-header">

        <a
            href="index.php"
            class="staff-sidebar-brand"
        >

            <div class="staff-brand-logo">

                <img
                    src="../assets/images/Logo.png"
                    alt="LFT Dumaguete"
                >

            </div>

            <div class="staff-brand-text">

                <strong>
                    LFT Dumaguete
                </strong>

                <span>
                    Staff Portal
                </span>

            </div>

        </a>


        <!-- MOBILE CLOSE -->

        <button
            type="button"
            class="staff-sidebar-close"
            id="staffSidebarClose"
            aria-label="Close sidebar"
        >

            <i class="fa-solid fa-xmark"></i>

        </button>

    </div>


    <!-- ============================================================
         STAFF PROFILE
    ============================================================= -->

    <div class="staff-sidebar-profile">

        <div class="staff-sidebar-avatar">

            <?= e($staffInitial) ?>

        </div>


        <div class="staff-sidebar-profile-info">

            <strong title="<?= e($staffName) ?>">

                <?= e($staffName) ?>

            </strong>

            <span title="<?= e($staffEmail) ?>">

                <?= e($staffEmail) ?>

            </span>

        </div>

    </div>


    <!-- ============================================================
         MENU
    ============================================================= -->

    <nav class="staff-sidebar-nav">

        <div class="staff-nav-title">
            MAIN MENU
        </div>


        <?php foreach ($staffMenu as $menu): ?>

            <?php

            /*
            |--------------------------------------------------------------------------
            | FILE CHECK
            |--------------------------------------------------------------------------
            | If a staff file has not been created yet, do not display the link.
            */

            $menuFile =
                __DIR__ . '/' . basename($menu['href']);

            $menuExists =
                file_exists($menuFile);

            ?>

            <?php if ($menuExists): ?>

                <a
                    href="<?= e($menu['href']) ?>"
                    class="staff-nav-link
                    <?= $currentStaffPage === $menu['key']
                        ? 'active'
                        : ''
                    ?>"
                >

                    <span class="staff-nav-icon">

                        <i
                            class="<?= e($menu['icon']) ?>"
                        ></i>

                    </span>


                    <span class="staff-nav-text">

                        <?= e($menu['label']) ?>

                    </span>


                    <?php if (
                        $currentStaffPage === $menu['key']
                    ): ?>

                        <span class="staff-active-dot"></span>

                    <?php endif; ?>

                </a>

            <?php endif; ?>

        <?php endforeach; ?>


        <!-- ========================================================
             ACCOUNT
        ========================================================= -->

        <div class="staff-nav-title staff-nav-account-title">

            ACCOUNT

        </div>


        <!-- WEBSITE -->

        <a
            href="../index.php"
            class="staff-nav-link"
            target="_blank"
            rel="noopener noreferrer"
        >

            <span class="staff-nav-icon">

                <i class="fa-solid fa-globe"></i>

            </span>

            <span class="staff-nav-text">

                View Website

            </span>


            <span class="staff-nav-extra-icon">

                <i class="fa-solid fa-arrow-up-right-from-square"></i>

            </span>

        </a>

    </nav>


    <!-- ============================================================
         LOGOUT
    ============================================================= -->

    <div class="staff-sidebar-footer">

        <?php if (file_exists(__DIR__ . '/logout.php')): ?>

            <a
                href="logout.php"
                class="staff-sidebar-logout"
            >

                <span class="staff-nav-icon">

                    <i
                        class="fa-solid fa-arrow-right-from-bracket"
                    ></i>

                </span>

                <span>
                    Logout
                </span>

            </a>

        <?php endif; ?>


        <div class="staff-sidebar-version">

            LFT Staff System

            <span>
                © <?= date('Y') ?>
            </span>

        </div>

    </div>

</aside>


<!-- ================================================================
     MOBILE OVERLAY
================================================================ -->

<div
    class="staff-sidebar-overlay"
    id="staffSidebarOverlay"
></div>


<style>

/* ================================================================
   ROOT
================================================================ */

:root {

    --staff-green-dark: #103f32;

    --staff-green:
        #165542;

    --staff-green-light:
        #236c56;

    --staff-gold:
        #c99850;

    --staff-white:
        #ffffff;

    --staff-sidebar-width:
        270px;

}


/* ================================================================
   SIDEBAR
================================================================ */

.staff-sidebar {

    position: fixed;

    top: 0;

    left: 0;

    bottom: 0;

    width:
        var(--staff-sidebar-width);

    display: flex;

    flex-direction: column;

    background:
        var(--staff-green-dark);

    color:
        var(--staff-white);

    z-index: 1200;

    overflow-x: hidden;

    overflow-y: auto;

    box-shadow:
        4px 0 24px
        rgba(0, 0, 0, 0.12);

    transition:
        transform 0.25s ease;

}


/* SCROLLBAR */

.staff-sidebar::-webkit-scrollbar {

    width: 5px;

}


.staff-sidebar::-webkit-scrollbar-thumb {

    background:
        rgba(255, 255, 255, 0.18);

    border-radius: 20px;

}


/* ================================================================
   HEADER
================================================================ */

.staff-sidebar-header {

    min-height: 94px;

    padding:
        17px
        16px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    border-bottom:
        1px solid
        rgba(255, 255, 255, 0.10);

}


/* BRAND */

.staff-sidebar-brand {

    min-width: 0;

    display: flex;

    align-items: center;

    gap: 11px;

    color:
        #ffffff;

    text-decoration: none;

}


/* LOGO */

.staff-brand-logo {

    width: 55px;

    height: 55px;

    min-width: 55px;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        #ffffff;

    border-radius: 11px;

    overflow: hidden;

}


.staff-brand-logo img {

    width: 100%;

    height: 100%;

    display: block;

    object-fit: contain;

    padding: 5px;

}


/* BRAND TEXT */

.staff-brand-text {

    min-width: 0;

}


.staff-brand-text strong {

    display: block;

    color:
        #ffffff;

    font-size: 14px;

    font-weight: 800;

    line-height: 1.2;

}


.staff-brand-text span {

    display: block;

    margin-top: 4px;

    color:
        rgba(255, 255, 255, 0.58);

    font-size: 10px;

    font-weight: 600;

    letter-spacing:
        0.7px;

    text-transform: uppercase;

}


/* MOBILE CLOSE */

.staff-sidebar-close {

    display: none;

    width: 37px;

    height: 37px;

    flex-shrink: 0;

    align-items: center;

    justify-content: center;

    border: 0;

    border-radius: 8px;

    background:
        rgba(255, 255, 255, 0.08);

    color:
        #ffffff;

    cursor: pointer;

    font-size: 16px;

}


.staff-sidebar-close:hover {

    background:
        rgba(255, 255, 255, 0.15);

}


/* ================================================================
   STAFF PROFILE
================================================================ */

.staff-sidebar-profile {

    margin:
        16px
        13px
        6px;

    padding: 13px;

    display: flex;

    align-items: center;

    gap: 11px;

    border:
        1px solid
        rgba(255, 255, 255, 0.07);

    border-radius: 12px;

    background:
        rgba(255, 255, 255, 0.06);

}


.staff-sidebar-avatar {

    width: 44px;

    height: 44px;

    min-width: 44px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background:
        #ffffff;

    color:
        var(--staff-green-dark);

    font-size: 17px;

    font-weight: 800;

}


.staff-sidebar-profile-info {

    min-width: 0;

    flex: 1;

}


.staff-sidebar-profile-info strong {

    display: block;

    max-width: 155px;

    overflow: hidden;

    color: #ffffff;

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;

    text-overflow: ellipsis;

}


.staff-sidebar-profile-info span {

    display: block;

    max-width: 155px;

    margin-top: 4px;

    overflow: hidden;

    color:
        rgba(255, 255, 255, 0.55);

    font-size: 10px;

    white-space: nowrap;

    text-overflow: ellipsis;

}


/* ================================================================
   NAV
================================================================ */

.staff-sidebar-nav {

    flex: 1;

    padding:
        12px
        12px
        20px;

}


.staff-nav-title {

    padding:
        11px
        13px
        9px;

    color:
        rgba(255, 255, 255, 0.38);

    font-size: 9px;

    font-weight: 800;

    letter-spacing:
        1.7px;

}


.staff-nav-account-title {

    margin-top: 20px;

}


/* LINKS */

.staff-nav-link {

    position: relative;

    min-height: 49px;

    margin-bottom: 5px;

    padding:
        0
        13px;

    display: flex;

    align-items: center;

    gap: 12px;

    border-radius: 10px;

    color:
        rgba(255, 255, 255, 0.75);

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    transition:
        background 0.18s ease,
        color 0.18s ease,
        transform 0.18s ease;

}


.staff-nav-link:hover {

    background:
        rgba(255, 255, 255, 0.08);

    color:
        #ffffff;

    transform:
        translateX(2px);

}


/* ICON */

.staff-nav-icon {

    width: 25px;

    min-width: 25px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 15px;

}


/* TEXT */

.staff-nav-text {

    flex: 1;

}


/* ACTIVE */

.staff-nav-link.active {

    background:
        #ffffff;

    color:
        var(--staff-green-dark);

    font-weight: 700;

    box-shadow:
        0 5px 15px
        rgba(0, 0, 0, 0.08);

}


.staff-nav-link.active::before {

    content: "";

    position: absolute;

    left: 0;

    top: 10px;

    bottom: 10px;

    width: 4px;

    border-radius:
        0 5px 5px 0;

    background:
        var(--staff-gold);

}


.staff-active-dot {

    width: 6px;

    height: 6px;

    margin-left: auto;

    border-radius: 50%;

    background:
        var(--staff-green-dark);

}


/* EXTERNAL ICON */

.staff-nav-extra-icon {

    margin-left: auto;

    color:
        rgba(255, 255, 255, 0.35);

    font-size: 9px;

}


/* ================================================================
   FOOTER
================================================================ */

.staff-sidebar-footer {

    padding:
        15px
        12px
        16px;

    border-top:
        1px solid
        rgba(255, 255, 255, 0.10);

}


/* LOGOUT */

.staff-sidebar-logout {

    min-height: 48px;

    padding:
        0
        13px;

    display: flex;

    align-items: center;

    gap: 12px;

    border-radius: 10px;

    background:
        rgba(255, 255, 255, 0.07);

    color:
        #ffffff;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

    transition:
        background 0.18s ease,
        color 0.18s ease;

}


.staff-sidebar-logout:hover {

    background:
        #ffffff;

    color:
        #a93232;

}


/* VERSION */

.staff-sidebar-version {

    margin-top: 13px;

    display: flex;

    justify-content: space-between;

    color:
        rgba(255, 255, 255, 0.30);

    font-size: 8px;

    letter-spacing:
        0.4px;

}


/* ================================================================
   MAIN CONTENT POSITION
================================================================ */

.staff-main {

    min-height: 100vh;

    margin-left:
        var(--staff-sidebar-width);

}


/* ================================================================
   OVERLAY
================================================================ */

.staff-sidebar-overlay {

    position: fixed;

    inset: 0;

    display: none;

    background:
        rgba(0, 0, 0, 0.48);

    backdrop-filter:
        blur(2px);

    z-index: 1100;

}


/* ================================================================
   MOBILE
================================================================ */

@media (
    max-width: 800px
) {

    .staff-sidebar {

        transform:
            translateX(-100%);

    }


    .staff-sidebar.open {

        transform:
            translateX(0);

    }


    .staff-sidebar-close {

        display: flex;

    }


    .staff-sidebar-overlay.show {

        display: block;

    }


    .staff-main {

        margin-left: 0;

    }

}


/* ================================================================
   SMALL MOBILE
================================================================ */

@media (
    max-width: 420px
) {

    .staff-sidebar {

        width: 86%;

        max-width: 290px;

    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const sidebar =
            document.getElementById(
                'staffSidebar'
            );

        const overlay =
            document.getElementById(
                'staffSidebarOverlay'
            );

        const closeButton =
            document.getElementById(
                'staffSidebarClose'
            );

        const menuButton =
            document.getElementById(
                'staffMenuButton'
            );


        /*
        |--------------------------------------------------------------------------
        | OPEN
        |--------------------------------------------------------------------------
        */

        function openSidebar() {

            sidebar?.classList.add(
                'open'
            );

            overlay?.classList.add(
                'show'
            );

            document.body.classList.add(
                'staff-sidebar-open'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | CLOSE
        |--------------------------------------------------------------------------
        */

        function closeSidebar() {

            sidebar?.classList.remove(
                'open'
            );

            overlay?.classList.remove(
                'show'
            );

            document.body.classList.remove(
                'staff-sidebar-open'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | MENU BUTTON
        |--------------------------------------------------------------------------
        */

        menuButton?.addEventListener(
            'click',
            function () {

                if (
                    sidebar?.classList.contains(
                        'open'
                    )
                ) {

                    closeSidebar();

                } else {

                    openSidebar();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | CLOSE BUTTON
        |--------------------------------------------------------------------------
        */

        closeButton?.addEventListener(
            'click',
            closeSidebar
        );


        /*
        |--------------------------------------------------------------------------
        | OVERLAY
        |--------------------------------------------------------------------------
        */

        overlay?.addEventListener(
            'click',
            closeSidebar
        );


        /*
        |--------------------------------------------------------------------------
        | ESCAPE KEY
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape'
                ) {

                    closeSidebar();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | MOBILE LINK CLICK
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.staff-nav-link'
            )
            .forEach(
                function (link) {

                    link.addEventListener(
                        'click',
                        function () {

                            if (
                                window.innerWidth
                                <= 800
                            ) {

                                closeSidebar();

                            }

                        }
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | WINDOW RESIZE
        |--------------------------------------------------------------------------
        */

        window.addEventListener(
            'resize',
            function () {

                if (
                    window.innerWidth
                    > 800
                ) {

                    closeSidebar();

                }

            }
        );

    }
);

</script>