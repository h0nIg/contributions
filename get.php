<?php

libxml_use_internal_errors(true);

$begin    = new DateTime('2012-11-09');
$end      = new DateTime();
$interval = DateInterval::createFromDateString('1 day');
$days     = new DatePeriod($begin, $interval, $end);

foreach ( $days as $day ) {
	$date = date_format($day, 'Y-m-d');

	$dom = new DomDocument;
	$dom->loadHTMLFile("https://github.com/h0nIg?tab=contributions&from=".$date);
	$xpath = new DomXPath($dom);
	$nodes = $xpath->query("//div[@class='contribution-activity-listing']");

	foreach ($nodes as $node) {
		if (!strstr($node->nodeValue, "has no activity during this period")) {

			$xmlvalue = $dom->saveXML($node);
			$dom->loadXML($xmlvalue);

			$xpath = new DomXPath($dom);
			$contributions = $xpath->query("//a[@class='title']");
			//$contributions = $xpath->query("//a[not(contains(@class, 'title'))]");

			foreach ($contributions as $contribution) {
				$contributionValue = $dom->saveXML($contribution);
				echo $date." ".str_replace("\n", "", $contributionValue)."<br />\n";
			}
		}
	}
}
