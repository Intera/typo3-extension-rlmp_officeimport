<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003 Kasper Skårhøj (kasper@typo3.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * rlmp_officeimport module cm1
 *
 * @author	Kasper Skårhøj <kasper@typo3.com>
 */

$LANG->includeLLFile('EXT:rlmp_officeimport/cm1/locallang_mod.xml');

/**
 * @author	Kasper Skårhøj <kasper@typo3.com>
 */
class tx_rlmpofficeimport_cm1 extends t3lib_SCbase {

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct() {
		$this->backPath = $GLOBALS['BACK_PATH'];
			// Set key for CSH
		$this->cshKey = '_MOD_' . $GLOBALS['MCONF']['name'];
	}

	/**
	 * Initializes the backend module
	 *
	 * @return	void
	 */
	public function init() {
		parent::init();

			// Initialize document
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->setModuleTemplate(t3lib_extMgm::extPath('rlmp_officeimport') . 'cm1/mod_template.html');
		$this->doc->backPath = $this->backPath;
		$this->doc->bodyTagId = 'typo3-mod-php';
		$this->doc->bodyTagAdditions = 'class="tx_rlmpofficeimport_cm1"';
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 *
	 * @return	void
	 */
	public function main() {

			// Access check!
			// The page will show only if user has admin rights
		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id, $this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		if ($this->id && $access)	{

				// Set the form
			$this->doc->form = '<form name="tx_rlmpofficeupload_form" id="tx_rlmpofficeupload_form" method="post" action="" enctype="multipart/form-data">';

				// JavaScript for main function menu
			$this->doc->JScode = '
				<script language="javascript" type="text/javascript">
					script_ended = 0;
					function jumpToUrl(URL) {
						document.location = URL;
					}
				</script>
			';

				// Prepare main content
			$this->content  = $this->doc->header(
				$GLOBALS['LANG']->getLL('title')
			);
			$this->content .= $this->doc->spacer(5);
			$this->content .= $this->getModuleContent();
		} else {

			if(!intval($this->id)) {
				$this->addMessage('PageID was not set');
			}
			else {
				$this->addMessage('You don\'t have the permission to add content to this page');
			}

				// If no access, only display the module's title
			$this->content  = $this->doc->header($GLOBALS['LANG']->getLL('title'));
			$this->content .= $this->doc->spacer(5);
		}

			// Place content inside template
		$content  = $this->doc->startPage($GLOBALS['LANG']->getLL('title'));
		$content .= $this->doc->moduleBody(
			array(),
			$this->getDocHeaderButtons(),
			$this->getTemplateMarkers()
		);
		$content .= $this->doc->endPage();

			// Replace content with templated content
		$this->content = $content;
	}

	/**
	 * Creates the module content
	 *
	 * @return	void
	 */
	function getModuleContent()	{
		global $LANG;

			// Get "type":
		$type = t3lib_div::GPvar('doctype');

		if (is_array($_FILES['_uploaded_office_file']))	{
			$tmpFileName = PATH_site.'typo3temp/ext_rlmp_officeimport_'.substr(md5(microtime()),0,10);
			if (!@is_file($tmpFileName))	{
				t3lib_div::upload_copy_move($_FILES['_uploaded_office_file']['tmp_name'],$tmpFileName);
				if (@is_file($tmpFileName))	{
						// Reset variable with RTE content:
					$RTEcontent = '';
						// Based on filetype, get the content:
					switch($type)	{
						case 1:	// Word files:
							$fileContent = t3lib_div::getUrl($tmpFileName);
							$firstFewBytes = substr($fileContent,0,200);
							if (strstr($firstFewBytes,'<?mso-application progid="Word.Document"?>'))	{
								$msOffice2003 = t3lib_div::makeInstance ('tx_rlmpofficeimport_msoffice2003');
								$RTEcontent = $msOffice2003->mainWord($fileContent, $conf);
							} else $this->addMessage($LANG->getLL('error_notwordfile'), t3lib_FlashMessage::ERROR);
						break;
						case 2:	// Excel files:
							$fileContent = t3lib_div::getUrl($tmpFileName);
							$firstFewBytes = substr($fileContent,0,200);
							if (strstr($firstFewBytes,'<?mso-application progid="Excel.Sheet"?>'))	{
								$msOffice2003 = t3lib_div::makeInstance ('tx_rlmpofficeimport_msoffice2003');
								$RTEcontent = $msOffice2003->mainExcel($fileContent, $conf);
							} else $this->addMessage($LANG->getLL('error_notexelfile'), t3lib_FlashMessage::ERROR);
						break;
						case 3:	// Open Office Writer:
							$ooDocObj = t3lib_div::makeInstance ('tx_rlmpofficeimport_openoffice');
							$RTEcontent = $ooDocObj->mainWriter($tmpFileName, $conf);
							$ooDocObj->unzipObj->clearCachedContent();
						break;
/*						case 4:	// Open Office Calc:
							$ooDocObj = t3lib_div::makeInstance ('tx_rlmpofficeimport_openoffice');
							$RTEcontent = $ooDocObj->mainCalc($tmpFileName, $conf);
							$ooDocObj->unzipObj->clearCachedContent();
						break;*/
					}

					if (strlen($RTEcontent))	{
						$newId = $this->createContentElement($RTEcontent);
						if ($newId)	{
							$loc = t3lib_div::locationHeaderUrl($GLOBALS['BACK_PATH'].'alt_doc.php?edit[tt_content]['.$newId.']=edit');
							header('Location: '.$loc);
							exit;
						} else debug('ERROR: No content element was created.');
					} else $this->addMessage($LANG->getLL('error_nocontentextracted'));

					if (t3lib_div::isFirstPartOfStr($tmpFileName,PATH_site.'typo3temp/ext_rlmp_officeimport_'))	{
						unlink($tmpFileName);
					} else $this->addMessage($LANG->getLL('error_fileoutsidetemp'));
				} else $this->addMessage($LANG->getLL('error_notfileuploaded'));
			} else debug('error_tempfileinuse');
		}

		$preText = '';

		switch($type)	{
			case 1:
				$preText.='<br /><p>' . $this->getIcon('cm_icon1.gif', $LANG->getLL('doctype1-icon')) . ' ' . $LANG->getLL('doctype1').'<br /><strong>'.$LANG->getLL('lbl_notice').'</strong> '.$LANG->getLL('doctype1_1').'</p><br />';
			break;
			case 2:
				$preText.='<br /><p>' . $this->getIcon('cm_icon2.gif', $LANG->getLL('doctype2-icon')) . ' ' .$LANG->getLL('doctype2').'<br /><strong>'.$LANG->getLL('lbl_notice').'</strong> '.$LANG->getLL('doctype2_1').'</p><br />';
			break;
			case 3:
				$preText.='<br /><p>' . $this->getIcon('cm_icon3.gif', $LANG->getLL('doctype3-icon')) . ' ' .$LANG->getLL('doctype3').'<br /></p><br />';
			break;
			/*case 4:
				$preText.='<br /><p>' . $this->getIcon('cm_icon4.gif', $LANG->getLL('doctype4-icon')) . ' ' .$LANG->getLL('doctype4').'<br /></p><br />';
			break;*/
		}
		$content .= $preText;
		$content .= '<input type="file" name="_uploaded_office_file" style="width:400px;" /><br />';
		$content .= '<input type="submit" name="Upload" value="'.$LANG->getLL('upload').'">
			<input type="hidden" name="doctype" value="' . htmlspecialchars($type) . '">
			<input type="hidden" name="id" value="' . htmlspecialchars($this->id) . '">';

		$this->content .= $this->doc->section($LANG->getLL('uploadformtitle'), $content, 0, 1);
	}

	protected function getIcon($iconFile, $title='') {
		$icon = ' <img ' . t3lib_iconWorks::skinImg(t3lib_extMgm::extRelPath('rlmp_officeimport'), 'cm1/' . $iconFile) . ' alt="' . $title . '" title="' . $title . '" />';
		return $icon;
	}

	function createContentElement($RTEcontent)	{
		global $LANG;
		$id = intval(t3lib_div::GPvar('id'));

		if ($id>0)	{
			$tce = t3lib_div::makeInstance("t3lib_TCEmain");
			$tce->stripslashes_values=0;
			$data=array();
			$data['tt_content']['NEW']['bodytext']=$RTEcontent;
			$data['tt_content']['NEW']['header']=$LANG->getLL('imported_office_file');
			$data['tt_content']['NEW']['pid']=$id;

			$tce->start($data,array());
			$tce->process_datamap();

			return $tce->substNEWwithIDs['NEW'];
		} else debug('ERROR: No ID value!!');
	}

	/**
	 * This method is used to add a message to the internal queue
	 *
	 * @param	string	the message itself
	 * @param	integer	message level (-1 = success (default), 0 = info, 1 = notice, 2 = warning, 3 = error)
	 * @return	void
	 */
	public function addMessage($message, $severity = t3lib_FlashMessage::ERROR) {
		$message = t3lib_div::makeInstance(
			't3lib_FlashMessage',
			$message,
			'',
			$severity
		);

		t3lib_FlashMessageQueue::addMessage($message);
	}

	public function render() {
		echo $this->content;
	}

	/**
	 * Gets the filled markers that are used in the HTML template.
	 *
	 * @return	array		The filled marker array
	 */
	protected function getTemplateMarkers() {
		$markers = array(
			'CONTENT'   => $this->content,
			'TITLE'     => $GLOBALS['LANG']->getLL('title'),
		);

		return $markers;
	}

	protected function getDocHeaderButtons() {
		return array(
			'shortcut' => $this->getShortcutButton()
		);
	}

	/**
	 * Gets the button to set a new shortcut in the backend (if current user is allowed to).
	 *
	 * @return	string		HTML representiation of the shortcut button
	 */
	protected function getShortcutButton() {
		$result = '';
		if ($GLOBALS['BE_USER']->mayMakeShortcut()) {
			$result = $this->doc->makeShortcutIcon('', 'function', $this->MCONF['name']);
		}

		return $result;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rlmp_officeimport/cm1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rlmp_officeimport/cm1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_rlmpofficeimport_cm1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE) {
	include_once($INC_FILE);
}
require_once(t3lib_extMgm::extPath('rlmp_officeimport').'class.tx_rlmpofficeimport_msoffice2003.php');
require_once(t3lib_extMgm::extPath('rlmp_officeimport').'class.tx_rlmpofficeimport_openoffice.php');

$SOBE->main();
$SOBE->render();
?>