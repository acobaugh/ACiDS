<?php

error_reporting(E_ALL &~ E_NOTICE);
ini_set('display_errors', 1);

include(getcwd() . "/include/includes.inc.php");

$r['q'] = sanitize_request($_GET['q']);

page_map($r['q']);

/* if the page doesn't exist under /pages, even after mapping, try to fallback 
 * to sticking $fallBackPath in front of $q
 */
fallback($r['q']);

/* header, display_menu(), end div that contains menu */
include("{$c['pageDir']}/header");
display_menu($r['q']);
echo '</div><!-- END div id=header -->' . "\n\n";

display_errors();

display_breadcrumbs($r['q']);

display_lsidebar($r['q']);

/* meh */
if (empty($sidebar)) {
	$contentstyle = 'margin-left: 200px';
} else {
	$contentstyle = 'margin-left: 200px';
}

echo "<div id=\"content\" style=\"$contentstyle\">" . "\n";
/* include the content */
display_content($r['q']);
echo '</div><!-- END div id=content -->' . "\n\n";

/* footer */
include($c['pageDir'] . '/footer');

?>
