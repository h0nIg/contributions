<?php

$begin    = new DateTime('2012-01-01');
$end      = new DateTime();
$interval = DateInterval::createFromDateString('1 day');
$days     = new DatePeriod($begin, $interval, $end);
$reversedays = array_reverse(iterator_to_array($days));

foreach ( $reversedays as $day ) {
	$date = date_format($day, 'Y-m-d');

	$content = file_get_contents("https://github.com/h0nIg?tab=overview&from=".$date);
	file_put_contents("./output/".$date, $content);

	// 2,5 sec
	usleep(2500000);
}

