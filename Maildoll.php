<?php

/**
 * It gets the email and token from the url,
 * then it checks if the email and token matches the database,
 * if it does, it returns the data from the database
 * @ version 1.0.0
 * @ Mohammad Prince
 * @ SoftTech-IT
 */

defined('BASEPATH') or exit('No direct script access allowed'); // Exit if accessed directly

header("Access-Control-Allow-Origin: *"); // Allow cross-domain requests
header("Content-Type: application/json; charset=UTF-8"); // Set content type to json

include_once(APPPATH . 'config/app-config.php'); // Load the app config file

class Maildoll extends CI_Controller // This is the main controller for the application
{

    public function index() // This is the default function that is called when the application is loaded
    {

        $email = $_GET['email']; // Get the email from the url
        $password = $_GET['token']; // Get the password from the url

        // validation if $email & $password is empty
        if (empty($email) || empty($password)) {
            $response = array( // Return the response in json format
                'status' => 'error',
                'message' => 'Email or Token is empty'
            ); // Return the response in json format
            echo json_encode($response); // Return the response in json format
            return;
        }

        // database information -- change this to your database information
        $db_host =  APP_DB_HOSTNAME; // Host name
        $db_username =  APP_DB_USERNAME; // Your database username
        $db_password =  APP_DB_PASSWORD; // password
        $db_name = APP_DB_NAME; // Database name

        try { // Connect to server and select database.
            $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_username, $db_password); // set the PDO error mode to exception
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set the PDO error mode to exception

            // Auth Check
            $stmt = $conn->prepare("SELECT * FROM tblstaff WHERE email = '$email'"); // Prepare a select statement
            $stmt->execute(); // Execute the prepared statement
            $token_email = $stmt->fetchAll(); // Fetch all the rows in the result set

            // get $email token from maildoll_token table
            $stmt = $conn->prepare("SELECT * FROM maildoll_token WHERE email = '$email'"); // Prepare a select statement
            $stmt->execute(); // Execute the prepared statement
            $token = $stmt->fetchAll(); // Fetch all the rows in the result set

            // user token and email

            $user_email = $token_email[0]['email']; // Get the email from the result set
            $user_token = $token[0]['token']; // Get the token from the result set

            // Auth Check::END

            // match the email and token
            $matchEmailToken = $conn->prepare("SELECT * FROM maildoll_token WHERE email = '$email' AND token = '$password'"); // Prepare a select statement
            $matchEmailToken->execute(); // Execute the prepared statement
            $getMatchEmailToken = $matchEmailToken->fetchAll(); // Fetch all the rows in the result set
            // match the email and token::END
            // count the number of rows in the result set
            $haveAnyToken = $matchEmailToken->rowCount(); // Get the number of rows in the result set

            if ($haveAnyToken > 0) { // If the result set is greater than 0

                    $sql = "SELECT * FROM `tblcontacts`"; // SQL query to fetch information of table

                    $q = $conn->query($sql); // execute the query
                    $q->setFetchMode(PDO::FETCH_ASSOC); // set the resulting array to associative
                    $r = $q->fetch(); // fetch the values

                    $itemCount = $q->rowCount(); // get the number of rows

                    $json = array(); // array

                    if($itemCount > 0){ // if there are any rows in the table

                        while ($r = $q->fetch()) { // loop through the results
                            $json[] = $r; // return the json encoded values
                        } // end while

                        echo json_encode($json);

                    }else{ // if there are no rows in the table
                        echo json_encode(array());
                    } // end if
            } // end if
            else { // If the result set is empty
                $response = array( // Return the response in json format
                    'status' => 'error',
                    'message' => 'Email or Token is invalid'
                ); // Return the response in json format
                echo json_encode($response); // Return the response in json format
                return;
            } // end else


        } catch(PDOException $e) { // catch the exception
            header('Content-Type: application/json'); // set the content type to json
            echo json_encode(array( // return the json encoded values
                'error' => array(
                    'text' => $e->getMessage(), // return the error message
                    'code' => $e->getCode() // return the error code
                )
            )); // return the json encoded values
        } // end of try catch

    } // end of index function

    // ENDS
}
