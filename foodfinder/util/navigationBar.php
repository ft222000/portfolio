<?php
/**
 * This file provides a navigation bar which can be used on other pages.
 *
 * It will:
 * -Allow the user to navigate between the pages of the web portal
 * -Allow the user to logout of the web portal
 */

require_once('session.php');

if (!isSustainabilityTeamUser($session_permission)) {
    header("Location: ./login.php");
}
?>

<html>
    <head>
        <!--JA: Bootstrap CSS-->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

        <!--JN: Time Library for dealing with time formats-->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
        <!--JA: Ajax-->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <!--JA: Style for the logo-->
        <style>#logo{width: 120px}</style>

        <!-- JW: Favicons for various browsers and devices -->
        <link rel="apple-touch-icon" sizes="57x57" href="resources/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="resources/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="resources/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="resources/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="resources/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="resources/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="resources/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="resources/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="resources/apple-icon-180x180.png">
        <link rel="icon" type="image/png" sizes="192x192" href="resources/android-icon-192x192.png">
        <link rel="icon" type="image/png" sizes="32x32" href="resources/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="resources/favicon-96x96.png">
        <link rel="icon" type="image/png" sizes="16x16" href="resources/favicon-16x16.png">
        <link rel="manifest" href="resources/manifest.json">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="resources/ms-icon-144x144.png">
        <meta name="theme-color" content="#ffffff">
    </head>

    <body>
        <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
            <!--JA: Logo-->
            <a class="navbar-brand">
                <img class="rounded" id="logo" src="resources/UTAS_LOGO.jpg"  alt="logo">
            </a>

            <!--JA: Page links-->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="./users.php">Users</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./requests.php">Requests</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./events.php">Events</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./feedback.php">Feedback</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./reports.php">Reports</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./admin.php">Admin</a>
                </li>
            </ul>

            <!--JA: Logout link-->
            <ul class="navbar-nav ml-auto">
                <label class="navbar-text"><?php echo $session_user_email; ?> | </label>
                <li class="nav-item">
                    <a class="nav-link" href="./signout.php">Logout</a>
                </li>
            </ul>
        </nav>
    </body>
</html>