<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

if (TYPO3_MODE=='BE')	{
	$GLOBALS['TBE_MODULES_EXT']['xMOD_alt_clickmenu']['extendCMclasses'][]=array(
		'name' => 'tx_rlmpofficeimport_cm1',
		'path' => t3lib_extMgm::extPath($_EXTKEY).'class.tx_rlmpofficeimport_cm1.php'
	);

	t3lib_extMgm::addModulePath('xMOD_tx_rlmpofficeimport_cm1', t3lib_extMgm::extPath($_EXTKEY, 'cm1/'));

	// The new TYPO3 context menu is build with TSconfig
	$itemId = 100;
	$officeimportActions = '';

	for ($i = 1; $i <= 3; $i++) {
		$officeimportActions .= '
			' . $itemId . ' = ITEM
			' . $itemId . ' {
				name = officeImport
				label = LLL:EXT:rlmp_officeimport/locallang.php:cm1_title' . $i . '
				spriteIcon = actions-document-import-t3d
				callbackAction = rlmpOfficeImport' . $i . '
			}
		';
		$itemId += 100;
	}

	$GLOBALS['TYPO3_CONF_VARS']['BE']['defaultUserTSconfig'] .= '
		options.contextMenu.table {
			pages.items.709 = DIVIDER
			pages.items.710 = SUBMENU
			pages.items.710 {

				label = LLL:EXT:rlmp_officeimport/locallang.php:cm1_title_activate

				' . $officeimportActions . '
			}
			pages.items.711 = DIVIDER
		}
	';

	// We register ourselves as a navigation component. This allows us to load custom
	// JS in the Backend which is needed to provide the configured callbackAction methods.
	t3lib_extMgm::addNavigationComponent('rlmpofficeimport', 'rlmpofficeimport-importcontextmenu');
}

$tempColumns = Array (
	'tx_rlmpofficeimport_office_file' => Array (
		'exclude' => 1,
		'label' => 'LLL:EXT:rlmp_officeimport/locallang_db.php:tt_content.tx_rlmpofficeimport_office_file',
		'config' => Array (
			'type' => 'group',
			'internal_type' => 'file',
			'allowed' => '',
			'disallowed' => 'php,php3',
			'max_size' => 10000,
			'uploadfolder' => 'uploads/tx_rlmpofficeimport',
			'show_thumbs' => 1,
			'size' => 1,
			'minitems' => 0,
			'maxitems' => 1,
		)
	),
);

t3lib_div::loadTCA('tt_content');
t3lib_extMgm::addTCAcolumns('tt_content',$tempColumns,1);

t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='tx_rlmpofficeimport_office_file;;;;1-1-1';

t3lib_extMgm::addPlugin(Array('LLL:EXT:rlmp_officeimport/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');

t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','Office 2003 / Open Office Displayer');
?>