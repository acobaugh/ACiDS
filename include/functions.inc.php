<?php

function display_content($page = 'home') {
	global $c, $r;
	/* check if the page even exists */
	if (is_file($c['pageDir'] . "/$page")) {
		include($c['pageDir'] . "/$page");
	} elseif (is_file($c['pageDir'] . "/$page/index")) {
		include($c['pageDir'] . "/$page/index");
	} else {
		/* if the page doesn't exist */
		include($c['pageDir'] . "/404");
	}
}

function display_menu($currentpage) {
	global $c, $r;

	echo '<div id="navcontainer">' . "\n";
	echo '<ul id="navlist">' . "\n";
	
	$crumbs = explode ('/', $currentpage);
	foreach ($c['nav'] as $page => $title) {
		if ($page == $crumbs[0])	{
			echo '<li id="active"><a href="/';
			echo	$page;
			echo '" id="current">';	
		} else {
			echo '<li><a href="/';
			echo $page;
			echo '">';
		}
		
		if (empty($title)) {
			$title = $page;
		}
		echo $title;
		echo '</a></li>' . "\n";

	}
	echo "</ul>\n";
	echo "</div><!-- END div id=navcontainer -->\n\n";
} /* END function print_menu() */

function page_map($q = NULL) {
	global $c, $r;
	if ($q != NULL) {
		if (array_key_exists($q, $c['pageMap'])) {
			$q = $c['pageMap'][$q];
			header("Location: /$q");
		}
	}
} /* END function page_map() */

function fallback($q = NULL) {
	global $c, $r;

	if ($q != NULL && !empty($c['fallbackPath'])) {
		if (!is_readable("{$c['pageDir']}/$q") && is_readable("{$c['baseDir']}/{$c['fallbackPath']}/$q")) {
			redirect("/{$c['fallbackPath']}/$q");
		}
	}
}

function redirect($uri = NULL) {
	if ($uri != NULL) {
		$host = $_SERVER['SERVER_NAME'];
		$port = $_SERVER['SERVER_PORT'];

		if ($_SERVER['HTTPS'] == 'on') {
			$proto = 'https';
		} else {
			$proto = 'http';
		}

		$url = $proto . '://' . $host . ':' . $port . $uri;
		header("Location: $url");
		exit;
	}
}
function display_breadcrumbs($q = NULL) {
	global $c, $r;

	/* breadcrumb navigation thingy */
	echo "<div id=\"breadcrumbs\"><a href=\"/\">{$c['siteName']}</a>";
	if ($q != NULL) {
		$crumbs = explode('/', $q);
		$path = '';
		foreach ($crumbs as $crumb)
		{
			$path = $path . '/' . $crumb;
			echo " &gt; ";
			if (basename($q) == $crumb)
			{
				echo "$crumb";
			} else {
				echo "<a href=\"$path\">$crumb</a>";
			}
		}
	}
	echo '</div>';
}

function display_lsidebar($q = NULL) {
	global $c, $r;

	if ($q != NULL) {
		/* find a sidebar for this page
		 * looks for page-sidebar in the page's directory
		 * then sidebar in the page's directory, and every directory above that
		 */
		$sidebar = '';

		/* if we're in a subdir and we want the index, set $q to that */
		if (is_dir($c['pageDir'] . "/$q")) {
			$q = $q . "/index";
		}
		$dir = dirname("/$q");
		$file = basename($q);

		/* find a sidebar specific to this page, foo-sidebar */
		if (is_file($c['pageDir'] . "/$q")) {
			$file = basename($q);
			clearstatcache();
			if (file_exists($c['pageDir'] . "/$dir/$file-sidebar")) {
				$sidebar = $c['pageDir'] . "/$dir/$file-sidebar";
			} 
		} elseif (is_dir($c['pageDir'] . "/$q")) {
			$dir = $q;
		}

		/* if we have no page-specific sidebar, search for one */
		if (empty($sidebar)) {
			if (file_exists($c['pageDir'] . "/$dir/sidebar")) {
				$sidebar = $c['pageDir'] . "/$dir/sidebar";
			} else {
				/* find the 'closest' sidebar file by then walking up $dir */
				while ($dir != '.' && $dir != '' && $dir != '/') {
					if (file_exists($c['pageDir'] . "/$dir/sidebar")) {
						$sidebar = $c['pageDir'] . "/$dir/sidebar";
						$dir = '.';
					}
					$dir = dirname($dir);
				}
			}
		}
		/* include the chosen sidebar */
		clearstatcache();
		if (!empty($sidebar)) {
			echo "<div id=\"lsidebar\">\n";
			include($sidebar);
			echo "</div><!-- END div id=lsidebar -->\n\n";
		}
	}
}

function sanitize_request($in = NULL) {
	if ($in != NULL) {
		$out = ereg_replace('^/+', '', trim(str_replace('..', '', $in)));
		if (empty($out)) {
			$out = 'index'; 
		}
	} else {
		$out = 'index';
	}
	return $out;
}

function display_lastmodified($q = NULL) {
	global $c;

	if ($q != NULL) {
		echo "<span id=\"lastmodified\">Last modified <strong>";
		if (file_exists($c['pageDir'] . "/$q")) {
			echo date("F d Y H:i:s", filemtime($c['pageDir'] . "/$q"));
		} else {
			echo "unknown";
		}
		echo "</strong>	by <strong>";
		if (file_exists($c['pageDir'] . "/$q")) {
			$userinfo = posix_getpwuid(fileowner($c['pageDir'] . "/$q")); 
			echo "<a href=\"mailto:"
			. $userinfo['name'] 
			. "-at-bx.psu.edu\">"
			. $userinfo['name']
			. "</a>";
		} else {
			echo "unknown";
		}
		echo "</strong></span>\n";
	}
}

function error_store($txt = NULL) {
	global $r;
	if ($txt == NULL) {
		return;
	}
	if (!is_array($r['errors'])) {
		$r['errors'] = array();
	}

	array_push($r['errors'], $txt);
}

function display_errors() {
	global $r;

	if (is_array($r['errors'])) {
		foreach ($r['errors'] as $e) {
			error_box($e, 1);
		}
	}
}

function ext_load($ext = NULL) {
	global $c;

	if ($ext == NULL) {
		return false;
	}
	
	$file = $c['extDir'] . "/$ext.inc.php";
	if (file_exists($file)) {
		if (include_once($file)) {
			$init_function = 'ef_' . $ext . 'init';
			if (function_exists($init_function)) {
				$init_function();
			}
		} else {
			error_store("ext_load(): could not include $file");
		}
	} else {
		error_store("ext_load(): file does not exist: $file");
	}
}

function ok_box($msg, $suppressHeader = 0) {
	if (!$suppressHeader) {
		echo "<h2>OK</h2>\n";
	}
	echo "<div style=\"padding: 10px; background-color: #aaeeaa ; border: 1px solid green\">$msg</div>\n";
}

function error_box($msg, $suppressHeader = 0) {
	if (!$suppressHeader) {
		echo "<h2>ERROR</h2>\n";
	}
	echo "<div style=\"margin: 1px; padding: 10px; background-color: #eeaaaa ; border: 1px solid red\">$msg</div>\n";
}

?>

