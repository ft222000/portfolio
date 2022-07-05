<?php
require_once ("session.php");
/**
 * Provides helper functions for other user based scripts
 */

    /**
    ** Generates a 64 character salt.
     *
     * @return The new random salt.
    */
    //generate 64 char salt
    function GetSalt() { //https://codereview.stackexchange.com/questions/92869/php-salt-generator
        $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; //the following characters cause the password hash to change when logging back in. can't be bothered looking into it atm   /\\][{}\'";:?.>,<!@#$%^&*()-_=+|
        $randStringLen = 64;
   
        $randString = "";
        for ($i = 0; $i < $randStringLen; $i++) {
            $randString .= $charset[mt_rand(0, strlen($charset) - 1)];
        }
   
        return $randString;
   }

    /**
    * Checks if a permission level is within the expected range
    *
    * @PARAM perm - The permission level being checked
    *
    * @RETURN true || false - Is the page valid
    */
    function CheckPermissionLevel($perm) {
        if ($perm > 0 && $perm < 4) {
            return true;
        } else {
            return false;
        }
    }
?>