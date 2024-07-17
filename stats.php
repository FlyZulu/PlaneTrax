<?php
    
    $db_status = '';

    //Database connection information
    require 'db_constants.php'; 

    echo ('<html>'.PHP_EOL);    
?>

<style>

 .styled-table {
    border-collapse: collapse;
    margin: 25px 0;
    font-size: 3.0em;
    font-family: sans-serif;
    min-width: 800px;
    box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
}   

.styled-table thead tr {
    background-color: #0066cc;
    color: #ffffff;
    text-align: left;
}

.styled-table th,
.styled-table td {
    padding: 12px 15px;
}

.styled-table tbody tr {
    border-bottom: 1px solid #dddddd;
}

.styled-table tbody tr:nth-of-type(even) {
    background-color: #f3f3f3;
}

.styled-table tbody tr:last-of-type {
    border-bottom: 2px solid #0066cc;
}

.styled-table tbody tr.active-row {
    font-weight: bold;
    color: #009879;
}

</style>

<?php
    echo ('<body>'.PHP_EOL);    
    //echo ('<head><title>Plane Trax</title><meta http-equiv="refresh" content="1">'.PHP_EOL);    
    echo ('<head><title>Plane Trax</title>'.PHP_EOL);    
        
    echo ('</head>'.PHP_EOL);    
    
    try {               
        //$db = new PDO('mysql:host=' . $user_set_array['db_host'] . ';dbname=' . $user_set_array['db_name'] . '', $user_set_array['db_user'], $user_set_array['db_pass']); 
        
        $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . '', DB_USER, DB_PASS);

	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
        //Start date 
        $sql = 'SELECT DATE_FORMAT(min(op_date), "%m/%d/%Y" ) as min_date, DATEDIFF( curdate(), min(op_date)) as days_since_start FROM ops';	

        $result = $db->query($sql);
        
        foreach ($result as $row ) { 
            $min_date = $row['min_date'];
            $days_since_start = $row['days_since_start'];
        }


        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
        echo('<td style="width: 885px";>');
        echo('Plane Trax for '.date("F d, Y").PHP_EOL);                   
        echo(' - '.date("h:i A").PHP_EOL);        
        echo('</td></tr></thead></table>' .PHP_EOL);

 
        //echo('Operations Tracking for '.date("F d, Y").'<br>'.'<br>'.PHP_EOL);        

        //echo(date("h:i A").PHP_EOL);        

        
        //*********************************************************
        //Operations Count
        //*********************************************************
        

        //Count of today's operations
        $sql = 'select count(*) as "operations" from ops where date (op_date) = curdate()';	

        $result = $db->query($sql);
        
        foreach ($result as $row ) { 
            $today_operations = $row['operations'];
        }

        //Count of all operations 
        $sql = 'select count(*) as "operations" from ops';	

        $result = $db->query($sql);
        
        foreach ($result as $row ) { 
            $total_operations = $row['operations'];
        }

        //Count of all operations in the last hour
        $sql = 'select count(*) as "operations" from ops where op_date >= DATE_SUB(NOW(),INTERVAL 1 HOUR);';	

        $result = $db->query($sql);
        
        foreach ($result as $row ) { 
            $hour_operations = $row['operations'];
        }

        echo ('<br>'.'<br>'.PHP_EOL);                                       
        
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
        echo('<td style="width: 880px";>');
        echo('Operations Summary'.PHP_EOL);                           
        echo('</td></tr></thead></table>' .PHP_EOL);

        //echo ('<br>'.'<br>'.'Operations Summary'.PHP_EOL);                               

        echo ('<table class="styled-table"><thead>'.PHP_EOL);

        //echo('<tr><td style="width: 750px;">'.'Operations Summary'.'</td></tr>');

        echo('<tr>');        

        echo('<td style="width: 300px;">'.'Last Hour'.'</td>');
        echo('<td style="width: 200px;">'.'Today'.'</td>');
        echo('<td style="width: 150px;">'.'Total since '. $min_date . '</td>');
        echo('<td style="width: 50px;">'.'&nbsp;'.'</td>');
        echo('</tr></thead>'.PHP_EOL); 

        echo('<tr>'); 
        echo('<td>'.$hour_operations.'</td>');
        echo('<td>'.$today_operations.'</td>');
        echo('<td>'.number_format($total_operations).'</td>');
        echo('<td>'.'&nbsp;'.'</td>');        
        echo('</tr>'.PHP_EOL); 
       
        echo ('</table>'.PHP_EOL);
        
       //*********************************************************
        //Day Leaderboard - All Operations
        //*********************************************************
         $sql = 'select date(op_date) as "rpt_date", count(*) as operations  from ops group by rpt_date order by operations desc limit 10;';	
       

        $result = $db->query($sql);
        
        //echo ('<br>'.'<br>'.'Date Leaderboard - All Operations'.PHP_EOL);                               

        echo ('<br>'.'<br>'.PHP_EOL);                                       
        
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
        echo('<td style="width: 890px";>');
        echo('Date Leaderboard - All Operations'.PHP_EOL);                           
        echo('</td></tr></thead></table>' .PHP_EOL);
        
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);

        echo('<td style="width: 175px;">'.'Rank'.'</td>');
        echo('<td style="width: 350px;">'.'Date'.'</td>');
        echo('<td style="width: 100px;">'.'Ops'.'</td>');
        echo('<td style="width: 175px;">'.'&nbsp;'.'</td>');  

        echo('</tr></thead>'.PHP_EOL); 

        $rank = 0;
        
        foreach ($result as $row ) { 
            echo('<tr>'); 

            echo('<td>'.++$rank.'</td>');
            echo('<td>'.$row['rpt_date'].'</td>');
            echo('<td>'.number_format($row['operations']).'</td>');
            echo('<td>'.'&nbsp;'.'</td>');            
            
            echo('</tr>'.PHP_EOL); 
        }
     
        echo ('</table>'.PHP_EOL);    
        
        //*********************************************************
        //Flight Leaderboard - Today's Operations
        //*********************************************************
        $sql = 'select flight, count(*) as operations from ops where date (op_date) = curdate() group by flight order by operations desc limit 10;';	
         
        $result = $db->query($sql);
        
        //echo ('<br>'.'<br>'.'Flight Leaderboard - Today\'s Operations'.PHP_EOL);                               

        echo ('<br>'.'<br>'.PHP_EOL);                                       
        
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
        echo('<td style="width: 890px";>');
        echo('Flight Leaderboard - Today\'s Operations'.PHP_EOL);                           
        echo('</td></tr></thead></table>' .PHP_EOL);        
                
        //echo ('<table border="1">'.PHP_EOL);
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
     
        echo('<td style="width: 175px;">'.'Rank'.'</td>');
        echo('<td style="width: 350px;">'.'Flight'.'</td>');
        echo('<td style="width: 100px;">'.'Ops'.'</td>');
        echo('<td style="width: 175px;">'.'&nbsp;'.'</td>');          
        echo('</thead></tr>'.PHP_EOL); 

        $rank = 0;
        
        foreach ($result as $row ) { 
            echo('<tr>'); 

            echo('<td>'.++$rank.'</td>');
            echo('<td>'.$row['flight'].'</td>');
            echo('<td>'.number_format($row['operations']).'</td>');
            echo('<td>'.'&nbsp;'.'</td>');            	
            
            echo('</tr>'.PHP_EOL); 
        }
     
        echo ('</table>'.PHP_EOL);
        
        //*********************************************************
        //Flight Leaderboard - All Operations
        //*********************************************************
         $sql = 'select flight, count(*) as operations  from ops group by flight order by operations desc limit 10;';	
         

        $result = $db->query($sql);
        
        //echo ('<br>'.'<br>'.'Flight Leaderboard - All Operations'.PHP_EOL);                               

        echo ('<br>'.'<br>'.PHP_EOL);                                       
        
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
        echo('<td style="width: 905px";>');
        echo('Flight Leaderboard - All Operations'.PHP_EOL);                           
        echo('</td></tr></thead></table>' .PHP_EOL);              
        
        //echo ('<table border="1">'.PHP_EOL);
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);

        echo('<td style="width: 175px;">'.'Rank'.'</td>');
        echo('<td style="width: 250px;">'.'Flight'.'</td>');
        echo('<td style="width: 175px;">'.'Ops'.'</td>');
        echo('<td style="width: 170px;">'.'% of Total'.'</td>');
        echo('<td style="width: 5px;">'.'&nbsp;'.'</td>');          
        
        echo('</thead></tr>'.PHP_EOL); 

        $rank = 0;
        
        foreach ($result as $row ) { 
            echo('<tr>'); 

            echo('<td>'.++$rank.'</td>');
            echo('<td>'.$row['flight'].'</td>');
            echo('<td>'.number_format($row['operations']).'</td>');
            echo('<td>'. round ($row['operations'] / $total_operations, 2) * 100  . '%'. '</td>');
            echo('<td>'.'&nbsp;'.'</td>');            	
                    
            echo('</tr>'.PHP_EOL); 
        }
     
        echo ('</table>'.PHP_EOL);

        //*********************************************************
        // By Day - All Operations
        //*********************************************************
        
        $sql = 'SELECT WEEKDAY(op_date) as day_number, DAYNAME(op_date) as day_name, COUNT(*) as operations FROM ops GROUP BY DAYNAME(op_date) order by WEEKDAY(op_date)';	
         
        $result = $db->query($sql);
        
        //echo ('<br>'.'<br>'.'By Day - All Operations'.PHP_EOL);                               

        echo ('<br>'.'<br>'.PHP_EOL);                                       
        
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
        echo('<td style="width: 910px";>');
        echo('By Day - All Operations'.PHP_EOL);                           
        echo('</td></tr></thead></table>' .PHP_EOL);              
        
        //echo ('<table border="1">'.PHP_EOL);
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
        
        echo('<td style="width: 400px;">'.'Day'.'</td>');       
        echo('<td style="width: 200px;">'.'Ops'.'</td>');
        echo('<td style="width: 250px;">'.'&nbsp;'.'</td>');         
        
        echo('</thead></tr>'.PHP_EOL); 
      
        foreach ($result as $row ) { 
            echo('<tr>'); 

            echo('<td>'.$row['day_name'].'</td>');
            echo('<td>'.number_format($row['operations']).'</td>');
            echo('<td>'.'&nbsp;'.'</td>');            	
            
            echo('</tr>'.PHP_EOL); 
        }
     
        echo ('</table>'.PHP_EOL);
                
        //*********************************************************
        // Operations by Hour
        //*********************************************************
        
        $sql = 'SELECT HOUR(op_date) as hour_24, CONCAT (TIME_FORMAT(op_date, "%h"), ":00 ", TIME_FORMAT(op_date, "%p"))as hour_12, COUNT(*) as operations FROM ops GROUP BY HOUR(op_date) order by hour_24';	
         
        $result = $db->query($sql);
        
        //echo ('<br>'.'<br>'.'By Hour - All Operations'.PHP_EOL);                               

        echo ('<br>'.'<br>'.PHP_EOL);                                       
        
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
        echo('<td style="width: 910px";>');
        echo('By Hour - All Operations'.PHP_EOL);                           
        echo('</td></tr></thead></table>' .PHP_EOL);        

        //echo ('<table border="1">'.PHP_EOL);
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);

        echo('<td style="width: 400px;">'.'Hour'.'</td>');       
        echo('<td style="width: 200px;">'.'Ops'.'</td>');
        echo('<td style="width: 250px;">'.'&nbsp;'.'</td>');         
        
        echo('</thead></tr>'.PHP_EOL); 
      
        foreach ($result as $row ) { 
            echo('<tr>'); 

            echo('<td>'.$row['hour_12'].'</td>');
            echo('<td>'.number_format($row['operations']).'</td>');
            echo('<td>'.'&nbsp;'.'</td>');            	
            
            echo('</tr>'.PHP_EOL); 
        }
     
        echo ('</table>'.PHP_EOL);
               
        //List of today's operations                 
        //$sql = 'select date(op_date) as "date", time (op_date) as "time", flight, runway, op, tg from ops where date (op_date) = curdate() order by op_date desc';	
        $sql = 'select date(op_date) as "date", TIME_FORMAT (op_date, "%h:%i %p") as "time", flight, runway, op, tg from ops where date (op_date) = curdate() order by op_date desc';          
                
         $result = $db->query($sql);

        $db_status = 'worked';
        
        //echo ('<br>'.'<br>'.'Today\'s Operational Details'.PHP_EOL);                               
       
        echo ('<br>'.'<br>'.PHP_EOL);                                       
       
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
        echo('<td style="width: 910px";>');
        echo('Today\'s Operational Details'.PHP_EOL);                           
        echo('</td></tr></thead></table>' .PHP_EOL);        
        
        //echo ('<table border="1">'.PHP_EOL);
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);

        
        echo('<td style="width: 275px;">'.'Time' . '</td>');                
        echo('<td style="width: 225px;">'.'Flight'.'</td>');
        echo('<td style="width: 100px;">'.'Op'.'</td>');
        echo('<td style="width: 225px;">'.'Runway'.'</td>');
        
        echo('</thead></tr>'.PHP_EOL); 

        foreach ($result as $row ) { 
            echo('<tr>'); 

        //if ($row['tg'] == 'Y')
        //    $op_descr = 'Touch and Go ';
        if ($row['op'] == 'T')
            $op_descr = 'T';
        elseif ($row['op'] == 'L')
            $op_descr = 'L';

        if ($row['tg'] == 'Y')
            $op_descr = $op_descr.' *';
        
//      $op_descr = $op_descr . $row['runway'];
            
            
            //echo('<td>'.$row['id'].'</td>');
//          echo('<td>'.$row['date'].'</td>');
            echo('<td>'.$row['time'].'</td>');                
            echo('<td>'.$row['flight'].'</td>');
            echo('<td>'.$op_descr.'</td>');
            echo('<td>'.$row['runway'].'</td>');
//          echo('<td>'.$row['tg'].'</td>');

//                for ($i=0;$i<mysql_num_fields($result);$i++)  {
//       				 
//                    echo('<td>'.$row[$i].'</td>'); 
//                } 
		
                echo('</tr>'.PHP_EOL); 
        }

        echo ('</table>'.PHP_EOL);
        
        //echo ('* Touch and Go'.PHP_EOL);
        echo ('<table class="styled-table"><thead><tr>'.PHP_EOL);
        echo('<td style="width: 910px";>');
        echo('* Touch and Go'.PHP_EOL);                           
        echo('</td></tr></thead></table>' .PHP_EOL);      

        $db = null;

    }   
    catch (PDOException $db_error) {
            $db_status = 'db error' . PHP_EOL . $db_error->getMessage();
    }

    //echo (PHP_EOL.'$db_status='.$db_status.PHP_EOL);

echo ('</table>'.PHP_EOL);
echo ('</body>'.PHP_EOL);    

?>
