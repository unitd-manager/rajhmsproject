<?
class CPL_Admin_Widgets_Hms_AttendanceReport_View extends CP_Common_Lib_WidgetViewAbstract
{

    /**
     *
     */
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $current_date  = date('Y-m-d');
        $current_year  = date('Y');
        $current_month = date('m');

        $start_date   = $fn->getReqParam('start_date');
        $end_date     = $fn->getReqParam('end_date');
        $employee_id  = $fn->getReqParam('employee_id');
        $monthVal     = $fn->getReqParam('month');
        $yearVal      = $fn->getReqParam('year');

        if($tv['module'] == 'common_dashboard'){
            $heading = "Attendance Report Last 7 Days";
        }else {
            $heading = "Attendance Report";
        }

        $summaryTable = "";
        if($tv['module'] != 'common_dashboard' && $employee_id != ""){
            if ($start_date != '' && $end_date == '') {
                $appendSql  = "AND a.record_date BETWEEN '{$start_date}' AND '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $appendSql  = "AND a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $appendSql  = "AND a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
            } else {

                if($monthVal != ''){
                    $current_month = $monthVal;
                }

                if($yearVal != ''){
                    $current_year = $yearVal;
                }

                $start_date = $current_year . '-' . $current_month . '-' . '01';
                $end_date   = $current_year . '-' . $current_month . '-' . '31';
                $appendSql  = "AND a.record_date BETWEEN '{$start_date}' AND '{$end_date}'";
            }

            $appendSqlTotalLeave = "AND DATE_FORMAT(a.record_date, '%Y') = '{$current_year}'";
            $appendSqlLeave      = "AND DATE_FORMAT(a.record_date, '%m') = '{$current_month}'";

            $SQL = "
            SELECT DISTINCT a.employee_id
                   ,a.time_in
                   ,a.leave_time
                   ,a.on_leave
                   ,(
                     SELECT count(a.on_leave)
                     FROM attendance a
                       WHERE a.employee_id = {$employee_id}
                       AND a.on_leave = 1
                       {$appendSqlTotalLeave}
                    ) AS total_leave_days
                 ,(
                   SELECT SUBSTRING(SEC_TO_TIME(AVG(TIME_TO_SEC(a.time_in))), 1, 8)
                   FROM attendance a
                   WHERE a.employee_id = {$employee_id}
                     {$appendSql}
                     AND (a.on_leave IS NULL
                     OR a.on_leave = 0)
                 ) AS avg_time_in
                 ,(
                   SELECT SUBSTRING(SEC_TO_TIME(AVG(TIME_TO_SEC(a.leave_time))), 1, 8)
                   FROM attendance a
                   WHERE a.employee_id = {$employee_id}
                     {$appendSql}
                     AND (a.on_leave IS NULL
                     OR a.on_leave = 0)
                 ) AS avg_leave_time
                 ,(
                   SELECT SUBSTRING(SEC_TO_TIME(AVG(TIME_TO_SEC(a.time_in_shift2))), 1, 8)
                   FROM attendance a
                   WHERE a.employee_id = {$employee_id}
                     {$appendSql}
                     AND (a.on_leave IS NULL
                     OR a.on_leave = 0)
                 ) AS avg_time_in_shift2
                 ,(
                   SELECT SUBSTRING(SEC_TO_TIME(AVG(TIME_TO_SEC(a.leave_time_shift2))), 1, 8)
                   FROM attendance a
                   WHERE a.employee_id = {$employee_id}
                     {$appendSql}
                     AND (a.on_leave IS NULL
                     OR a.on_leave = 0)
                 ) AS avg_leave_time_shift2

                 ,(
                   SELECT count(a.on_leave)
                   FROM attendance a
                   WHERE a.employee_id = {$employee_id}
                     {$appendSqlLeave}
                     AND a.on_leave = 1
                 ) AS leave_days
            FROM attendance a
            WHERE a.employee_id = {$employee_id}
            ";
            $result  = $db->sql_query($SQL);
            $row = $db->sql_fetchrow($result);

            $summaryTable = "
            <table class='thinlist summaryTable mb20'>
                <thead>
                    <th colspan='9'>Summary</th>
                </thead>
                <tr>
                    <th><u>Total Leave Days</u> : {$row['total_leave_days']}</th>
                    <th><u>Day Avg TI</u> : {$row['avg_time_in']}</th>
                    <th><u>Day Avg TO</u> : {$row['avg_leave_time']}</th>
                    <th><u>Night Avg TI</u> : {$row['avg_time_in_shift2']}</th>
                    <th><u>Night Avg TO</u> : {$row['avg_leave_time_shift2']}</th>
                    <th><u>Leave Days</u> : {$row['leave_days']}</th>
                </tr>
            </table>
            ";
        }

        $text = "
        <h2>{$heading}</h2>
        <div class = 'tableOuter scroll-pane'>
            {$summaryTable}
            <table class='thinlist'>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Leave Taken</th>
                        <th>Day Time In</th>
                        <th>Day Time Out</th>
                        <th>Night Time In</th>
                        <th>Night Time Out</th>
                        <th>Total Hrs Worked</th>
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
    /**
     *
     */

     function getRowsHTML() {
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        
        $rows = '';

        foreach($this->model->dataArray as $row){
            $record_date = $fn->getCPDate($row['record_date'],"d-m-Y");

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

            $rows .= "
            <tr>
                <td>{$record_date}</td>
                <td>{$row['employee_name']}</td>
                <td>{$on_leave}</td>
                <td>{$row['time_in']}</td>
                <td>{$row['leave_time']}</td>
                <td>{$row['time_in_shift2']}</td>
                <td>{$row['leave_time_shift2']}</td>
                <td>{$total_time}</td>
            </tr>
            ";

        }

        $text = "
        {$rows}
        ";

        return $text;
    }
}