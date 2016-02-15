<?php

libxml_use_internal_errors(true);

$begin    = new DateTime('2012-11-09');
$end      = new DateTime();
$interval = DateInterval::createFromDateString('1 day');
$days     = new DatePeriod($begin, $interval, $end);
$reversedays = array_reverse(iterator_to_array($days));

echo "<table>";
foreach ( $reversedays as $day ) {
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

			foreach ($contributions as $contribution) {
				$html = $dom->saveXML($contribution);
				$html = str_replace("\n", "", $html);

				# Pushed XXX commits to XXX
				if (strstr($html, " Pushed ")) {
					continue;
				}

				$values = null;
				preg_match("|<a.+href=\"/(([^/]+)/([^/]+)/[^\"]+)\"[^>]+>([^<]+)</a>|", $html, $values);

				echo "<tr><td>";
				echo $date;
				echo "</td><td>";
				echo "<a href=\"/".$values[2]."/".$values[3]."\">".$values[3]."</a>";
				echo "</td><td>";
				echo "<a href=\"/".$values[1]."\">".$values[4]."</a>";
				echo "</td></tr>";

				echo "\n";
			}
		}
	}

	// 2,5 sec
	usleep(2500000);
}
echo "</table>";
