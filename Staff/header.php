<?php

$pageTitle =
    $pageTitle ?? 'Staff Portal | LFT Dumaguete';

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


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f5f3ee;

    color: #181818;

}


/* ================================================================
   SIDEBAR
================================================================ */

.staff-sidebar {

    position: fixed;

    top: 0;
    left: 0;
    bottom: 0;

    width: 260px;

    background: #103f32;

    color: #ffffff;

    display: flex;

    flex-direction: column;

    z-index: 1000;

}


/* ================================================================
   LOGO
================================================================ */

.staff-sidebar-top {

    padding: 25px 22px;

    border-bottom:
        1px solid
        rgba(255,255,255,0.12);

}


.staff-sidebar-logo {

    display: flex;

    align-items: center;

    gap: 12px;

    color: #ffffff;

    text-decoration: none;

}


.staff-sidebar-logo img {

    width: 58px;

    height: 58px;

    object-fit: contain;

    padding: 5px;

    background: #ffffff;

    border-radius: 8px;

}


.staff-sidebar-logo strong {

    display: block;

    font-size: 20px;

}


.staff-sidebar-logo span {

    display: block;

    margin-top: 2px;

    font-size: 12px;

    opacity: 0.75;

}


/* ================================================================
   NAVIGATION
================================================================ */

.staff-sidebar-nav {

    flex: 1;

    padding: 22px 14px;

}


.staff-sidebar-nav a,
.staff-sidebar-bottom a {

    display: flex;

    align-items: center;

    gap: 14px;

    padding: 14px 16px;

    margin-bottom: 7px;

    border-radius: 10px;

    text-decoration: none;

    color:
        rgba(255,255,255,0.82);

    font-size: 14px;

    font-weight: 600;

    transition: 0.2s;

}


.staff-sidebar-nav a i,
.staff-sidebar-bottom a i {

    width: 22px;

    text-align: center;

    font-size: 17px;

}


.staff-sidebar-nav a:hover {

    background:
        rgba(255,255,255,0.09);

    color: #ffffff;

}


.staff-sidebar-nav a.active {

    background: #ffffff;

    color: #103f32;

}


/* ================================================================
   LOGOUT
================================================================ */

.staff-sidebar-bottom {

    padding: 15px;

    border-top:
        1px solid
        rgba(255,255,255,0.12);

}


.staff-sidebar-bottom a:hover {

    background:
        rgba(255,255,255,0.09);

    color: #ffffff;

}


/* ================================================================
   MAIN
================================================================ */

.staff-main {

    margin-left: 260px;

    min-height: 100vh;

}


/* ================================================================
   TOP HEADER
================================================================ */

.staff-top-header {

    height: 82px;

    padding: 0 35px;

    background: #ffffff;

    border-bottom:
        1px solid #e5e1da;

    display: flex;

    align-items: center;

    justify-content: space-between;

    position: sticky;

    top: 0;

    z-index: 900;

}


.staff-header-left {

    display: flex;

    align-items: center;

    gap: 15px;

}


.staff-header-left h1 {

    margin: 0;

    font-size: 22px;

}


.staff-menu-button {

    display: none;

    border: none;

    background: transparent;

    font-size: 22px;

    cursor: pointer;

}


.staff-user {

    display: flex;

    align-items: center;

    gap: 12px;

}


.staff-avatar {

    width: 42px;

    height: 42px;

    border-radius: 50%;

    background: #103f32;

    color: #ffffff;

    display: flex;

    align-items: center;

    justify-content: center;

    font-weight: 700;

}


.staff-user-details strong {

    display: block;

    font-size: 14px;

}


.staff-user-details span {

    display: block;

    margin-top: 2px;

    color: #777;

    font-size: 12px;

}


/* ================================================================
   PAGE
================================================================ */

.staff-content {

    padding: 32px;

}


/* ================================================================
   CARDS
================================================================ */

.staff-stats-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 18px;

}


.staff-stat-card {

    background: #ffffff;

    border:
        1px solid
        #e7e3dc;

    border-radius: 14px;

    padding: 22px;

}


.staff-stat-card-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.staff-stat-icon {

    width: 44px;

    height: 44px;

    border-radius: 10px;

    background: #eef4f0;

    color: #104936;

    display: flex;

    align-items: center;

    justify-content: center;

}


.staff-stat-card p {

    color: #777;

    margin:
        0 0
        10px;

    font-size: 13px;

}


.staff-stat-card h2 {

    margin: 0;

    font-size: 29px;

}


/* ================================================================
   TABLE
================================================================ */

.staff-panel {

    margin-top: 25px;

    background: #ffffff;

    border:
        1px solid
        #e7e3dc;

    border-radius: 14px;

    overflow: hidden;

}


.staff-panel-header {

    padding: 20px 22px;

    border-bottom:
        1px solid
        #eeeeee;

    display: flex;

    justify-content: space-between;

    align-items: center;

}


.staff-panel-header h2 {

    margin: 0;

    font-size: 18px;

}


.staff-table-wrap {

    overflow-x: auto;

}


.staff-table {

    width: 100%;

    border-collapse: collapse;

}


.staff-table th,
.staff-table td {

    padding: 14px 18px;

    text-align: left;

    border-bottom:
        1px solid
        #eeeeee;

    white-space: nowrap;

}


.staff-table th {

    background: #faf9f6;

    color: #666;

    font-size: 12px;

    text-transform: uppercase;

}


.staff-table td {

    font-size: 14px;

}


/* ================================================================
   BUTTONS
================================================================ */

.staff-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    padding: 9px 14px;

    border-radius: 8px;

    text-decoration: none;

    border: none;

    cursor: pointer;

    font-weight: 600;

}


.staff-btn-primary {

    background: #104936;

    color: #ffffff;

}


.staff-btn-light {

    background: #f2f2f2;

    color: #333333;

}


/* ================================================================
   MOBILE
================================================================ */

@media (
    max-width: 1000px
) {

    .staff-stats-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (
    max-width: 750px
) {

    .staff-sidebar {

        transform:
            translateX(-100%);

        transition:
            transform 0.25s;

    }


    .staff-sidebar.open {

        transform:
            translateX(0);

    }


    .staff-main {

        margin-left: 0;

    }


    .staff-menu-button {

        display: block;

    }


    .staff-content {

        padding: 20px;

    }


    .staff-user-details {

        display: none;

    }

}


@media (
    max-width: 500px
) {

    .staff-stats-grid {

        grid-template-columns: 1fr;

    }


    .staff-top-header {

        padding:
            0 18px;

    }

}

</style>

</head>


<body>