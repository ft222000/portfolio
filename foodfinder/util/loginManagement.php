<?php
/**
 * Contains helper functions for the login process
 */

// JN: Sessions are required by this document
require_once('session.php');
require_once('util/Dbconnection.php');

/**
 * LoginUser
 *
 * Will check a given username and password, against entries in the database connection supplied
 *
 * @PARAM userEmail - The email associated with the user
 * @PARAM password - The plain text password of the user
 * NOTE: The password should be transmitted over HTTPS to ensure security
 * @PARAM connection - The mySQLi connection being used
 *
 * @RETURN bool - True on success, False on failure
 */
function LoginUser($userEmail, $password, $connection)
{
    $username = mysqli_real_escape_string($connection, $userEmail);

    $stmt = $connection->prepare("SELECT User_ID, EmailAddress, Password, Salt, isDeleted, Activated, HasPermissionLevel FROM User WHERE EmailAddress LIKE ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    // Create and execute database query
    //$query = "SELECT User_ID, EmailAddress, Password, Salt, Activated, HasPermissionLevel FROM User WHERE EmailAddress LIKE '$username'";
    //$result = $connection->query($query);

    // Check results
    if (mysqli_num_rows($result) >= 1)
    {
        $row = $result->fetch_assoc();

        // Only continue if this user is activated
        if ($row['Activated'] && !$row['isDeleted']) {
            // hash password with salt
            $password = $row['Salt'] . $password; // Add salt to front of password
            $hashed_password = hash('sha256', $password);
            // Check password
            $password_correct = strcasecmp($hashed_password, $row['Password']) == 0;
            //echo $row['Password'];
           // echo $hashed_password;
            if ($password_correct == 1) {
                if (IsSustainabilityTeamUser($row['HasPermissionLevel'])) {
                    // Password was correct, store session variables
                    $_SESSION['session_user_id'] = $row['User_ID'];
                    $_SESSION['session_user_email'] = $row['EmailAddress'];
                    $_SESSION['session_permission'] = $row['HasPermissionLevel'];
                    return true;
                } else {
                    echo "<script>alert('You do not have permission to access this site')</script>";
                }
            } else {
                echo "<script>alert('The password was wrong');</script>";
            }
        } else {
            echo "<script>alert('This account is not activated, contact the Sustainability Team for more information');</script>";
        }
    } else {
        echo "<script>alert('The username was not found');</script>";
    }
    return false;
}

/**
 * Destroys the users session so that they are no longer logged in
 * */
function Signout() {
    session_destroy();
}
