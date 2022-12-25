<?php

/**
 * It creates a table in the database if it doesn't exist,
 * and then inserts a token into the table if it doesn't exist,
 * and if it does exist, it updates the token
 * @ version 1.0.1
 * @ Mohammad Prince
 * @ Contributed by Jahangir
 * @ SoftTech-IT
 */

defined('BASEPATH') or exit('No direct script access allowed'); // Exit if accessed directly

include_once(APPPATH . 'config/app-config.php'); // Load the app config file

class Maildoll_token extends CI_Controller // This is the main controller for the application
{
    public function index() // This is the default function that is called when the application is loaded
    {

        // output: /myproject/index.php
        $currentPath = $_SERVER['PHP_SELF'];
        // output: Array ( [dirname] => /myproject [basename] => index.php [extension] => php [filename] => index )
        $pathInfo = pathinfo($currentPath);
        // output: localhost
        $hostName = $_SERVER['HTTP_HOST'];
        // output: http://
        $protocol = strtolower(substr($_SERVER["SERVER_PROTOCOL"],0,5))=='https'?'https':'http';
        // return: http://localhost/myproject/
        $perfex_domain = $protocol.'://'.$hostName.$pathInfo['dirname']; // this is the perfex crm installed domain

        $perfex_gmail = $_GET['email']; // this is the perfex crm user gmail

        // validation if $perfex_gmail is empty
        if (empty($perfex_gmail)) {
            $response = array( // Return the response in json format
                'status' => 'error',
                'message' => 'Email is empty'
            ); // Return the response in json format
            echo json_encode($response); // Return the response in json format
            return;
        }

        // database information -- change this to your database information
        $host =  APP_DB_HOSTNAME; // Host name
        $username =  APP_DB_USERNAME; // Your database username
        $password =  APP_DB_PASSWORD; // password
        $db_name = APP_DB_NAME; // Database name

        try { // connect to the database
        $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // match the email and token
        $matchEmail = $conn->prepare("SELECT * FROM tblstaff WHERE email = '$perfex_gmail'"); // Prepare a select statement
        $matchEmail->execute(); // Execute the prepared statement
        $getMatchEmail = $matchEmail->fetchAll(); // Fetch all the rows in the result set
        // match the email and token::END
        // count the number of rows in the result set
        $haveAnyEmail = $matchEmail->rowCount(); // Get the number of rows in the result set

        if ($haveAnyEmail <= 0) { // If the result set is greater than 0
            $response = array( // Return the response in json format
                'status' => 'error',
                'message' => 'Email is invalid'
            ); // Return the response in json format
            echo json_encode($response); // Return the response in json format
            return;
        }

        // sql to create table
        $sql = "CREATE TABLE IF NOT EXISTS maildoll_token (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(50),
        token VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )"; // sql to create table

        // use exec() because no results are returned
        $conn->exec($sql);

        // check maildoll_token atleast one value
        $stmt = $conn->prepare("SELECT * FROM maildoll_token");
        $stmt->execute();
        $has_value = $stmt->fetchAll();

        if (count($has_value) <= 0) { // if no value then insert one value
            // generate a token
            $token = bin2hex(random_bytes(16));

            // insert the token into the database
            $stmt = $conn->prepare("INSERT INTO maildoll_token (email, token) VALUES ('$perfex_gmail', '$token')"); // Prepare a select statement
            $stmt->execute(); // Execute the prepared statement
        }else { // if value then update the token
            $response = array( // if value then update the token
                'status' => 'error',
                'message' => 'Token already exists'
            ); // Return the response in json format
        } // Return the response in json format

        // return the token as json
        $stmt = $conn->prepare("SELECT * FROM maildoll_token");
        $stmt->execute(); // Execute the prepared statement
        $result = $stmt->fetchAll(); // Fetch all the rows in the result set

        $response = array( // return the response in json format
            'status' => 'success',
            'message' => 'Token generated',
            'token' => $result[0]['token']
        ); // return the response in json format

        echo json_encode($response); // Return the response in json format

        } catch(PDOException $e) { //  catch the exception
            echo $sql . "<br>" . $e->getMessage(); // display the error message
        } // catch the exception

        $conn = null; // close the connection
    }

    //ENDS
}
