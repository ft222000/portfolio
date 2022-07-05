<?php
/**
 * Logs the user out of the application when navigated to.
 *
 * After logging the user out, they are taken back to the login screen.
 */

require_once('util/loginManagement.php');
Signout();
header("Location: ./login.php");
?>
