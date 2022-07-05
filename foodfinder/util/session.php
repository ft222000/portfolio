<?php
/**
 * Handles the session for any page it is included on.
 * Should be included on any page/script that requires access to session variables
 *
 * After running this script, you have access to the following variables
 * session_user_id - The ID of the user in the database
 * session_user_email - The Email address of the logged in user in the database
 * session_permission - The permission level of the user (Sustainability Team should always 3)
 */

// The ID of the SUSTEAM level in the database, to save an additional SQL request
define("SUSTEAM", 3);

// Start the session
session_start();

// If there is no session_access variable set, set all variables to default state
if(!isset($_SESSION['session_permission']))
{
    $_SESSION['session_user_id'] = -1;
    $_SESSION['session_user_email']=""; // The email address of the logged in user
    $_SESSION['session_permission'] = 0; // The permission level of the user (0 being none)

}

// Store these variables in some super handy variables
$session_user_id = $_SESSION['session_user_id'];
$session_user_email = $_SESSION['session_user_email'];
$session_permission = $_SESSION['session_permission'];

// Helper function definitions follow

/**
 * IsSustainabilityTeamUser
 *
 * @param $permission_level - The permission level of the user
 * Note: Use $session_permission for checking the logged in user.
 * Was not hardcoded so that this function can be used to check other users as well.
 *
 * @return BOOL - True if the user us a member of the Sustainability Team, False otherwise.
 */
function IsSustainabilityTeamUser($permission_level)
{
    return  $permission_level == SUSTEAM;
}
?>