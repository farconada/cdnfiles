<?php

########################################################################
# Extension Manager/Repository config file for ext "dam3s".
#
# Auto generated 19-02-2010 17:05
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Replace local files for CDN URLs',
	'description' => 'Replace local files for CDN URLs',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.9.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Fernando Arconada',
	'author_email' => 'fernando.arconada@gmail.com',
	'author_company' => '',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '4.0.0-0.0.0',
			'typo3' => '3.8.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:7:{s:9:"ChangeLog";s:4:"2f90";s:21:"ext_conf_template.txt";s:4:"22ad";s:12:"ext_icon.gif";s:4:"6103";s:14:"ext_tables.php";s:4:"c645";s:14:"doc/manual.sxw";s:4:"ec3d";s:41:"modfunc_index/class.tx_dam3s_s3upload.php";s:4:"4a99";s:27:"modfunc_index/locallang.xml";s:4:"8fe4";}',
	'suggests' => array(
	),
);

?>