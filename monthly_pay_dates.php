<?php

/**
 * Ik heb ervoor gekozen om geen Drupal te gebruiken voor deze opdracht omdat
 * het erg omslachtig zou zijn om een website op te zetten voor deze opdracht,
 * omdat gevraagd wordt om een command line programma. Er is dus niet meer nodig
 * dan een php file met de juiste code die aangeroepen kan worden via de command
 * line.
 *
 * Ik heb gekozen om zelf een functie te schrijven voor het bepalen of iets
 * een week- of weekeind dag is omdat ik er geen standaard php functie voor
 * heb kunnen vinden.
 */

$myfile = fopen($argv[1], 'w') or die('Unable to open file!');

// Print headers to the file.
fwrite($myfile, "Month,Bonusday,Payday\n");

$month_array = get_month_array();

$holiday_array = array(
  'nieuwjaarsdag' => '1-1',
  // I didn't use easter_date() because that gives one easter date and we need
  // two. Also, I'm not sure if Dutch easter is equal to php easter.
  'paassen1' => '16-4',
  'paassen2' => '17-4',
  'koningsdag' => '27-4',
  'hemelvaart' => '25-5',
  'pinksteren1' => '25-5',
  'pinksteren2' => '25-5',
  'kerst1' => '24-12',
  'kerst2' => '25-12',
  'oudjaarsdag' => '31-12',
);

// Create a csv file with payment and bonus days per month. 
$month_integer = 0;
foreach ($month_array as $month) {
	$month_integer ++;
	// Is the 15th a weekday?
  $bonus_date = date('Y'). '-' . $month_integer . '-15';
	$bonus_date_timestamp = strtotime($bonus_date);
  $bonus_day = calculate_bonus_day($bonus_date_timestamp);

  $ld = last_day_of_month($month_integer);
  $last_day_of_month = date('Y'). '-' . $month_integer . '-' . last_day_of_month($month_integer);
  $payday = calculate_pay_day(strtotime($last_day_of_month), $holiday_array);

	// Print all information to the file.
  // Not necessary to create a function to write generic columns to a file,
  // because the amount of columns is expected to stay the same for a while.
	fwrite($myfile, $month . ',' . $bonus_day . ',' . $payday . "\n");
}

fclose($myfile);

/**
 * Get an array of all months of the year.
 *
 * @return array
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
 *
 * @param $timestamp
 *
 * @return bool
 */
function isWeekend($timestamp) {
	if (date('N', $timestamp) < 6) {
	  // For the sake of clean coding, I choose to not use a variable to return.
    // Instead, I return it straight away.
		return FALSE;
	}
  return TRUE;
}

/**
 * Calculate the bonus day, based on the given date.
 * The default bonus day is the 15th. If that date falls in a weekend,
 * the next payday is the first upcoming wednesday.
 *
 * @param $date_timestamp
 *
 * @return false|string
 */
function calculate_bonus_day($timestamp){
  $week_day = !isWeekend($timestamp);
  // For clean code, initialize the bonus day as the least complicated scenario.
  // That way, the "if" will only be entered when it's necessary.
  $bonus_day = '15';
  if (!$week_day) {
    $bonus_day_timestamp = strtotime('next wednesday', $timestamp);
    $bonus_day = date('d', $bonus_day_timestamp);
  }
  return $bonus_day;
}

/**
 * Return the last day of the given month as an integer.
 *
 * @param $month_int
 *
 * @return int
 */
function last_day_of_month ($month_int) {
  // Tried this, didn't work.
  //return date('t', $timestamp);

  // If the month is February, return 28 if it's not a leap year. Else, return
  // 29.
  if ($month_int === 2) {
    if (date('L', $month_int === 1)) {
      return 29;
    }
    return 28;
  }

  // If the month is April, June, September or November,
  // return 30.
  $short_months = array(4, 6, 9, 11);
  if (in_array($month_int, $short_months, TRUE)) {
    return 30;
  }

  return 31;
}

/**
 * Calculates the payday based on the given date.
 * If the given date is a weekday, the alleged payday is the given date.
 * If not, the alleged payday is the last thursday before the given date.
 * If the alleged payday falls in a holliday, it is changed to the first weekday
 * before the holliday.
 *
 * @param $timestamp
 * @param $holiday_array
 *
 * @return false|string
 */
function calculate_pay_day($timestamp, $holiday_array){
  if (isWeekend($timestamp)) {
    $timestamp = strtotime('last thursday',$timestamp);
  }

  $holiday = holiday($timestamp, $holiday_array);
  if (!empty($holiday)) {
    $full_date = first_weekday_before($holiday.'-' . date('Y'), $holiday_array);
    return date('j', strtotime($full_date));
  }

  return date ('j', $timestamp);
}

/**
 * Return the holiday of the given timestamp if the timestamp is a date in the
 * given holiday array.
 *
 * @param $timestamp
 * @param $holiday_array
 *
 * @return false|null|string
 */
// Ik heb ervoor gekozen om dit op te lossen met een externe array, omdat
// de vakantiedagen per bedrijf en land kunnen verschillen en op deze manier is
// het volledig configureerbaar.
function holiday($timestamp, $holiday_array) {
  $date = date('j-n', $timestamp);
  if (in_array($date, $holiday_array, TRUE)) {
    return $date;
  }

  return null;
}

/**
 * Calculate the first weekday before the given date according to the holiday
 * array.
 *
 * @param $date
 * @param $holiday_array
 *
 * @return false|string
 */
function first_weekday_before($date, $holiday_array) {
  do {
    $timestamp = strtotime('-1 day', strtotime($date));
    $date = date('j-n-Y', $timestamp);
    $holiday = holiday(strtotime($date.'-'  . date('Y')), $holiday_array);
  }
  while ($holiday != NULL || isWeekend($timestamp));

  return $date;
}