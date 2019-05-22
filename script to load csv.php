<?php

include "dbconnect.php";

 
		$filename= "final.csv";
        $file = fopen($filename, "r");
		fgetcsv($file);
         while (($getData = fgetcsv($file)) !== FALSE)
           {
		
			     /*
			   $sql = 'SELECT constituencyID
      FROM constituency
      WHERE constituencyName = '". $getData[0] . "';
	   $result1 = mysqli_query($con, $sql);
	   
	   */
			   $sqlInsert = "INSERT into centres (constituencyID,centreName ,monitorName,MSISDN)
                   values ('". $getData[1] . "','". $getData[2] . "','". $getData[3] . "','". $getData[4] . "')";
           $result = mysqli_query($con, $sqlInsert);
			  /*	
	$sqlInsert1 = "INSERT into districts (regionID ,districtName)
                   values (LAST_INSERT_ID(),'". $getData[1] . "')";
           $result1 = mysqli_query($con, $sqlInsert1);
			  print_r($getData);
			  die();
			 
				
				$sqlInsert = "INSERT into regions (regionName)
                   values ('". $getData[0] . "')";
           $result = mysqli_query($con, $sqlInsert);
				
	$sqlInsert1 = "INSERT into districts (regionID ,districtName)
                   values (LAST_INSERT_ID(),'". $getData[1] . "')";
           $result1 = mysqli_query($con, $sqlInsert1);
		   
		   
		   $sqlInsert2 = "INSERT into constituency (districtID ,constituencyName)
                   values (LAST_INSERT_ID(),'". $getData[2] . "')";
           $result2 = mysqli_query($con, $sqlInsert2);
		   
		    $sqlInsert3 = "INSERT into wards (constituencyID ,wardName,MSISDN  )
                   values (LAST_INSERT_ID(),'". $getData[3] . "','". $getData[4] . "')";
           $result3 = mysqli_query($con, $sqlInsert3);
		   
		   */
				
				

		
  
           }
    
           fclose($file);  

 ?>