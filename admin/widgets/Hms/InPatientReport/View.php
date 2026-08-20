<?
class CPL_Admin_Widgets_Hms_InPatientReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = "
        <h2>In Patient Report</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Date</th>
						<th>Patient Name</th>
                        <th>Date Admitted</th>
                        <th>Time Admitted</th>
                        <th>Date Discharge</th>
                        <th class='txtRight'>Amount</th>
                        <th>Status</th>
                   </tr>
				</thead>
				<tbody>
					{$this->getRowsHTML()}
				</tbody>
			</table>
		</div>
        ";
        return $text;
    }

    function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');

        $datas = '';
      

        foreach($this->model->dataArray as $row){
            $check_up_date  = $fn->getCPDate($row['check_up_date'], 'd-m-Y');
            $date_admitted  = $fn->getCPDate($row['date_admitted'], 'd-m-Y');
            $date_discharge = $fn->getCPDate($row['date_discharge'], 'd-m-Y');
 
		    $datas .= "
			<tr>
				<td>{$check_up_date}</td>
                <td>{$row['name']}</td>
                <td>{$date_admitted}</td>
                <td>{$row['time_admitted']}</td>
                <td>{$date_discharge}</td>
                <td align='right'>{$row['amount']}</td>
                <td>{$row['status']}</td>
             </tr>   
            ";
           
        }
       
        $text = "
        {$datas}
        ";

        return $text;
    }

}