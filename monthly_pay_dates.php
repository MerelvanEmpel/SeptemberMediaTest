<?php 

$myfile = fopen($argv[1], "w") or die("Unable to open file!");

$month_array = get_month_array();

// Create a csv file with payment and bonus days per month. 
$month_integer = 0;
foreach ($month_array as $month) {
	$month_integer ++;
	// Is the 15th a weekday?
	$bonus_day_timestamp = strtotime(date('Y'). '-' . $month_integer . '-15');
	
	$weekend_day = isWeekend($bonus_day_timestamp) . "\n";
	
	// Print all information to the file.
	fwrite($myfile, $month . "\n");
	
	// TODO: Instead of putting the $weekend_day boolean in the second column, 
	// put the current day in there if the $weekend_day is false, 
	// If $weekend_day is true, Make a function to calculate the next wednesday 
	// And put that in the second column in stead. 
	// Add headers (Month, Bonusday, Paymentday to csv file)
	// Add third column information to csv file (make function to calculate the 
	// payday according to the Test sheet rules).
	// Create documentation in markdown. 
}

fclose($myfile);

/**
 * Get an array of all months of the year.
 */
function get_month_array(){
	$month_array = array();
	for ($m=1; $m<=12; $m++) {
     $month = date('F', mktime(0,0,0,$m, 1, date('Y')));
     array_push($month_array, $month);
   }
   
   return $month_array;
}

/**
 * Returns a boolean true for weekend days, false for work days.
 */
function isWeekend($date) {
	$weekend = FALSE;
	
	if (date('N', $date) > 6) {
		$weekend = TRUE;
	}
	
   return $weekend;
}
?>