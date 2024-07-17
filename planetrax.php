#!/usr/bin/php
<?php

/****************************************************************
** planetrax.php  
**
** E-Mail: info@planetrax.com
**
** Date: July 15, 2024
**
** Uses ADSB data transmitted from aircraft to generate a count of 
** takeoffs and landings (operations) at a given airport and store
** details of the operations in a database.
** 
*****************************************************************/

/****************************************************************
** Environment set up 
*****************************************************************/

$ini = parse_ini_file("/etc/planetrax.conf");

print_r($ini);

$d = $ini['debug'];

//Set paths to JSON files
//1090 files may be at: http://127.0.0.1/info/
$json_1090 = $ini['json_1090'];
$json_978 = $ini['json_978'];

//Parameters for database connection
$db_name = $ini['db_name'];
$db_host = $ini['db_host'];
$db_user = $ini['db_user'];
$db_pass = $ini['db_pass'];

//Set delay in message processing loop in seconds
//Default is 1 (must be integer between 1 - 900) 
$sleep = $ini['sleep']; 

//Set your timezone see http://php.net/manual/en/timezones.php
$time_zone = $ini['time_zone'];
date_default_timezone_set($time_zone);

//Maximum altitude for messages to be processed
//Default is 1500
$max_alt = $ini['max_alt'];

//Maximum deviation from the runway course
//Default is 40
$max_deviation = $ini['max_deviation'];

//The amount of time in seconds a duplicate entry for an op is ignored 
//Default is 120
$duplicate_time = $ini['duplicate_time'];

//Runway 'a' approach area / Runway 'b' takeoff area coordinates

$runway_a_name = $ini['runway_a_name'];

$min_lat_a = $ini['min_lat_a'];    
$max_lat_a = $ini['max_lat_a'];    

$min_lon_a = $ini['min_lon_a'];
$max_lon_a = $ini['max_lon_a'];    

//Magnetic direction of runway in degrees
$course_a = $ini['course_a']; 
 
//Runway 'b' approach area / Runway 'a' takeoff area coordinates
$runway_b_name = $ini['runway_b_name'];

$min_lat_b = $ini['min_lat_b'];    
$max_lat_b = $ini['max_lat_b'];    

$min_lon_b = $ini['min_lon_b'];
$max_lon_b = $ini['max_lon_b'];     

//Magnetic direction of runway in degrees
$course_b = $ini['course_b'];

//Used to compute receiver distance to aircraft
//Only used to determine whether a message should be written to the debug log
$receiver_lat = $ini['receiver_lat'];
$receiver_lon = $ini['receiver_lon'];

//Only used to determine whether a message should be written to the debug log
$max_log_distance = $ini['max_log_distance'];
$max_log_alt = $ini['max_log_alt'];

/****************************************************************
** End Environment set up 
*****************************************************************/

$run_count = 0;
$ops_count = 0;

$start_time = time();

//Used to cycle between 1090 and 978 in processing loop
$json_current = ''; 

//$The ops array is used to store recent ops 
//so duplicates won't be added to the database.
//The array key is flight:runway:operation (e.g. 'N789:16:L')
$ops = array();

/******************************************************************************************************************
* courseDeviation returns the difference in a track and course in degrees
******************************************************************************************************************/
function courseDeviation (int $track, int $course) : int{

  $dev1 = 0;
  $dev2 = 0;  
  $dev3 = 0;

  if ($track == 0 || $course == 0 ){ //This is an error as needed information isn't being supplied
    return 0;  
  }

  //The lesser of the three values is the deviation
  //This deals with the issue of the bearing going below zero or above 360 when calculations are made

  $dev1 = $track - $course;
  $dev2 = $track - ($course + 360);
  $dev3 = $track - ($course - 360); 
 
    if ( $dev1 < 0){
        $dev1 = $dev1 * -1;
    }
    
    if ( $dev2 < 0) {
        $dev2 = $dev2 * -1;
    }
    

    if ( $dev3 < 0){
        $dev3 = $dev3 * -1;
    }
    
    if ( $dev3 < $dev2 && $dev3 < $dev1 ) {  
        return $dev3;
    }
      
    if ($dev2 < $dev1) {
        return $dev2;
    }
    else {
        return $dev1;
    }

}

// function to compute distance between receiver and aircraft
function compute_distance($lat_from, $lon_from, $lat_to, $lon_to, $earth_radius = 3440) {
	$delta_lat = deg2rad($lat_to - $lat_from);
	$delta_lon = deg2rad($lon_to - $lon_from);
	$a = sin($delta_lat / 2) * sin($delta_lat / 2) + cos(deg2rad($lat_from)) * cos(deg2rad($lat_to)) * sin($delta_lon / 2) * sin($delta_lon / 2);
	$c = 2 * atan2(sqrt($a), sqrt(1-$a));
	return round (($earth_radius * $c),2);
}

while (true) {
 	
    //Alternate processing between 1090 and 978 files

    if ($json_current == $json_1090) {
        $json_current = $json_978;
    }
    else {
        $json_current = $json_1090;
    }
               
    //Fetch the JSON file that contains ADSB messages  
    $json_data_array = json_decode(file_get_contents($json_current), true);

    isset($json_data_array['now']) ? $ac_now = $json_data_array['now'] : $ac_now = '';
    isset($json_data_array['messages']) ? $ac_messages_total = $json_data_array['messages'] : $ac_messages_total = '';

    //Delete any operations from the $ops array older than the duplicate_time 
    //This is the tolerance for operations not be counted as duplicates
    foreach ($ops as $key => $v1 ) {
   
        foreach ($v1 as $ops_time) {
            
            if ($ops_time + $duplicate_time < time() ) {
                unset($ops[$key]);          
          
            }
        }

    }
        
    //Read ADSB messages from the JSON file written by dump1090 and dump978
    foreach ($json_data_array['aircraft'] as $row) {
        
        $sql = '';
        $db_insert = '';
        
        isset($row['flight']) ? $ac_flight = trim($row['flight']) : $ac_flight = '';

        //Field name may be 'altitude' in some versions                
        isset($row['alt_baro']) ? $ac_altitude = $row['alt_baro'] : $ac_altitude = '';

        isset($row['lat']) ? $ac_lat = $row['lat'] : $ac_lat = '';
        isset($row['lon']) ? $ac_lon = $row['lon'] : $ac_lon = '';
        isset($row['track']) ? $ac_track = $row['track'] : $ac_track = '';

        //Field name may be 'speed' in some versions 
        isset($row['gs']) ? $ac_speed = $row['gs'] : $ac_speed = '';

        isset($row['vert_rate']) ? $ac_vert_rate = $row['vert_rate'] : $ac_vert_rate = '';
        
	$distance = compute_distance(floatval($receiver_lat), floatval($receiver_lon), floatval($ac_lat), floatval($ac_lon));
                
        //Any entry that qualifies here will be inserted into the ops_log table
        //More qualification is required before determining if this is an operation to be logged	
        
        if (
                ($ac_flight != '' && $ac_altitude != '' && $ac_altitude < $max_log_alt && $ac_altitude != 'ground' && $distance <= $max_log_distance) 

                
                
               
                
           ) {
                
            if ($d) print(PHP_EOL.'*****'. $json_current. ' '. $ac_flight . ' ' . $ac_altitude . ' ' . $ac_track . ' ' .$ac_speed . ' ' . $ac_lat . ' '. $ac_lon . ' ' . $distance .  PHP_EOL);
            
            $runway = '';
            $op = '';
            $tg = 'N';
            $new = false;
            $current_op = '';

            //Flight is in the runway 'a' approach area / runway 'b' departure area                         
            if ( $ac_lat < $max_lat_a && $ac_lat > $min_lat_a && $ac_lon < $max_lon_a && $ac_lon > $min_lon_a && $ac_altitude < $max_alt  ) {
 
                //See if track is within variation of course for 'runway a' landing
                if ( courseDeviation ($ac_track, $course_a) <= $max_deviation ) {
                    $runway = $runway_a_name;
                    $op = 'L';
                }

                //See if track is within variation of course for 'runway b' takeoff
                if ( courseDeviation ($ac_track, $course_b) <= $max_deviation ) {
                   $runway = $runway_b_name;
                    $op = 'T';
                }
                
            }
        

            //Flight is in the runway 'b' approach area / runway 'a' departure area                         
            if ( $ac_lat < $max_lat_b && $ac_lat > $min_lat_b && $ac_lon < $max_lon_b && $ac_lon > $min_lon_b && $ac_altitude < $max_alt  ) {

                //See if track is within variation of course for 'runway a' takeoff
                if ( courseDeviation ($ac_track, $course_a) <= $max_deviation ) {
                     $runway = $runway_a_name;
                     $op = 'T';
                }
                
                //See if track is within variation of course for 'runway b' landing 
                if ( courseDeviation ($ac_track, $course_b) <= $max_deviation ) {
                     $runway = $runway_b_name;
                     $op = 'L';
                }
                
             }

            $current_op = $ac_flight.':'.$runway.':'.$op;

            //Check to see if the current operation will be a duplicate 
            //Add it to the array if it's new
            //Note: May need to update time to current so tg computed correctly

            if ($runway != '' && $op != ''){
                if (! isset($ops[$current_op])) {
                    //new to array so add it
                    $new_op = array($current_op => array('time' => time(),),);

                    $new_array = array();
                    $new_array = array_merge ($ops, $new_op);
                    unset ($ops);
                    $ops = $new_array;
                    unset ($new_array); 
                    $new = true;
                    }
            }

            //Set the 'touch and go' flag to 'Y' if there was a recent landing 
            //by this flight on this runway
            //The entries in the $ops array are no older than the duplicate_time 
            if ($runway != '' && $op == 'T'){
                if (isset($ops[$ac_flight.':'.$runway.':'.'L'])) {
                    $tg = 'Y';
                    }
            }

            if ($d) {
                $d_msg = '*****'. $current_op . ' '. $tg . ' ' . ($new ? 'new' : '');
                $d_msg .= date(" G:i:s", time());
                $d_msg .= ' ' . $ac_altitude;
                $d_msg .= ' ' . $distance;
                print(PHP_EOL.$d_msg.PHP_EOL); 
            }

            if ($d && false) {
                print(PHP_EOL.'##############$ops########################'.PHP_EOL);
                var_dump($ops);
                print(PHP_EOL.'##########################################'.PHP_EOL);
            }

            if ($new) {

                $sql .= "INSERT INTO ops VALUES (NULL, '" . date("Y-m-d G:i:s", $ac_now) . "', '$ac_flight', '$runway', '$op', '$tg', false);";
                    $sql .= PHP_EOL;

                $ops_count++;
            }

            //Insert row in db for debugging 
            if ($d) {

                $log_sql = "INSERT INTO ops_log VALUES (NULL, '" . date("Y-m-d G:i:s", $ac_now) . "', '$ac_flight', '$runway', '$op', '$tg',";
                    $log_sql .= "'$ac_altitude', '$ac_lat', '$ac_lon', '$ac_track', '$ac_speed', '$ac_vert_rate',";
                    $log_sql .= "'$d_msg');";
                    $log_sql .= PHP_EOL;

                try {               
                    $db = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_name . '', $db_user, $db_pass); $db_insert = '';
                    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $db->exec($log_sql); 
                    $db = null;
                } 
                catch (PDOException $db_error) {
                    $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
                }
            }
            
            //Insert row for op in db
            if ($sql) {                
                
                $db_insert = $current_op;
                
                try {               
                        $db = new PDO('mysql:host=' . $db_host . ';dbname=' . $db_name . '', $db_user, $db_pass); $db_insert = '';
                        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $db->exec($sql);  
                        $db = null;
                }
            
                catch (PDOException $db_error) {
                        $db_insert = 'db-error' . PHP_EOL . $db_error->getMessage();
                }
            }
        }
    }
    
    //Generate terminal output 
    $runtime = (time() - $start_time);
    $runtime_formatted = sprintf('%d days %02d:%02d:%02d', $runtime/60/60/24,($runtime/60/60)%24,($runtime/60)%60,$runtime%60);

    print(PHP_EOL.'Run Time ' . $runtime_formatted . ' - run ' . $run_count . ' ' . ' ops -> ' . $ops_count . ' ' .$db_insert . PHP_EOL);

    sleep($sleep);

    $run_count++;

}

?>
