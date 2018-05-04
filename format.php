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

$groupings = array('ansible', 'graylog', 'memcached-session-manager', 'gelfj');

sort($files);
$orderedFiles = array_reverse($files);
foreach ($orderedFiles as $file) {
	$date = basename($file);

	$dom = new DomDocument;
	$dom->loadHTMLFile("./output/".$date);
	$xpath = new DomXPath($dom);
	$nodes = $xpath->query("//div[@id='js-contribution-activity']");

	foreach ($nodes as $node) {
		if (!strstr($node->nodeValue, "had no activity during this period")) {

			$xmlvalue = $dom->saveXML($node);
			$dom->loadXML($xmlvalue);

			$xpath = new DomXPath($dom);

			$results = array();

			$contributions = $xpath->query("//a[contains(@href, '/issues/')] | //a[contains(@href, '/pull/')]");
			foreach ($contributions as $contribution) {
				$results[] = $contribution;
			}

			$fragments = $xpath->query("//include-fragment[contains(@src, 'created_issues')] | //include-fragment[contains(@src, 'created_pull_requests')]");
			foreach ($fragments as $fragment) {
				$links = file_get_contents("https://github.com" . $fragment->getAttribute("src"));
				
				$fragdom = new DomDocument;
				$fragdom->loadXML($links);
				$fragxpath = new DomXPath($fragdom);
			       
				$linksresults = $fragxpath->query("//a[contains(@href, '/issues/')] | //a[contains(@href, '/pull/')]");
				foreach ($linksresults as $linksresult) {
			       		$results[] = $linksresult;
				}
			}

			foreach ($results as $result) {
				// Issue title is private
				if ($result->hasAttribute("data-error-text")) {
					continue;
				}
				// Embedded links to foreign sites
				if (strstr($result->getAttribute("href"), "http")) {
					continue;
				}
				$notrequired = array("/h0nIg/cli/pull/1", "/jenkinsci/docker-plugin/issues/482", "/ansible/ansible/issues/20942", "/ansible/ansible/pull/27127", "/ansible/ansible-modules-extras/pull/2922");
				if (in_array($result->getAttribute("href"), $notrequired)) {
					continue;
				}

				$values = null;
				preg_match("|/(([^/]+/([^/]+))/.+)|", $result->getAttribute("href"), $values);

				$linktext = htmlspecialchars(trim(str_replace("\n", "", $result->nodeValue)));

				$finding =  "";
				$finding .= "<tr><td>";
				$finding .= $date;
				$finding .= "</td><td>";
				$finding .= "<a href=\"https://github.com/".$values[2]."\">".$values[2]."</a>";
				$finding .= "</td><td>";
				$finding .= "<a href=\"https://github.com/".$values[1]."\">".$linktext."</a>";
				$finding .= "</td></tr>";

				$finding .= "\n";

				$group = null;
				foreach ($groupings as $grouping) {
					if (strstr($values[2], $grouping) !== false) {
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

echo file_get_contents("header.html");
?>


<strong>minimalistic overview of my opensource contributions</strong>
<ul>
<li>github activities are displayed only for the last year</li>
<li>grouped by topic</li>
</ul>
<br /><br />

<table>
<thead>
<tr><td colspan="3" align="center"><strong>other</strong></td></tr>
<tr><td><strong>date</strong></td><td><strong>repository</strong></td><td><strong>description</strong></td></tr>
</thead><tbody>
<?php

foreach ($miscFindings as $request) {
	echo $request."\n";
}

?>
</tbody>
</table>
<table>
<?php

foreach ($groupFindings as $name => $finding) {

	?>
	<thead>
	<tr><td colspan="3" align="center"><br /></td></tr>
	<tr><td colspan="3" align="center"><strong><?php echo $name; ?></strong></td></tr>
	<tr><td><strong>date</strong></td><td><strong>repository</strong></td><td><strong>description</strong></td></tr>
	</thead><tbody>
	<?php

	foreach ($finding as $request) {
		echo $request."\n";
	}

	?>
	</tbody>
	<?php
}

?>
</table>
<br />
<?php
echo file_get_contents("footer.html");
?>

