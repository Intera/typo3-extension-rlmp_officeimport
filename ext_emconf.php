<?php

########################################################################
# Extension Manager/Repository config file for ext "rlmp_officeimport".
#
# Auto generated 26-01-2010 15:40
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'General Office Displayer',
	'description' => 'Displays a Word or Excel file from Microsoft Office 2003 if saved in the new XML format. Additionally it supports Open Office Writer documents.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '1.0.5',
	'dependencies' => 'libunzipped',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'stable',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => 'tt_content',
	'clearcacheonload' => 1,
	'lockType' => '',
	'author' => 'Robert Lemke',
	'author_email' => 'rl@robertlemke.de',
	'author_company' => 'robert lemke medienprojekte',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '3.0.0-0.0.0',
			'typo3' => '3.5.0-0.0.0',
			'libunzipped' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:32:{s:33:"class.tx_rlmpofficeimport_cm1.php";s:4:"8f84";s:42:"class.tx_rlmpofficeimport_msoffice2003.php";s:4:"b275";s:40:"class.tx_rlmpofficeimport_openoffice.php";s:4:"4be1";s:33:"class.tx_rlmpofficeimport_xml.php";s:4:"f1f0";s:12:"ext_icon.gif";s:4:"3d08";s:17:"ext_localconf.php";s:4:"11fc";s:14:"ext_tables.php";s:4:"7aca";s:14:"ext_tables.sql";s:4:"4637";s:13:"locallang.php";s:4:"becd";s:16:"locallang_db.php";s:4:"aed1";s:13:"cm1/clear.gif";s:4:"cc11";s:16:"cm1/cm_icon1.gif";s:4:"301b";s:16:"cm1/cm_icon2.gif";s:4:"91f8";s:16:"cm1/cm_icon3.gif";s:4:"d9dc";s:16:"cm1/cm_icon4.gif";s:4:"1ebf";s:24:"cm1/cm_icon_activate.gif";s:4:"c435";s:12:"cm1/conf.php";s:4:"aca4";s:13:"cm1/index.php";s:4:"860f";s:17:"cm1/locallang.php";s:4:"55e7";s:17:"doc/CHANGELOG.txt";s:4:"0d44";s:12:"doc/TODO.txt";s:4:"bb96";s:14:"doc/manual.sxw";s:4:"b5a9";s:19:"doc/wizard_form.dat";s:4:"3e1f";s:20:"doc/wizard_form.html";s:4:"3992";s:37:"pi1/class.tx_rlmpofficeimport_pi1.php";s:4:"7a3d";s:17:"pi1/locallang.php";s:4:"ea4f";s:24:"pi1/static/editorcfg.txt";s:4:"d41d";s:20:"pi1/static/setup.txt";s:4:"4053";s:20:"samples/Expenses.sxc";s:4:"8037";s:20:"samples/Expenses.xml";s:4:"ebae";s:30:"samples/The Paris Incident.sxw";s:4:"8c79";s:30:"samples/The Paris Incident.xml";s:4:"0094";}',
);

?>