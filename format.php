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
                                $finding .= "<a href=\"https://github.com/".$values[2]."\">".$values[2]."</a>";
                                $finding .= "</td><td>";
                                $finding .= "<a href=\"https://github.com/".$values[1]."\">".$values[4]."</a>";
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

?>


<strong>minimalistic overview of my opensource contributions</strong>
<ul>
<li>github activities are displayed only for the last year</li>
<li>grouped by topic</li>
</ul>
<br /><br />

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
<br />

