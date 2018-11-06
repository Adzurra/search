<meta charset="utf8">
<?php

	set_time_limit(0);
	require "db-connect.php";
	error_reporting(0);

	$res = mysql_query("SELECT id FROM page");
	$numofurl = mysql_num_rows($res);
	while ($val = mysql_fetch_array($res)) {
		$arr_id[] = $val['id'];				
	}
	
		//$cur_val = array();
		//$prev_val = array();
	for ($i = 0; $i < $numofurl; $i++) { 
		
		$res1 = mysql_query("SELECT * FROM link WHERE pid1=".$arr_id[$i]);
		$res3 = mysql_query("SELECT * FROM link WHERE pid2=".$arr_id[$i]);
		$num_out_link[$i] = mysql_num_rows($res1);		
		$num_in_link[$i] = mysql_num_rows($res3);
		for ($j = 0; $j < $num_in_link[$i]; $j++) { 
			$row = mysql_fetch_array($res3);
			$data[$i][$j] = $row['pid1'];
		}
		//echo $num_out_link[$i]."<br>";
		$prev_val[$i] = 1;
		$cur_val[$i] = 0;
		
		
	}

$check = 0;
while ($check < $numofurl) {
	
	$check = 0;
	 for ($i = 0; $i < $numofurl; $i++) { 
		for ($j = 0; $j < $num_in_link[$i]; $j++) { 
			$cur_val[$i]+= $prev_val[$data[$i][$j]-1]/$num_out_link[$data[$i][$j]-1];

		}
		
		$cur_val[$i] = $cur_val[$i]*0.85+0.15;
		$differ = abs($cur_val[$i] - $prev_val[$i]);
		if ($differ <= 0.01) {
		$check++;
		}
	
	}
	
 
 	for ($i = 0; $i < $numofurl; $i++) { 
//	echo $num_in_link[$i]."in <br>";
//	echo $num_out_link[$i]."out <br>";
 		//echo $cur_val[$i]."<br>";
}
	//echo "end<br>";

	for ($i = 0; $i < $numofurl; $i++) { 
		$prev_val[$i] = $cur_val[$i];
		$cur_val[$i] = 0;
	}
	
}
	for ($i = 0; $i < $numofurl; $i++) { 
		$q = "UPDATE page SET page_rank=".$prev_val[$i]." WHERE id=".($i+1);
		echo $q."<br>";
		mysql_query($q);
	}
?>