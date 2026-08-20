<?php 
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$fn = Zend_Registry::get('fn');
		$tv = Zend_Registry::get('tv');
		$cpCfg = Zend_Registry::get('cpCfg');

		$images = '<img src="images/pdfheader.png" />';

		$font = $this->addTTFfont("lib/gothic.ttf");
        $this->SetFont($font,'B',10);

        if($tv['spAction'] == "printLetterPadDraft") {
        	$draft_date = $fn->getSettingsValueByKey("cp.letterPadDraftDate");

        	$header='
			<table border="0" width="100%">
	            <tr>
	                <td width="100%" align="center">'.$images.'</td>
	            </tr>
	        </table>
			';
        } else {
        	$header='
			<table border="0" width="100%">
	            <tr>
	                <td width="100%" align="center">'.$images.'</td>
	            </tr>
	        </table>
			';
        }
		
		$this->writeHTML($header, true, false, false, false, '');
		$this->SetTopMargin(60);

	}

	public function Footer() {
		$fn = Zend_Registry::get('fn');
		$cpCfg = Zend_Registry::get('cpCfg');

      	// Page number
      	//$this->Cell(0, 10, '(This is computer generated document, and does not require a signature) Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'R');
		$font = $this->addTTFfont("lib/calibri.ttf");
        $this->SetFont($font,'',10);
		//$images = '<img src="images/pdffooter.png" width="800px" />';
        //$this->Image($images, 0, 100, 100, '', 'PNG', '', 'C', false, 70, '', false, false, 0, false, false, false);
        $image_file = $cpCfg['cp.localPath']."images/pdffooter.png";
        $this->Image($image_file, 0, 268.5, 209.9, '', 'PNG', '', 'L', false, 70, '', false, false, 0, false, false, false);
      	/*$footer ='
			<table border="0" width="100%">
	            <tr>
	                <td width="100%" align="left">'.$images.'</td>
	            </tr>
	        </table>
			';*/

		//$this->ln(-35);
		//$this->writeHTML($footer, true, false, false, false, '');
		//$this->SetFooterMargin(80);
    }
}
?>