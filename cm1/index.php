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

	// DEFAULT initialization of a module [BEGIN]
unset($MCONF);	
require ('conf.php');
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
require_once (PATH_t3lib.'class.t3lib_scbase.php');
include ('locallang.php');
	// ....(But no access check here...)
	// DEFAULT initialization of a module [END] 
require_once(t3lib_extMgm::extPath('rlmp_officeimport').'class.tx_rlmpofficeimport_msoffice2003.php');
require_once(t3lib_extMgm::extPath('rlmp_officeimport').'class.tx_rlmpofficeimport_openoffice.php');
require_once(PATH_t3lib.'class.t3lib_tcemain.php');

/**
 * @author	Kasper Skårhøj <kasper@typo3.com>
 */
class tx_rlmpofficeimport_cm1 extends t3lib_SCbase {

	/**
	 * Main function of the module. Write the content to $this->content
	 * 
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS;
		
			// Draw the header.
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->docType='xhtml_trans';
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="" method="post" enctype="'.$TYPO3_CONF_VARS['SYS']['form_enctype'].'">';

			// JavaScript
		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
				/*<![CDATA[*/
					script_ended = 0;
					function jumpToUrl(URL)	{
						document.location = URL;
					}
				/*]]>*/
			</script>
		';

		$this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
		$access = is_array($this->pageinfo) ? 1 : 0;
		if ($this->id && $access)	{
			$headerSection = $this->doc->getHeader('pages',$this->pageinfo,$this->pageinfo['_thePath']).'<br>'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.path').': '.t3lib_div::fixed_lgd_pre($this->pageinfo['_thePath'],50);

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);

				// Render upload form:
			$this->moduleContent();
			
				// ShortCut
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id,type',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
			}
		}				
		$this->content.=$this->doc->spacer(10);
	}

	/**
	 * Outputs the accumulated content from $this->content
	 * 
	 * @return	void
	 */
	function printContent()	{
		$this->content.=$this->doc->middle();
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Creates the module content 
	 * 
	 * @return	void
	 */
	function moduleContent()	{
		global $LANG;
	
			// Get "type":
		$type = t3lib_div::GPvar('doctype');

		if (is_array($GLOBALS['HTTP_POST_FILES']['_uploaded_office_file']))	{
			$tmpFileName = PATH_site.'typo3temp/ext_rlmp_officeimport_'.substr(md5(microtime()),0,10);
			if (!@is_file($tmpFileName))	{
				t3lib_div::upload_copy_move($GLOBALS['HTTP_POST_FILES']['_uploaded_office_file']['tmp_name'],$tmpFileName);
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
							} else debug('ERROR: This was not a Word 2003 file!');
						break;
						case 2:	// Excel files:
							$fileContent = t3lib_div::getUrl($tmpFileName);
							$firstFewBytes = substr($fileContent,0,200);
							if (strstr($firstFewBytes,'<?mso-application progid="Excel.Sheet"?>'))	{
								$msOffice2003 = t3lib_div::makeInstance ('tx_rlmpofficeimport_msoffice2003');
								$RTEcontent = $msOffice2003->mainExcel($fileContent, $conf);
							} else debug('ERROR: This was not an Excel 2003 file!');
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
					} else debug('ERROR: No content returned from document.');

					if (t3lib_div::isFirstPartOfStr($tmpFileName,PATH_site.'typo3temp/ext_rlmp_officeimport_'))	{
						unlink($tmpFileName);
					} else debug('ERROR: For some mysterious reason the temporary file variable pointed to a file OUTSIDE the typo3temp/ folder - thus we cannot delete it!');
				} else debug('ERROR: No file copied...');
			} else debug('ERROR: Temporary filename already in use...');
		} else {
			$preText = '';
			
			switch($type)	{
				case 1:
					$preText.='<br /><p><img src="cm_icon1.gif" width="16" height="16" border="0" alt="" align="absmiddle" /> '.$LANG->getLL('doctype1').'<br /><strong>'.$LANG->getLL('lbl_notice').'</strong> '.$LANG->getLL('doctype1_1').'</p><br />';
				break;
				case 2:
					$preText.='<br /><p><img src="cm_icon2.gif" width="16" height="16" border="0" alt="" align="absmiddle" /> '.$LANG->getLL('doctype2').'<br /><strong>'.$LANG->getLL('lbl_notice').'</strong> '.$LANG->getLL('doctype2_1').'</p><br />';
				break;
				case 3:
					$preText.='<br /><p><img src="cm_icon3.gif" width="16" height="16" border="0" alt="" align="absmiddle" /> '.$LANG->getLL('doctype3').'<br /></p><br />';
				break;
/*				case 4:
					$preText.='<br /><p><img src="cm_icon4.gif" width="16" height="16" border="0" alt="" align="absmiddle" /> '.$LANG->getLL('doctype4').'<br /></p><br />';
				break;*/
			}
			$content.=$preText;
			$content.= '<input type="file" name="_uploaded_office_file" style="width:400px;" /><br />';
			$content.= '<input type="submit" name="Upload" value="'.$LANG->getLL('upload').'">
				<input type="hidden" name="doctype" value="'.htmlspecialchars($type).'">
				<input type="hidden" name="id" value="'.htmlspecialchars($this->id).'">';
			
			$this->content.=$this->doc->section("Upload office file to Import:",$content,0,1);
		}
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
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rlmp_officeimport/cm1/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rlmp_officeimport/cm1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_rlmpofficeimport_cm1');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>