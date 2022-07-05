 <?php
/**
 * Allows the user to login to the web portal.
 *
 * Successful login attempts are added to the session and redirected to the user page.
 *
 * Accepts both post and get requests.
 *
 * POST Requests: Checks to see if the user has mad a login attempt, and confirms if it was successful
 * @param login Must be set to any value, signals that a login attempt is being made.
 * @param email The email address of the user attempting to login.
 * @param password The password of the user attempting to login.
 *
 * The page will always respond with the login page for the web portal on unsuccessful attempts, or regular browsing.
 *
 */
//require_once('util/verboseLogging.php');
require_once('util/Dbconnection.php');
require_once('util/session.php');

// JN: POST request check for login requests
if (isset($_POST['login'])) {
    require_once('util/loginManagement.php');
    if(LoginUser($_POST['email'], $_POST['password'], $connection)) {
        header("Location: ./users.php");
    }
}

// JN: Redirect if the user is logged in
if (IsSustainabilityTeamUser($session_permission))
{
    header("Location:./users.php");
}

// JN: HTML follows
?>

<html>
    <head>
        <!--JN: Import for Bootstrap 4-->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
        <!--JA: Style for the logo-->
        <!--JH: Font Styling-->
        <link rel="stylesheet" href="styles/Font.css" />
        <link rel="stylesheet" href="styles/Login.css" />

        <!--JA: Style for the logo-->
        <style>#logo{width: 240px; max-height: 10%}</style>

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

        <title>Food Finder</title>
    </head>

    <body class="container-fluid">
        <div class="mt-5 mb-5">
            <!--JA: UTas Logo-->
            <div class="row justify-content-center">
                <img class="img-fluid m-auto" id="logo" src="resources/UTAS-Colour-Horizontal.png" alt="logo">
            </div>

            <!--JA: Page heading-->
            <div class="row justify-content-center">
                <h3 class="text-center">Food Finder</h3>
            </div>

            <!--JA: Sustainability team motto-->
            <div class="row justify-content-center">
                <label class="text-center">Simple Actions Towards Sustainability</label>
            </div>
        </div>

        <!--JA: Login details-->
        <div class="row justify-content-center align-items-center">
            <form action="./login.php" method="post">
                <!--JA: Email address-->
                <div class="form-group">
                    <input class="form-control" id="emailInput" type="email" name="email" placeholder="Email Address">
                </div>

                <!--JA: Password-->
                <div class="form-group">
                    <input class="form-control" id="passwordInput" type="password" name="password" placeholder="Password">
                </div>

                <!--JA: Login button-->
                <div class="form-group text-center">
                    <button id="loginButton" class="btn btn-primary" type="submit" name="login" value="Login">Login</button>
                </div>
                <div class="form-group text-center">
                    <button type="button" id="forgotPasswordButton" class="btn btn-danger" data-toggle="modal" data-target="#forgotPasswordModal">Forgot Password</button>
                </div>
            </form>
        </div>

        <div class="modal fade" id="forgotPasswordModal" tabindex="-1" role="dialog" aria-labelledby="forgotPasswordTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="forgotPasswordTitle">Forgot Password</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div id="forgotPasswordContent" class="modal-body">
                        <form id="forgotPasswordForm">
                            <!--JA: Email address-->
                            <div class="form-group">
                                <input class="form-control" id="forgotEmail" type="email" name="EmailAddress" placeholder="Email Address">
                            </div>

                            <div class="form-group text-center">
                                <button id="forgotPasswordSubmit" type="button" class="btn btn-primary">Reset My Password</button>
                            </div>
                        </form>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script>
        var threadLock = false;

        /**
         * Document on load
         *
         * Adds handlers for the forgot password functionality.
         */
        $(function() {
            // Fix some size of buttons to be the same
            $('#loginButton').width($('#forgotPasswordButton').width());

            $('#forgotPasswordSubmit').click(function() {
                threadLock = true;
                let data = $('#forgotPasswordForm').serialize();
                $("#forgotPasswordContent").html("Processing..");
                $.post("api/ForgotPassword.php", data , function(data, status) {
                    // You can add additional error checking here, and it will not be repeated until the first has completed.
                    threadLock = false;
                    $('#forgotPasswordContent').html("Email sent!");
                    console.log(data);
                });
            });
        });
    </script>
</html>