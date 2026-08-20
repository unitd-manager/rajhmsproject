<?
class CPL_Admin_Widgets_Hms_AttendanceReport_Model extends CP_Common_Lib_WidgetModelAbstract
{
    
    function getSQL(){
        
        $SQL = "
        SELECT a.*
              ,e.first_name AS employee_name
        FROM `attendance` a
        LEFT JOIN (`employee` e) ON (e.employee_id = a.employee_id)  
        ";

        return $SQL;
    }
    /**
     *
     */
    function setSearchVar() {
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $searchVar = $this->searchVar;
        $searchVar->mainTableAlias = 'a';

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        $month        = date('m');
        $year         = date('Y');
        $employee_id  = $fn->getReqParam('employee_id');
        $current_date = date('Y-m-d');
        $last30days   = date('Y-m-d', strtotime('today - 7 days'));

        if($tv['module'] == 'common_dashboard'){
            $last7days = date('Y-m-d', strtotime('today - 7 days'));
            $start_date = $last7days;
            $end_date   = $current_date;
        }
        
        if ($start_date != '' && $end_date == '') {
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        } else {

            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            
            $searchVar->sqlSearchVar[] = "a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
        }

        if ($monthVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(a.record_date, '%m') = '{$monthVal}'";
        }

        if ($yearVal != '') {
            $searchVar->sqlSearchVar[] = "DATE_FORMAT(a.record_date, '%Y') = '{$yearVal}'";
        }

        if ($employee_id != '') {
            $searchVar->sqlSearchVar[] = "a.employee_id = '{$employee_id}'";
        }


        $searchVar->groupBy   = 'a.attendance_id';
        $searchVar->sortOrder = 'a.attendance_id DESC';

    }

    /**
     *
     * @param <type> $SQL
     * @return <type>
     */
    function getDataArray() {
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $modelHelper = Zend_Registry::get('modelHelper');
        $dataArray = $modelHelper->getWidgetDataArray($this->controller, 'hms_attendanceReport');

        $this->dataArray = $dataArray;
        return $this->dataArray;
    }
    
    /**
     */
    function getExportToExcel(){
        $db = Zend_Registry::get('db');
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $cpUtil = Zend_Registry::get('cpUtil');
        $dateUtil = Zend_Registry::get('dateUtil');
        $fn = Zend_Registry::get('fn');

        set_time_limit(50000);
        ini_set('memory_limit', '512M');

        require_once("PHPExcel.php");
        include 'PHPExcel/IOFactory.php';

        $file_name = "AttendanceReport_" . date("d-m-Y") . ".xls";

        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment;filename={$file_name}");
        header("Content-Transfer-Encoding: binary ");

        $objPHPExcel = new PHPExcel();

        //--------------------------------------------------//
        $rowc = 1;
        $colc = 0;

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');
        $month        = date('m');
        $year         = date('Y');
        $employee_id  = $fn->getReqParam('employee_id');
        $current_date = date('Y-m-d');

        $monthValAppendSql  = '';
        $yearValAppendSql   = '';
        $startDateAppendSql = '';

        $actSheet = &$objPHPExcel->getActiveSheet();

        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Date');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Name');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Leave Taken');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Day Time In');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Day Time Out');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Night Time In');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Night Time Out');
        $actSheet->setCellValueByColumnAndRow($colc++, $rowc, 'Total Hrs Worked');

        /******************** FORMAT HEADER *******************/
        $headStyle = array(
            'font' => array('bold' => true)
        );

        $lastCol    = $actSheet->getHighestColumn();
        $lastColInd = PHPExcel_Cell::columnIndexFromString($lastCol);
        $actSheet->getStyle("A1:{$lastCol}1")->applyFromArray($headStyle);

        for ($i=0; $i < $lastColInd; $i++){
            $colAlphabet = PHPExcel_Cell::stringFromColumnIndex($i);
            $actSheet->getColumnDimension($colAlphabet)->setAutoSize(true);
        }

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "a.record_date >= '{$start_date}' AND a.record_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "a.record_date >= '{$start_date}' AND a.record_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "a.record_date >= '{$start_date}' AND a.record_date <= '{$end_date}'";
        } else {
            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "a.record_date >= '{$start_date}' AND a.record_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(a.record_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(a.record_date, '%Y') = '{$yearVal}'" ;
        }

        if ($employee_id != '') {
            $startDateAppendSql .= "AND a.employee_id = '{$employee_id}'";
        }
  
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND a.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT a.*
              ,e.first_name AS employee_name
        FROM `attendance` a
        LEFT JOIN (`employee` e) ON (e.employee_id = a.employee_id)
        WHERE {$startDateAppendSql}
        {$monthValAppendSql}
        {$yearValAppendSql}
        {$appendSqlSite}
        ORDER BY a.attendance_id DESC
        ";

        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $colc = 0;
            $rowc++;

            $creationDate = $fn->getCPDate($row['record_date'],"d-m-Y");
            $record_sign_in        = $row['time_in'];
            $record_sign_out       = $row['leave_time'];
            $record_created        = $row['record_date'];
            $time1                 = date("H:i", strtotime($record_sign_in) );
            $time2                 = date("H:i", strtotime($record_sign_out) );
            $record_created        = date("l", strtotime($record_created) );
            $day                   = $record_created;
            list($hours, $minutes) = explode(':', $time1);
            $startTimestamp        = mktime($hours, $minutes);
            list($hours, $minutes) = explode(':', $time2);
            $endTimestamp          = mktime($hours, $minutes);
            $seconds               = $endTimestamp - $startTimestamp;
            $minutes               = ($seconds / 60) % 60;
            $hours                 = floor($seconds / (60 * 60));
            
            if($row['leave_time'] != '00:00:00' && $row['leave_time'] != ''){
                $total_time = sprintf("%02d", $hours). ":" .sprintf("%02d", $minutes);
            } else {
                $total_time = '';
            }

            $on_leave = ($row['on_leave'] == 1) ? "Yes" : "No";

            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $creationDate);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['employee_name']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $on_leave);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['time_in']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['leave_time']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['time_in_shift2']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $row['leave_time_shift2']);
            $actSheet->setCellValueByColumnAndRow($colc++, $rowc, $total_time);
        }

        $colc = 0;
        $rowc++;
        $actSheet->getStyle("A{$rowc}:H{$rowc}")->applyFromArray($headStyle);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
    }
}