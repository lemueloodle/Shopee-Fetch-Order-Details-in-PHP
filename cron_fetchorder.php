<?php

/**
* This is the file that you can run in CRON to fetch order details from your Shopee Store.
* 
* File type: PHP
* Format: JSON (but can be configured depends on your system need)
* 
* You can time the CRON job every 5,15,20 minutes. I suggest to have a minimum of 15 minutes to avoid server allocation timeout.
*
* @param int 
* $datenow - Time in your server
* $createtimefrom - Set start time for each array (given 15 days) 
* $createtimeto - Set end time for each array (after 15 days) 
*
* @param array
* $dates_array[] - Holds the array of start and end time of each 15 days (it is the limit of Shopee as per test)
*
*/

header( 'Content-Type: application/json' ); //Display result in JSON format
date_default_timezone_set( 'Asia/Manila' ); //Set time to your specific timezone
session_start(); //Start Session in Server
ini_set( 'max_execution_time', 6000000 ); //Execute longer server time
require_once( 'config.php' ); //Extend the file to the config file
include_once( 'function.php' ); //Extend the file to the function file

$datenow     = time(); 

$dates_array = [];

/**
* 
* Set the start time and end time then push in the array for result.
* 
* Add days as much as you needed for your system.
*  
*/

//Set 15th day to time now
$createtimefrom = strtotime( '-15 day', $datenow );
$createtimeto   = $datenow;
$dates_array[0] = array( 'from' => $createtimefrom, 'to' => $createtimeto );

//Set 29th to 14th day 
$createtimefrom = strtotime( '-29 day', $datenow );
$createtimeto   = strtotime( '-14 day', $datenow );
$dates_array[1] = array( 'from' => $createtimefrom, 'to' => $createtimeto );

//Set 43rd to 28th day
$createtimefrom = strtotime( '-43 day', $datenow );
$createtimeto   = strtotime( '-28 day', $datenow );
$dates_array[2] = array( 'from' => $createtimefrom, 'to' => $createtimeto );

//Set 57th to 42nd day
$createtimefrom = strtotime( '-57 day', $datenow );
$createtimeto   = strtotime( '-42 day', $datenow );
$dates_array[3] = array( 'from' => $createtimefrom, 'to' => $createtimeto );

//Set 71st to 56th day
$createtimefrom = strtotime( '-71 day', $datenow );
$createtimeto   = strtotime( '-56 day', $datenow );
$dates_array[4] = array( 'from' => $createtimefrom, 'to' => $createtimeto );

//Set 85th to 70th day
$createtimefrom = strtotime( '-85 day', $datenow );
$createtimeto   = strtotime( '-70 day', $datenow );
$dates_array[5] = array( 'from' => $createtimefrom, 'to' => $createtimeto );

//Set 99th to 84th day
$createtimefrom = strtotime( '-99 day', $datenow );
$createtimeto   = strtotime( '-84 day', $datenow );
$dates_array[6] = array( 'from' => $createtimefrom, 'to' => $createtimeto );

/**
* 
* Execute class to get data from the function.
* 
* This will loop the $dates_array to retrieve Shopee Order Details through API.
*
* @param class $shopee Class function for Shopee Integration
* 
* printf @param $result Order Details in JSON format
*
*/
   
$shopee = new ShopeeIntegrate();

foreach( $dates_array as $key => $value ){
    $lastkey_parent = 0;
    $result = $shopee->fetch_orderdetails( $username, $password, $sign, $partner_id, $shopid, $pageoffset, $pagination_per_page, $datenow, $value['from'], $value['to'], $lastkey_parent );
}
