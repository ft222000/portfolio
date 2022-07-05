<?php
/**
 * Creates a new database connection, and stores it within the connection variable.
 *
 * The connection variable will be a mysqli connection that can be used throughout the rest of the application.
 */

	$SERVERNAME = "localhost"; // The address of the server
	$USERNAME = "root"; // The username to be used when connecting to the MySQL database
	$PASSWORD = "root"; // The password to be used when connecting to the MySQL database
	$DATABASE = "foodfind"; // The name of the database that has been allocated for this application.

	$connection = new mysqli($SERVERNAME,$USERNAME,$PASSWORD,$DATABASE);

	/*Check to see if the connection succeeded or not by check for an error in the connection.*/
	if($connection->connect_error){
		die("Connection failed: " . $connection->connect_error);
	}


?>
