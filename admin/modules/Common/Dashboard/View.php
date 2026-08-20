<?
ini_set('max_execution_time', 300); //300 seconds = 5 minutes
class CPL_Admin_Modules_Common_Dashboard_View extends CP_Admin_Modules_Common_Dashboard_View
{
    function getList($dataArray) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $arr = $cpCfg['cp.dashboardArr'];

        $hook = getCPModuleHook('common_dashboard', 'list', $dataArray, $this);
        if($hook['status']){
            return $hook['html'];
        }

        $rows = '';
        foreach($arr as $widgetArr){
            $widget   = $widgetArr['name'];
            $subClass = $widgetArr['subClass'];
            $cssClass = $widgetArr['cssClass'];

            $clsInst = getCPWidgetObj($widget);

            $rows .= "
            <div class='{$cssClass}'>
                <div class='{$subClass} widget' id='wd_{$widget}'>
                    {$clsInst->getWidget()}
                </div>
            </div>
            ";
        }

        $text = "
        <div id='dashboard' class='subcolumns'>
            <div class='dashboardSummary floatbox'>
                <!--<div class='c33l txtCenter revenueSummary'>
                    <div>TODAY REVENUE</div>
                    {$this->getSummaryDisplay('Today')}
                    <hr>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <div>Current Month</div>
                            {$this->getSummaryDisplay('Yesterday')}
                        </div>
                        <div class='float_right'>
                            <div>Last Month</div>
                            {$this->getSummaryDisplay('Last Week')}
                        </div>
                    </div>
                </div>-->
                <!--<div class='c33l txtCenter patientVisitSummary'>
                    <div>TODAY PATIENT VISIT BY APPOINTMENT</div>
                    {$this->getPatientVisitDisplay('Today', 'By Appointment')}
                    <hr>
                    <div class='floatbox'>
                        <div class='float_left'>
                            <div>Yesterday</div>
                            {$this->getPatientVisitDisplay('Yesterday', 'By Appointment')}
                        </div>
                        <div class='float_right'>
                            <div>This Week</div>
                            {$this->getPatientVisitDisplay('Last Week', 'By Appointment')}
                        </div>
                    </div>
                </div>-->
                <div class='col-md-8 paddingNone'>
                    <div class='col-md-6 colDash'>
                        <div class='txtCenter patientVisitSummaryWalkIn'>
                            <div>
                            <a class='btn btn-default btnRefreshColorPanels'><span class='refreshIcon'></span>Refresh</a>
                                TODAY PATIENT VISIT (NEW)
                            </div>
                            <div id='PatientVisitDisplayDiv1'>{$this->getPatientVisitDisplay('Today', 'Walk In')}</div>
                            <hr>
                            <div class='floatbox'>
                                <div class='float_left'>
                                    <div>Current Month</div>
                                    <div id='PatientVisitDisplayDiv2'>{$this->getPatientVisitDisplay('Yesterday', 'Walk In')}</div>
                                </div>
                                <div class='float_right'>
                                    <div>Last Month</div>
                                    <div id='PatientVisitDisplayDiv3'>{$this->getPatientVisitDisplay('Last Week', 'Walk In')}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='col-md-6 colDash'>
                        <div class='patientVisitSummary'>
                            <div class='txtCenter'>PATIENT VISITS DR WISE</div>
                            <div id='patientVisitSummaryDiv'>{$this->getPatientDoctorWise()}</div>
                        </div>
                    </div>

                    <div class='col-md-6 colDash'>
                        <div class='patientVisitSummarySiteWise'>
                            <div class='txtCenter'>OVERALL PATIENT VISITS</div>
                            <div id='patientVisitSummarySiteWiseDiv'>{$this->getPatientVisitSiteWise()}</div>
                        </div>
                    </div>

                    <div class='col-md-6 colDash'>
                        <div class='labReportSummaryYesterday'>
                            <div class='txtCenter'>Lab Report</div>
                            <hr>
                            <div id='labReportSummaryYesterdayDiv'>{$this->getLabReportSummary()}</div>
                        </div>
                    </div>
                </div>
                <div class='col-md-4 paddingNone'>
                    <div class='col-md-12 colDash'>
                        <div class='attendanceReportSummary'>
                            <div class='txtCenter'>Attendance Report</div>
                            <hr>
                            <div id='attendanceReportSummaryDiv'>{$this->getAttendanceReportSummary()}</div>
                        </div>
                    </div>
                </div>
            </div>
            {$rows}
        </div>
        ";

        return $text;
    }

    function getPatientVisitSiteWise() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');
        $rows = '';
        $appendSql = '';

        $today = date('Y-m-d');
        $yesterday     = date("Y-m-d", strtotime("yesterday"));

        $SQLSite = "
        SELECT title 
               ,site_id
        FROM site
        WHERE published = 1
        ";
        $resultSite = $db->sql_query($SQLSite);
        $sum_amount = 0;
        $case_count = 0;
        $sum_amountYesterDay = 0;
        $case_countYesterDay = 0;
        $overallCaseCountYesterday  = 0;
        $overallCaseAmountYesterday = 0;
        $overallCaseCountToday      = 0;
        $overallCaseAmountToday     = 0;
        $overallNoConsCaseCountYesterday  = 0;
        $overallNoConsCaseAmountYesterday = 0;
        $overallNoConsCaseCountToday      = 0;
        $overallNoConsCaseAmountToday     = 0;
        while($rowSite    = $db->sql_fetchrow($resultSite)){

            $SQLSub = "
            SELECT COUNT(ev.patient_visit_id) AS patient_count
                  ,SUM(ev.consultation_fees) AS fees_count
                  ,IF(e.category = 'Consultant', COUNT(ev.patient_visit_id), 0) AS ConsultantCount
                  ,IF(e.category = 'Consultant', SUM(ev.consultation_fees), 0) AS ConsultantAmount
            FROM employee_visit ev
            LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
            LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
            WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$yesterday}'
              AND e.first_name != ''
              AND pv.status != 'Cancelled'
              AND pv.site_id = {$rowSite['site_id']}
              GROUP BY e.category
            ";
            $resultSub = $db->sql_query($SQLSub);
            $sum_amountYesterDay = 0;
            $case_countYesterDay = 0;
            $consultantAmountYesterDay = 0;
            $consultantCountYesterDay  = 0;
            while ($rowSub = $db->sql_fetchrow($resultSub)) {
                $sum_amountYesterDay += $rowSub['fees_count'];
                $case_countYesterDay += $rowSub['patient_count'];
                $consultantCountYesterDay  += $rowSub['ConsultantCount'];
                $consultantAmountYesterDay += $rowSub['ConsultantAmount'];
            }

            $overallCaseCountYesterday  += $case_countYesterDay;
            $overallCaseAmountYesterday += $sum_amountYesterDay;

            $SQLSub2 = "
            SELECT COUNT(ev.patient_visit_id) AS patient_count
                  ,SUM(ev.consultation_fees) AS fees_count
                  ,IF(e.category = 'Consultant', COUNT(ev.patient_visit_id), 0) AS ConsultantCount
                  ,IF(e.category = 'Consultant', SUM(ev.consultation_fees), 0) AS ConsultantAmount
            FROM employee_visit ev
            LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
            LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
            WHERE DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$today}'
              AND e.first_name != ''
              AND pv.status != 'Cancelled'
              AND pv.site_id = {$rowSite['site_id']}
              GROUP BY e.category
            ";
            $resultSub2 = $db->sql_query($SQLSub2);
            $sum_amount = 0;
            $case_count = 0;
            $consultantAmount = 0;
            $consultantCount  = 0;
            while ($rowSub2 = $db->sql_fetchrow($resultSub2)) {
                $sum_amount += $rowSub2['fees_count'];
                $case_count += $rowSub2['patient_count'];
                $consultantCount  += $rowSub2['ConsultantCount'];
                $consultantAmount += $rowSub2['ConsultantAmount'];
            }

            $overallCaseCountToday  += $case_count;
            $overallCaseAmountToday += $sum_amount;

            $overallNoConsCaseCountYesterday  += $case_countYesterDay - $consultantCountYesterDay;
            $overallNoConsCaseAmountYesterday += $sum_amountYesterDay - $consultantAmountYesterDay;
            $overallNoConsCaseCountToday      += $case_count - $consultantCount;
            $overallNoConsCaseAmountToday     += $sum_amount - $consultantAmount;

            $rows .= '<tr>
                        <td width="40%">'.$rowSite['title'].'</td> 
                        <td width="30%">'.$case_countYesterDay.' / '.$sum_amountYesterDay.'</td>
                        <td width="30%">'.$case_count.' / '.$sum_amount.'</td>
                     </tr>';
        }

        $text = "
        <table cellpadding='5' width='100%'>
            <tr>
                <td style='border-top:1px solid #FFFFFF;'></td>
                <td style='border-top:1px solid #FFFFFF;'><u>Yesterday</u></td>
                <td style='border-top:1px solid #FFFFFF;'><u>Today</u></td>
            </tr>
            {$rows}
            <tr>
                <td colspan='3'><br/></td>
            </tr>
            <tr>
                <td style='line-height:30px;border-top:1px solid #FFFFFF;border-bottom:1px solid #FFFFFF;'>Total</td>
                <td style='line-height:30px;border-top:1px solid #FFFFFF;border-bottom:1px solid #FFFFFF;'>{$overallCaseCountYesterday}CS - {$overallCaseAmountYesterday}RS</td>
                <td style='line-height:30px;border-top:1px solid #FFFFFF;border-bottom:1px solid #FFFFFF;'>{$overallCaseCountToday}CS - {$overallCaseAmountToday}RS</td>
            </tr>
        </table>
        ";

        return $text;
    }

    function getPatientDoctorWise() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = '';
        $currentYear = date('Y');
        $currentMonth = date('m');
        $start_date  = $currentYear . '-' . $currentMonth . '-' . '01';
        $end_date = date("Y-m-d");
        //$date = "DATE_FORMAT(ev.creation_date, '%Y-%m-%d') >= '{$start_date}' AND DATE_FORMAT(ev.creation_date, '%Y-%m-%d') <= '{$end_date}'";
        $check_up_date = date("Y-m-d");
        $yesterday     = date("Y-m-d", strtotime("yesterday"));
        $Yesterdaydate = "DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$yesterday}'";
        $date          = "DATE_FORMAT(pv.check_up_date, '%Y-%m-%d') = '{$check_up_date}'";

        $sum_amount = 0;
        $case_count = 0;

        $sqlCategory = $fn->getValueListSQL('employeeCategory');
        $resultCat   = $db->sql_query($sqlCategory);

        $overallCaseCountYesterday  = 0;
        $overallCaseAmountYesterday = 0;
        $overallCaseCountToday      = 0;
        $overallCaseAmountToday     = 0;
        $overallNoConsCaseCountYesterday  = 0;
        $overallNoConsCaseAmountYesterday = 0;
        $overallNoConsCaseCountToday      = 0;
        $overallNoConsCaseAmountToday     = 0;

        while ($rowCat = $db->sql_fetchrow($resultCat)) {

            if($rowCat['value'] != "Student"){
                $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
                $appendSqlSite = '';
                if ($cpCfg['cp.hasMultiUniqueSites']) {
                    $appendSqlSite = "AND pv.site_id = {$cpSiteIdSession}";
                }

                $SQL = "
                SELECT ev.*
                      ,COUNT(ev.patient_visit_id) AS patient_count
                      ,SUM(ev.consultation_fees) AS fees_count
                FROM employee_visit ev
                LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                WHERE {$Yesterdaydate}
                  AND e.first_name != ''
                  AND pv.status != 'Cancelled'
                  AND e.category = '{$rowCat['value']}'
                  {$appendSqlSite}
                ";
                $result = $db->sql_query($SQL);
                $sum_amountYesterDay = 0;
                $case_countYesterDay = 0;
                while ($row = $db->sql_fetchrow($result)) {
                    $sum_amountYesterDay = $row['fees_count'];
                    $case_countYesterDay = $row['patient_count'];
                }

                if($sum_amountYesterDay == ""){
                    $sum_amountYesterDay = 0;
                }

                if($case_countYesterDay == ""){
                    $case_countYesterDay = 0;
                }

                $overallCaseCountYesterday  += $case_countYesterDay;
                $overallCaseAmountYesterday += $sum_amountYesterDay;

                $SQL2 = "
                SELECT ev.*
                      ,COUNT(ev.patient_visit_id) AS patient_count
                      ,SUM(ev.consultation_fees) AS fees_count
                FROM employee_visit ev
                LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
                LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
                WHERE {$date}
                  AND e.first_name != ''
                  AND pv.status != 'Cancelled'
                  AND e.category = '{$rowCat['value']}'
                  {$appendSqlSite}
                ";
                $result2 = $db->sql_query($SQL2);
                
                $sum_amount = 0;
                $case_count = 0;
                while ($row2 = $db->sql_fetchrow($result2)) {
                    $sum_amount = $row2['fees_count'];
                    $case_count = $row2['patient_count'];
                }

                if($sum_amount == ""){
                    $sum_amount = 0;
                }

                if($case_count == ""){
                    $case_count = 0;
                }

                $overallCaseCountToday  += $case_count;
                $overallCaseAmountToday += $sum_amount;

                if($rowCat['value'] != "Consultant") {
                    $overallNoConsCaseCountYesterday  += $case_countYesterDay;
                    $overallNoConsCaseAmountYesterday += $sum_amountYesterDay;
                    $overallNoConsCaseCountToday      += $case_count;
                    $overallNoConsCaseAmountToday     += $sum_amount;
                }

                $rows .= "
                <tr>
                    <td width='40%'>{$rowCat['value']}</td>
                    <td width='30%'>{$case_countYesterDay} / {$sum_amountYesterDay}</td>
                    <td width='30%'>{$case_count} / {$sum_amount}</td>
                </tr>
                ";
            }
        }

        $text = "
        <table border='0' width='100%'>
            <tr>
                <td style='border-top:1px solid #FFFFFF;'></td>
                <td style='border-top:1px solid #FFFFFF;'><u>Yesterday</u></td>
                <td style='border-top:1px solid #FFFFFF;'><u>Today</u></td>
            </tr>
            {$rows}
            <tr>
                <td style='border-top:1px solid #FFFFFF;'>Total</td>
                <td style='border-top:1px solid #FFFFFF;'>{$overallCaseCountYesterday}CS - {$overallCaseAmountYesterday}RS</td>
                <td style='border-top:1px solid #FFFFFF;'>{$overallCaseCountToday}CS - {$overallCaseAmountToday}RS</td>
            </tr>
            <tr>
                <td  style='border-bottom:1px solid #FFFFFF;'>Total - Consultant</td>
                <td  style='border-bottom:1px solid #FFFFFF;'>{$overallNoConsCaseCountYesterday} - {$overallNoConsCaseAmountYesterday}</td>
                <td  style='border-bottom:1px solid #FFFFFF;'>{$overallNoConsCaseCountToday} - {$overallNoConsCaseAmountToday}</td>
            </tr>
        </table>
        ";

        return $text;
    }

    function getSummaryDisplay($day) {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        $rows = 0;

        if($day == 'Today'){
            $creation_date = date("Y-m-d");
            $date = "AND r.date = '{$creation_date}'";
        }else if($day == 'Yesterday'){
            $currentYear = date('Y');
            $currentMonth = date('m');
            //$creation_date = date('Y-m-d', mktime (0,0,0,date("m"), date("d")-1, date("Y")));
            $start_date  = $currentYear . '-' . $currentMonth . '-' . '01';
            $end_date = date("Y-m-d");
            //$date = "AND r.date = '{$creation_date}'";
            $date = "AND r.date >= '{$start_date}' AND r.date <= '{$end_date}'";
        }else if($day == 'Last Week'){
            /*$currentYear = date('Y');
            $lastMonth = date('m') - 1;
            $start_date = $currentYear . '-' . $lastMonth . '-' . '01';
            $end_date = $currentYear . '-' . $lastMonth . '-' . '31';*/
            $start_date = date('Y-m-d', strtotime('first day of last month'));
            $end_date = date('Y-m-d', strtotime('last day of last month'));
            $date = "AND r.date >= '{$start_date}' AND r.date <= '{$end_date}'";
        }

        $SQL = "
        SELECT r.*
        FROM receipt r
        WHERE r.receipt_status != 'Cancelled'
          {$date}
        ";
        $result = $db->sql_query($SQL);

        while ($row = $db->sql_fetchrow($result)) {
            $rows += "
            {$row['amount']}
            ";
        }
        $rows = number_format($rows, 2);

        return $rows;
    }

    function getPatientVisitDisplay($day = '', $type = '') {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn = Zend_Registry::get('fn');
        $db = Zend_Registry::get('db');

        if($day == ""){
            $day = $fn->getReqparam('day');
        }

        if($type == ""){
            $type = $fn->getReqparam('type');
        }

        $rows = '';

        if($day == 'Today'){
            $creation_date = date("Y-m-d");
            $date = "AND pv.check_up_date = '{$creation_date}' AND pv.record_type='{$type}'";
            $datePatient = "AND DATE_FORMAT(p.creation_date, '%Y-%m-%d') = '{$creation_date}'";
        }else if($day == 'Yesterday'){
            $currentYear = date('Y');
            $currentMonth = date('m');
            $start_date  = $currentYear . '-' . $currentMonth . '-' . '01';
            $end_date = date("Y-m-d");
            $date = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}' AND pv.record_type='{$type}'";
            $datePatient = "AND DATE_FORMAT(p.creation_date, '%Y-%m-%d') >= '{$start_date}' AND DATE_FORMAT(p.creation_date, '%Y-%m-%d') <= '{$end_date}'";
        }else if($day == 'Last Week'){
            $start_date = date('Y-m-d', strtotime('first day of last month'));
            $end_date = date('Y-m-d', strtotime('last day of last month'));
            $date = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}' AND pv.record_type='{$type}'";
            $datePatient = "AND DATE_FORMAT(p.creation_date, '%Y-%m-%d') >= '{$start_date}' AND DATE_FORMAT(p.creation_date, '%Y-%m-%d') <= '{$end_date}'";
        }

        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $SQL = "
        SELECT COUNT(pv.patient_visit_id) AS patient_count
        FROM patient_visit pv
        WHERE pv.status != 'Cancelled'
        {$date}
        {$appendSqlSite}
        ";
        $result = $db->sql_query($SQL);
        $row = $db->sql_fetchrow($result);

        $SQLNewPat = "
        SELECT DISTINCT p.patient_information_id AS patient_count
        FROM patient_information p
        LEFT JOIN patient_visit pv ON (pv.patient_information_id = p.patient_information_id)
        WHERE pv.status != 'Cancelled'
        {$datePatient}
        {$date}
        {$appendSqlSite}
        GROUP BY pv.patient_information_id
        ";
        $resultNewPat    = $db->sql_query($SQLNewPat);
        $newPatientCount = 0;
        while($rowNewPat = $db->sql_fetchrow($resultNewPat)){
            $newPatientCount++;
        }

        $perentCalc = 0;
        if($row['patient_count'] > 0){
            $perentCalc = ($newPatientCount / $row['patient_count']) * 100;
            $perentCalc = number_format($perentCalc, 0, '.', '');
        }

        //while ($row = $db->sql_fetchrow($result)) {
            $rows = "
            {$row['patient_count']} ({$newPatientCount}){$perentCalc}%
            ";
        //}

        return $rows;
    }

    function getLabReportSummary() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');

        $yesterday = date("Y-m-d", strtotime("yesterday"));
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQL = "
        SELECT * FROM (
          SELECT site_id, DATE_FORMAT(mtv.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_visit mtv
          UNION  
          SELECT site_id, DATE_FORMAT(mtl.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_lab mtl
          UNION  
          SELECT site_id, DATE_FORMAT(mtip.creation_date, '%Y-%m-%d') AS creation_date
        FROM medical_test_in_patient mtip
        ) A
        WHERE DATE_FORMAT(creation_date, '%Y-%m-%d') = '{$yesterday}'
        GROUP BY DATE_FORMAT(creation_date, '%Y-%m-%d')
        ";

        $rows = "";
        $totalOverAll           = 0;
        $totalOverAllCount      = 0;
        $totaltestcount         = 0;
        $totalPattestcount      = 0;
        $totalIpTestCount       = 0;
        $totaltestamount        = 0;
        $totalPattestamount     = 0;
        $totalIpTestAmount      = 0;
        $totalPatTestXrayCount  = 0;
        $totalPatTestXrayAmount = 0;
        $totalPatTestEcgCount   = 0;
        $totalPatTestEcgAmount  = 0;
        $totalTestXrayCount     = 0;
        $totalTestXrayAmount    = 0;
        $totalTestEcgCount      = 0;
        $totalTestEcgAmount     = 0;
        $totalIpTestXrayCount   = 0;
        $totalIpTestXrayAmount  = 0;
        $totalIpTestEcgCount    = 0;
        $totalIpTestEcgAmount   = 0;
        $totalAlltestcount      = 0;
        $totalAlltestamount     = 0;
        $totalLabCount          = 0; 
        $totalLabAmount         = 0; 
        $totalEcgCount          = 0; 
        $totalEcgAmount         = 0; 
        $totalXrayCount         = 0; 
        $totalXrayAmount        = 0; 
        $result = $db->sql_query($SQL);
        while($row    = $db->sql_fetchrow($result)){
            $day  = $fn->getCPDate($row['creation_date'], 'D');
            $date = $fn->getCPDate($row['creation_date'], 'd-m-Y');
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSql = "AND m.site_id = {$cpSiteIdSession}";
            }

            $SQLPatLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,mt.category
                   ,SUM(m.fees) AS fees
            FROM medical_test_visit m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id = m.patient_visit_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND pv.status != 'Cancelled'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultPatLabTest = $db->sql_query($SQLPatLabTest);
            $patlabtesttotal        = "";
            $totalPattestcount      = 0;
            $totalPattestamount     = 0;
            $totalPatTestXrayCount  = 0;
            $totalPatTestXrayAmount = 0;
            $totalPatTestEcgCount   = 0;
            $totalPatTestEcgAmount  = 0;
            while ($rowPatLabTest = $db->sql_fetchrow($resultPatLabTest)) {
                if($rowPatLabTest['fees'] == ""){
                    $rowPatLabTest['fees'] = 0;
                }

                $patlabtesttotal .= $rowPatLabTest['title'].'('.$rowPatLabTest['count'].' - '.$rowPatLabTest['fees'].'), ';

                if($rowPatLabTest['category'] != "radiology" && $rowPatLabTest['category'] != "ECG"){
                    $totalPattestcount  += $rowPatLabTest['count'];
                    $totalPattestamount += $rowPatLabTest['fees'];
                }

                if($rowPatLabTest['category'] == "radiology"){
                    $totalPatTestXrayCount  += $rowPatLabTest['count'];
                    $totalPatTestXrayAmount += $rowPatLabTest['fees'];
                }

                if($rowPatLabTest['category'] == "ECG"){
                    $totalPatTestEcgCount  += $rowPatLabTest['count'];
                    $totalPatTestEcgAmount += $rowPatLabTest['fees'];
                }

            }
            $patlabtesttotal = rtrim($patlabtesttotal, ", ");

            $SQLLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,mt.category
                   ,SUM(m.fees) AS fees
            FROM medical_test_lab m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (lab_test lt) ON (lt.lab_test_id = m.lab_test_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND lt.status != 'Cancelled'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultLabTest = $db->sql_query($SQLLabTest);
            $labtesttotal = "";
            $totaltestcount      = 0;
            $totaltestamount     = 0;
            $totalTestXrayCount  = 0;
            $totalTestXrayAmount = 0;
            $totalTestEcgCount   = 0;
            $totalTestEcgAmount  = 0;
            while ($rowLabTest   = $db->sql_fetchrow($resultLabTest)) {
                if($rowLabTest['fees'] == ""){
                    $rowLabTest['fees'] = 0;
                }

                $labtesttotal .= $rowLabTest['title'].'('.$rowLabTest['count'].' - '.$rowLabTest['fees'].'), ';

                if($rowLabTest['category'] != "radiology" && $rowLabTest['category'] != "ECG"){
                    $totaltestcount  += $rowLabTest['count'];
                    $totaltestamount += $rowLabTest['fees'];
                }

                if($rowLabTest['category'] == "radiology"){
                    $totalTestXrayCount  += $rowLabTest['count'];
                    $totalTestXrayAmount += $rowLabTest['fees'];
                }

                if($rowLabTest['category'] == "ECG"){
                    $totalTestEcgCount  += $rowLabTest['count'];
                    $totalTestEcgAmount += $rowLabTest['fees'];
                }

            }
            $labtesttotal = rtrim($labtesttotal, ", ");

            $SQLInPatLabTest = "
            SELECT COUNT(m.medical_test_id) AS count
                   ,m.title
                   ,mt.category
                   ,SUM(m.fees) AS fees
            FROM medical_test_in_patient m
            LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
            LEFT JOIN (in_patient ip) ON (ip.in_patient_id = m.in_patient_id)
            WHERE DATE_FORMAT(m.creation_date, '%Y-%m-%d') = '{$row['creation_date']}'
              AND ip.status != 'Cancelled'
            {$appendSql}
            GROUP BY m.title
            ";
            $resultInPatLabTest = $db->sql_query($SQLInPatLabTest);
            $InPatLabTestTotal = "";
            $totalIpTestCount      = 0;
            $totalIpTestAmount     = 0;
            $totalIpTestXrayCount  = 0;
            $totalIpTestXrayAmount = 0;
            $totalIpTestEcgCount   = 0;
            $totalIpTestEcgAmount  = 0;
            while ($rowInPatLabTest    = $db->sql_fetchrow($resultInPatLabTest)) {
                if($rowInPatLabTest['fees'] == ""){
                    $rowInPatLabTest['fees'] = 0;
                }

                $InPatLabTestTotal .= $rowInPatLabTest['title'].'('.$rowInPatLabTest['count'].' - '.$rowInPatLabTest['fees'].'), ';

                if($rowInPatLabTest['category'] != "radiology" && $rowInPatLabTest['category'] != "ECG"){
                    $totalIpTestCount  += $rowInPatLabTest['count'];
                    $totalIpTestAmount += $rowInPatLabTest['fees'];
                }

                if($rowInPatLabTest['category'] == "radiology"){
                    $totalIpTestXrayCount  += $rowInPatLabTest['count'];
                    $totalIpTestXrayAmount += $rowInPatLabTest['fees'];
                }

                if($rowInPatLabTest['category'] == "ECG"){
                    $totalIpTestEcgCount  += $rowInPatLabTest['count'];
                    $totalIpTestEcgAmount += $rowInPatLabTest['fees'];
                }

            }

            $InPatLabTestTotal = rtrim($InPatLabTestTotal, ", ");

            $totalAlltestcount  = $totaltestcount + $totalPattestcount + $totalIpTestCount + $totalPatTestXrayCount + $totalPatTestEcgCount + $totalTestXrayCount + $totalTestEcgCount + $totalIpTestXrayCount + $totalIpTestEcgCount;
            $totalAlltestamount = $totaltestamount + $totalPattestamount + $totalIpTestAmount + $totalPatTestXrayAmount + $totalPatTestEcgAmount + $totalTestXrayAmount + $totalTestEcgAmount + $totalIpTestXrayAmount + $totalIpTestEcgAmount;
            
            $totalLabCount   = $totaltestcount + $totalPattestcount + $totalIpTestCount;
            $totalLabAmount  = $totaltestamount + $totalPattestamount + $totalIpTestAmount;
            $totalEcgCount   = $totalPatTestEcgCount + $totalTestEcgCount + $totalIpTestEcgCount;
            $totalEcgAmount  = $totalPatTestEcgAmount + $totalTestEcgAmount + $totalIpTestEcgAmount;
            $totalXrayCount  = $totalPatTestXrayCount + $totalTestXrayCount + $totalIpTestXrayCount;
            $totalXrayAmount = $totalPatTestXrayAmount + $totalTestXrayAmount + $totalIpTestXrayAmount;
        }

        $text = "
        <table width='100%' border='0'>
            <tr>
                <td width='25%'></td>
                <td width='25%'><u>LAB TEST</u></td>
                <td width='25%'><u>X-RAY</u></td>
                <td width='25%'><u>ECG</u></td>
            </tr>
            <tr>
                <td width='22%'>Pat Visit</td>
                <td width='26%'>{$totalPattestcount} / {$totalPattestamount}</td>
                <td width='26%'>{$totalPatTestXrayCount} / {$totalPatTestXrayAmount}</td>
                <td width='26%'>{$totalPatTestEcgCount} / {$totalPatTestEcgAmount}</td>
            </tr>
            <tr>
                <td width='22%'>Lab(Self)</td>
                <td width='26%'>{$totaltestcount} / {$totaltestamount}</td>
                <td width='26%'>{$totalTestXrayCount} / {$totalTestXrayAmount}</td>
                <td width='26%'>{$totalTestEcgCount} / {$totalTestEcgAmount}</td>
            </tr>
            <tr>
                <td width='22%'>Lab(IP)</td>
                <td width='26%'>{$totalIpTestCount} / {$totalIpTestAmount}</td>
                <td width='26%'>{$totalIpTestXrayCount} / {$totalIpTestXrayAmount}</td>
                <td width='26%'>{$totalIpTestEcgCount} / {$totalIpTestEcgAmount}</td>
            </tr>
            <tr>
                <td width='22%' style='border-top:1px solid #FFFFFF;line-height:25px;'>Total</td>
                <td width='26%' style='border-top:1px solid #FFFFFF;line-height:25px;'>{$totalLabCount} / {$totalLabAmount}RS</td>
                <td width='26%' style='border-top:1px solid #FFFFFF;line-height:25px;'>{$totalXrayCount} / {$totalXrayAmount}RS</td>
                <td width='26%' style='border-top:1px solid #FFFFFF;line-height:25px;'>{$totalEcgCount} / {$totalEcgAmount}RS</td>
            </tr>
            <tr>
                <td width='30%' style='border-bottom:1px solid #FFFFFF;line-height:25px;'>Grand Total : </td>
                <td width='70%' style='border-bottom:1px solid #FFFFFF;line-height:25px;' colspan='3'>{$totalAlltestamount}RS</td>
            </tr>
        </table>
        ";

        return $text;
    }

    function getAttendanceReportSummaryOld() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');

        $today    = date("Y-m-d");
        $firstDay = date("Y-m-01");
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        $SQL = "
        SELECT e.employee_id
              ,e.first_name AS employee_name
              ,MAX(com.attendance_id) AS attendance_id
        FROM employee e
        LEFT JOIN
        (SELECT attendance_id
                ,employee_id
        FROM attendance
        WHERE record_date = '{$today}'
        ORDER BY attendance_id DESC
        ) AS com ON (com.employee_id = e.employee_id)
        WHERE e.site_id = {$cpSiteIdSession}
        AND (e.position = 'Nurse' OR e.position = 'LAB TECHNICIAN')
        AND e.status = 'Active'
        GROUP BY e.employee_id
        ORDER BY attendance_id DESC
        ";
        $rows = "";
        $totalOverAll = 0;
        $totalOverAllCount = 0;
        $result = $db->sql_query($SQL);
        while($row    = $db->sql_fetchrow($result)){

            $SQLPresent = "
            SELECT  TIME_FORMAT(a.time_in, '%H:%i') AS time_in
                   ,TIME_FORMAT(a.leave_time, '%H:%i') AS leave_time
                   ,TIME_FORMAT(a.time_in_shift2, '%H:%i') AS time_in_shift2
                   ,TIME_FORMAT(a.leave_time_shift2, '%H:%i') AS leave_time_shift2
            FROM `attendance` a
            WHERE a.employee_id = {$row['employee_id']}
            AND a.record_date = '{$today}'
            AND a.site_id = {$cpSiteIdSession}
            ORDER BY a.attendance_id DESC
            ";
            $resultPresent  = $db->sql_query($SQLPresent);
            $numRowsPresent = $db->sql_numrows($resultPresent);
            $rowPresent     = $db->sql_fetchrow($resultPresent);

            $SQLAbsent = "
            SELECT employee_id 
            FROM attendance
            WHERE employee_id = {$row['employee_id']}
            AND site_id = {$cpSiteIdSession}
            AND record_date BETWEEN '{$firstDay}' AND '{$today}'
            AND on_leave = 1
            ";
            $resultAbsent  = $db->sql_query($SQLAbsent);
            $numRowsAbsent = $db->sql_numrows($resultAbsent);

            $DayShiftTimes = "";
            if($rowPresent['time_in'] != "" & $rowPresent['leave_time'] != ""){
                $DayShiftTimes = "[{$rowPresent['time_in']} / {$rowPresent['leave_time']}]";
            }

            if($rowPresent['time_in'] != "" & $rowPresent['leave_time'] == ""){
                $DayShiftTimes = "[{$rowPresent['time_in']}]";
            }

            $NightShiftTimes = "";
            if($rowPresent['time_in_shift2'] != "" & $rowPresent['leave_time_shift2'] != ""){
                $NightShiftTimes = "[{$rowPresent['time_in_shift2']} / {$rowPresent['leave_time_shift2']}]";
            }

            if($rowPresent['time_in_shift2'] != "" & $rowPresent['leave_time_shift2'] == ""){
                $NightShiftTimes = "[{$rowPresent['time_in_shift2']}]";
            }

            $rows .= "
            <tr>
                <td width='34%'>{$row['employee_name']}({$numRowsAbsent})</td>
                <td width='33%'>{$DayShiftTimes}</td>
                <td width='33%'>{$NightShiftTimes}</td>
            </tr>
            ";
        }

        $text = "
        <div class='attendanceWidgetDashboardDiv'>
            <table width='100%' border='0'>
                <tr>
                    <td><u>STAFF</u></td>
                    <td><u>DAY SHIFT</u></td>
                    <td><u>NIGHT SHIFT</u></td>
                </tr>
                {$rows}
            </table>
        </div>
        ";

        return $text;
    }

    function getAttendanceReportSummary() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $fn    = Zend_Registry::get('fn');
        $db    = Zend_Registry::get('db');

        $today    = date("Y-m-d");
        //$today    = date("2018-03-29");
        $firstDay = date("Y-m-01");
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

        /*$SQL = "
        SELECT e.employee_id
              ,e.first_name AS employee_name
              ,MAX(com.attendance_id) AS attendance_id
        FROM employee e
        LEFT JOIN
        (SELECT attendance_id
                ,employee_id
        FROM attendance
        WHERE record_date = '{$today}'
        AND time_in != ''
        ORDER BY attendance_id DESC
        ) AS com ON (com.employee_id = e.employee_id)
        WHERE e.site_id = {$cpSiteIdSession}
        AND e.position = 'Nurse'
        AND e.status = 'Active'
        GROUP BY e.employee_id
        ORDER BY attendance_id DESC
        ";*/
        $SQL = "
        SELECT a.attendance_id
              ,a.employee_id
              ,e.first_name AS employee_name
        FROM attendance a
        LEFT JOIN (employee e) ON ( a.employee_id = e.employee_id )
        WHERE a.record_date = '{$today}'
        AND a.site_id = {$cpSiteIdSession}
        AND a.time_in != ''
        AND e.position IN ('Nurse', 'LAB TECHNICIAN')
        AND e.status = 'Active'
        ORDER BY e.first_name
        ";
        $rows = "";
        $totalOverAll = 0;
        $totalOverAllCount = 0;
        $result = $db->sql_query($SQL);
        while($row    = $db->sql_fetchrow($result)){

            $SQLPresent = "
            SELECT  TIME_FORMAT(a.time_in, '%H:%i') AS time_in
                   ,TIME_FORMAT(a.leave_time, '%H:%i') AS leave_time
                   ,TIME_FORMAT(a.time_in_shift2, '%H:%i') AS time_in_shift2
                   ,TIME_FORMAT(a.leave_time_shift2, '%H:%i') AS leave_time_shift2
                   ,a.record_date
            FROM `attendance` a
            WHERE a.employee_id = {$row['employee_id']}
            AND a.record_date = '{$today}'
            AND a.site_id = {$cpSiteIdSession}
            ORDER BY a.attendance_id DESC
            ";
            $resultPresent  = $db->sql_query($SQLPresent);
            $numRowsPresent = $db->sql_numrows($resultPresent);
            $rowPresent     = $db->sql_fetchrow($resultPresent);

            $SQLAbsent = "
            SELECT employee_id 
            FROM attendance
            WHERE employee_id = {$row['employee_id']}
            AND site_id = {$cpSiteIdSession}
            AND record_date BETWEEN '{$firstDay}' AND '{$today}'
            AND on_leave = 1
            ";
            $resultAbsent  = $db->sql_query($SQLAbsent);
            $numRowsAbsent = $db->sql_numrows($resultAbsent);

            $DayShiftTimes = "";
            if($rowPresent['time_in'] != "" & $rowPresent['leave_time'] != ""){
                $DayShiftTimes = "{$rowPresent['time_in']} / {$rowPresent['leave_time']}";
            }

            if($rowPresent['time_in'] != "" & $rowPresent['leave_time'] == ""){
                $DayShiftTimes = "{$rowPresent['time_in']}";
            }

            $record_sign_in        = $rowPresent['time_in'];
            $record_sign_out       = $rowPresent['leave_time'];
            $record_created        = $rowPresent['record_date'];
            $time1                 = date("H:i", strtotime($record_sign_in));
            $time2                 = date("H:i", strtotime($record_sign_out));
            $record_created        = date("l", strtotime($record_created));
            $day                   = $record_created;
            list($hours, $minutes) = explode(':', $time1);
            $startTimestamp        = mktime($hours, $minutes);
            list($hours, $minutes) = explode(':', $time2);
            $endTimestamp          = mktime($hours, $minutes);
            $seconds               = $endTimestamp - $startTimestamp;
            $minutes               = ($seconds / 60) % 60;
            $hours                 = floor($seconds / (60 * 60));
            
            if($rowPresent['leave_time'] != '00:00:00' && $rowPresent['leave_time'] != ''){
                $total_time = sprintf("%02d", $hours). ":" .sprintf("%02d", $minutes);
            } else {
                $total_time = '';
            }

            $rows .= "
            <tr>
                <td width='34%'>{$row['employee_name']}</td>
                <td width='30%'>{$DayShiftTimes}</td>
                <td width='16%'>{$total_time}</td>
                <td class='txtCenter' width='20%'>{$numRowsAbsent}</td>
            </tr>
            ";
        }

        $rowsNight = "";

        /*$SQLNight = "
        SELECT e.employee_id
              ,e.first_name AS employee_name
              ,MAX(com.attendance_id) AS attendance_id
        FROM employee e
        LEFT JOIN
        (SELECT attendance_id
                ,employee_id
        FROM attendance
        WHERE record_date = '{$today}'
        AND time_in_shift2 != ''
        ORDER BY attendance_id DESC
        ) AS com ON (com.employee_id = e.employee_id)
        WHERE e.site_id = {$cpSiteIdSession}
        AND e.position = 'Nurse'
        AND e.status = 'Active'
        AND e.time_in_night > 0
        AND e.time_out_night > 0
        GROUP BY e.employee_id
        ORDER BY attendance_id DESC
        "; */
        $SQLNight = "
        SELECT a.attendance_id
              ,a.employee_id
              ,e.first_name AS employee_name
        FROM attendance a
        LEFT JOIN (employee e) ON ( a.employee_id = e.employee_id )
        WHERE a.record_date = '{$today}'
        AND a.time_in_shift2 != ''
        AND a.site_id = {$cpSiteIdSession}
        AND e.position IN ('Nurse', 'LAB TECHNICIAN')
        AND e.status = 'Active'
        ORDER BY e.first_name
        ";
        $resultNight = $db->sql_query($SQLNight);
        while($rowNight    = $db->sql_fetchrow($resultNight)){

            $SQLPresent = "
            SELECT  TIME_FORMAT(a.time_in_shift2, '%H:%i') AS time_in_shift2
                   ,TIME_FORMAT(a.leave_time_shift2, '%H:%i') AS leave_time_shift2
                   ,a.record_date
            FROM `attendance` a
            WHERE a.employee_id = {$rowNight['employee_id']}
            AND a.record_date = '{$today}'
            AND a.site_id = {$cpSiteIdSession}
            ORDER BY a.attendance_id DESC
            ";
            $resultPresent  = $db->sql_query($SQLPresent);
            $numRowsPresent = $db->sql_numrows($resultPresent);
            $rowPresent     = $db->sql_fetchrow($resultPresent);

            $SQLAbsent = "
            SELECT employee_id 
            FROM attendance
            WHERE employee_id = {$rowNight['employee_id']}
            AND site_id = {$cpSiteIdSession}
            AND record_date BETWEEN '{$firstDay}' AND '{$today}'
            AND on_leave = 1
            ";
            $resultAbsent  = $db->sql_query($SQLAbsent);
            $numRowsAbsent = $db->sql_numrows($resultAbsent);

            $NightShiftTimes = "";
            if($rowPresent['time_in_shift2'] != "" & $rowPresent['leave_time_shift2'] != ""){
                $NightShiftTimes = "{$rowPresent['time_in_shift2']} / {$rowPresent['leave_time_shift2']}";
            }

            if($rowPresent['time_in_shift2'] != "" & $rowPresent['leave_time_shift2'] == ""){
                $NightShiftTimes = "{$rowPresent['time_in_shift2']}";
            }

            $record_sign_in        = $rowPresent['time_in_shift2'];
            $record_sign_out       = $rowPresent['leave_time_shift2'];
            $record_created        = $rowPresent['record_date'];
            $time1                 = date("H:i", strtotime($record_sign_in));
            $time2                 = date("H:i", strtotime($record_sign_out));
            $record_created        = date("l", strtotime($record_created));
            $day                   = $record_created;
            list($hours, $minutes) = explode(':', $time1);
            $startTimestamp        = mktime($hours, $minutes);
            list($hours, $minutes) = explode(':', $time2);
            $endTimestamp          = mktime($hours, $minutes);
            $seconds               = $endTimestamp - $startTimestamp;
            $minutes               = ($seconds / 60) % 60;
            $hours                 = floor($seconds / (60 * 60));
            
            if($rowPresent['leave_time_shift2'] != '00:00:00' && $rowPresent['leave_time_shift2'] != ''){
                $total_time = sprintf("%02d", $hours). ":" .sprintf("%02d", $minutes);
            } else {
                $total_time = '';
            }

            $rowsNight .= "
            <tr>
                <td width='34%'>{$rowNight['employee_name']}</td>
                <td width='30%'>{$NightShiftTimes}</td>
                <td width='16%'>{$total_time}</td>
                <td class='txtCenter' width='20%'>{$numRowsAbsent}</td>
            </tr>
            ";
        }

        $text = "
        <div class='attendanceWidgetDashboardDiv'>
            <table width='100%' border='0'>
                <tr>
                    <td><u>NAME</u></td>
                    <td><u>DAY SHIFT</u></td>
                    <td><u>HRS</u></td>
                    <td><u>LEAVE DAYS</u></td>
                </tr>
                {$rows}
                <tr>
                    <td style='border-top:1px solid #FFFFFF;'><u>NAME</u></td>
                    <td style='border-top:1px solid #FFFFFF;'><u>NIGHT SHIFT</u></td>
                    <td style='border-top:1px solid #FFFFFF;'><u>HRS</u></td>
                    <td style='border-top:1px solid #FFFFFF;'><u>LEAVE DAYS</u></td>
                </tr>
                {$rowsNight}
            </table>
        </div>
        ";

        return $text;
    }
}
