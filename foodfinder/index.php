<!DOCTYPE html>
<?php
/**
 * Index file
 *
 * Redirects users based on whether or not they are logged in.
 */

require_once('util/session.php');
if (isSustainabilityTeamUser($session_permission)) {
    header("Location: ./users.php");
} else {
    header("Location: ./login.php");
}
?>












