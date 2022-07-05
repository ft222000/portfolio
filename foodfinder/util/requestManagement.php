<?php
/**
 * This function will take the response to the request, and return an SQL statement ready to be used.
 *
 * @param $id - The user ID of the selected user.
 * @param $newPermissionLevel - The new permission level based on the response to the request
 *
 * @return string -  An SQL update query
 */
function GetUpdateStatement ($id, $newPermissionLevel) {
    $requestState = 0;

    // JA: Build the query using the provided variables
    $query = "UPDATE `User` SET `RequestedOrganiserPrivileges` = '$requestState', `HasPermissionLevel` = '$newPermissionLevel' WHERE `User_ID` = '$id'";

    return $query;
}
