<?php

libxml_use_internal_errors(true);

$files = array();
$dir = new DirectoryIterator("./output/");
foreach ($dir as $fileinfo) {
	if ($fileinfo->isDot()) {
		continue;
	}

	$files[] = basename($fileinfo->getFilename());
}

$groupFindings = array();
$miscFindings = array();

$groupings = array('ansible/');

sort($files);
$orderedFiles = array_reverse($files);
foreach ($orderedFiles as $file) {
	$date = basename($file);

	$dom = new DomDocument;
	$dom->loadHTMLFile("./output/".$date);
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
				preg_match("|<a.+href=\"/(([^/]+/([^/]+))/[^\"]+)\"[^>]+>([^<]+)</a>|", $html, $values);

				$finding =  "";
				$finding .= "<tr><td>";
				$finding .= $date;
				$finding .= "</td><td>";
				$finding .= "<a href=\"/".$values[1]."\">".$values[4]."</a>";
				$finding .= "</td></tr>";

				$finding .= "\n";

				$group = null;
				foreach ($groupings as $grouping) {
					if (strpos($values[2], $grouping) === 0) {
						$group = $grouping;
					}
				}

				if ($group != null) {
					$groupFindings[$group][] = $finding;
				} else {
					$miscFindings[] = $finding;
				}
			}
		}
	}
}

echo "<table>";
foreach ($groupFindings as $name => $finding) {
	echo "<thead><tr><td colspan=\"2\" align=\"center\"><strong>".$name."</strong></td></tr></thead>";
	echo "<tbody>";
	foreach ($finding as $request) {
		echo $request."\n";
	}
	echo "</tbody>";
}
echo "</table>";

echo "<table>";
echo "<thead><tr><td colspan=\"2\" align=\"center\"><strong>misc</strong></td></tr></thead>";
echo "<tbody>";
foreach ($miscFindings as $request) {
	echo $request."\n";
}
echo "</tbody>";
echo "</table>";
