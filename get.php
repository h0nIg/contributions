<?php

$begin    = new DateTime('2012-01-01');
$end      = new DateTime();
$interval = DateInterval::createFromDateString('1 day');
$days     = new DatePeriod($begin, $interval, $end);
$reversedays = array_reverse(iterator_to_array($days));

$cookie = '';

foreach ( $reversedays as $day ) {
	$date = date_format($day, 'Y-m-d');

	$curl = curl_init("https://github.com/h0nIg?tab=overview&from=".$date);
	curl_setopt($curl, CURLOPT_COOKIE, $cookie);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

	$content = curl_exec($curl);
	curl_close($curl);

	file_put_contents("./output/".$date, $content);

	// 2,5 sec
	usleep(2500000);
}

