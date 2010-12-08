<?php

function ef_news_init() {
	return true;
}

function efNews_display_recent($recent_entries = 5) {
	global $c, $r;

	/* this code gets the $recent_entries most recent news entries */
	$news_base = $c['ext']['news']['base'];

	$years = array();
	$entries = array();

	if ($handle = opendir($news_base)) {
		while (false !== ($dir = readdir($handle))) {
			if ($dir != '..' && $dir != '.' && is_dir("$news_base/$dir")) {
				array_push($years, $dir);
			}
		}
		closedir($handle);
		rsort($years, $sort_flags = SORT_NUMERIC);
		
		$i = 1;
		foreach ($years as $year) {
			if ($i <= $recent_entries && $handle = opendir("$news_base/$year")) {
				while (false !== ($entry = readdir($handle)) && $i <= $recent_entries) {
					if ($i <= $recent_entries 
						&& $entry != '..' 
						&& $entry != '.' 
						&& is_file("$news_base/$year/$entry") 
						&& substr($entry, 0, 1) != '.' 
					) {
						array_push($entries, "$year/$entry");
						$i++;
					}
				}
				closedir($handle);
			}
		}
		rsort($entries, $sort_flags = SORT_STRING);

		/* this is where we actually include the text from each news entry */
		foreach ($entries as $entry) {
			$cut = false;
			echo "<dd>\n";
			if ($handle = fopen("$news_base/$entry", "r")) {
				while (!feof($handle) && ! $cut) {
					$matches = array();
					$line = fgets($handle);
					if (preg_match_all("/<!--title-->(.+)<!--\/title-->/", $line, $matches, PREG_SET_ORDER)) {
						echo "<span class=\"newstitle\"><a href=\"/news-archive/$entry\">{$matches[0][0]}</a></span>\n";
						echo "<br />\n";
					} elseif (preg_match_all("/^<!--cut-->$/", $line, $matches, PREG_SET_ORDER)) {
						echo "<p class=\"fullarticle\"><strong>...</strong>\n";
						echo "<a href=\"/news-archive/$entry\">Read Full Article</a></p>\n";
						$cut = true;
					} else {
						echo $line;
					}
				}
				fclose($handle);
			} else {
				echo "<p>Unable to open article $entry for reading.</p>\n";
			}
			#echo "<p class=\"permalink\"><a href=\"/news-archive/$entry\">permalink</a></p>\n";
			echo "</dd>\n";
		}
	} else {
		echo "<p>Unable to get news-archive years - no news to display.</p>";
	}
	?>
	</dl>
	<p style="text-align: right"><em>Displaying <?php echo $recent_entries ?> most recent entries.</em> 
	<a href="/news-archive">View Older Entries</a></p>
	<?php
}

function efNews_display_archive() {
	global $c;

	$news_base = $c['ext']['news']['base'];
	$archive_loc = $c['ext']['news']['archiveLoc'];

	$recent_entries = 1000;

	$years = array();
	$entries = array();

	if ($handle = opendir($news_base)) {
		while (false !== ($dir = readdir($handle))) {
			if ($dir != '..' && $dir != '.' && is_dir("$news_base/$dir")) {
				array_push($years, $dir);
			}
		}
		closedir($handle);
		rsort($years, $sort_flags = SORT_NUMERIC);
		
		$i = 1;
		foreach ($years as $year) {
			if ($i <= $recent_entries && $handle = opendir("$news_base/$year")) {
				while (false !== ($entry = readdir($handle)) && $i <= $recent_entries) {
					if ($i <= $recent_entries 
						&& $entry != '..' 
						&& $entry != '.' 
						&& is_file("$news_base/$year/$entry") 
						&& substr($entry, 0, 1) != '.' 
					) {
						array_push($entries, "$year/$entry");
						$i++;
					}
				}
				closedir($handle);
			}
		}
		rsort($entries, $sort_flags = SORT_STRING);

		/* this is where we actually include the text from each news entry */
		echo "<ul>\n";
		foreach ($entries as $entry) {
			$handle = fopen("$news_base/$entry", "r");
			$contents = fread($handle, 1024);
			if (preg_match_all('/.*\<h1>(.+)<\/h1>.*/U', $contents, $matches) != FALSE) {
				$title = $matches[1][0];
			} else {
				$title = 'NOTITLE';
			}
			echo "<li>\n";
			echo "$entry: <a href=\"$archive_loc/$entry\">$title</a>\n";
			echo "</li>\n";
		}
		echo "</ul>\n";
	} else {
		echo "<p>Unable to get news-archive years - no news to display.</p>";
	}
}

?>
