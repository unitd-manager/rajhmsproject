<?php
include_once(CP_LIBRARY_PATH.'lib_php/tcpdf-extra/headfoot.php');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF_Local extends MYPDF{
	//Page header
	public function Header() {
		$cpCfg = Zend_Registry::get('cpCfg');
		$fn    = Zend_Registry::get('fn');
		$db    = Zend_Registry::get('db');

		$site_id = $fn->getSessionParam('cp_site_id');

		$font = $this->addTTFfont("lib/gothic.ttf");
    	$this->SetFont($font,'', 10);

		if (count($this->pages) == 1 ) {

			$header='
			<table border="0" width="100%">
				<tr>
					<td width="30%"><br/>
	                <span style="font-weight: bold;font-size:14px;">Dr. J. Vinaignan</span><span style="font-size:13px;"> MBBS, DLO.,<br/>
	                Consultant ENT Surgeon<br/>
	                Endoscopic Sinus Surgeon</span>
	                </td>
	                <td width="40%"></td>
	                <td width="30%"><br/>
	                <span style="font-weight: bold;font-size:14px;">SOORIYA HOSPITAL</span><br/>
	                <span style="font-size:13px;">1, Arunachalam Road,<br/>
	                Saligramam, Chennai - 93.</span>
	                </td>
	            </tr>
			</table>
			';

			$this->writeHTML($header, true, false, false, false, '');
			$this->SetTopMargin(27);
		} else {
			$this->SetTopMargin(6);
		}
	}

	public function Footer() {
		$this->SetFont('Courier','',9);
		$cpCfg = Zend_Registry::get('cpCfg');

      	// Page number
      	//$this->Cell(0, 10, '(This is computer generated document, and does not require a signature) Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'R');

      	$footer='
      	<table border="0" width="100%">
	        <tr>
	            <td align="center">
	            </td>
	        </tr>
			<tr>
				<td width="78%">(This is computer generated document, and does not require a signature)</td>
				<td width="22%" align="right">Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages().'</td>
			</tr>
		</table>';
		//$this->writeHTML($footer, true, false, false, false, '');
    }
}
?>