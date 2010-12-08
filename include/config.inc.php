<?php

$c['siteName'] = 'CCGB';
$c['baseDir'] = getcwd();
$c['pageDir'] = $c['baseDir'] . '/pages';
$c['fallbackPath'] = 'old';
$c['extDir'] = $c['baseDir'] . '/extensions';

/* These are the pages for which we want proper titles 
 * key is what will be in the url
 * value is the title to be used for that page and menu link
 *
 * menu is generated in the order these items are listed in the array
 */
$c['nav'] = array(
	'index' => 'Home / News',
	'about' => 'About',
	'courses' => 'Courses',
	'research' => 'Research',
	'projects' => 'Projects',
	'people' => 'People',
	'contact' => 'Contact'
);

$c['pageMap'] = array(
	'synopsis.html' => 'about',
	'archived.html' => 'news-archive',
	'schedule.html' => 'schedule',
	'courses.html' => 'courses'
);

/* extension configuration */
$c['ext']['news'] = array(
	'base' => $c['pageDir'] . '/news-archive',
	'archiveLoc' => '/news-archive'
);

ext_load('news');

?>
