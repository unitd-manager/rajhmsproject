<?
class CPL_Admin_Widgets_Hms_BalanceSheetReport_View extends CP_Common_Lib_WidgetViewAbstract
{
    //==================================================================//
    function getWidget() {
        $db = Zend_Registry::get('db');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        
        $text = "
        <h2>Balance Sheet Report</h2>
		<div class = 'tableOuter scroll-pane'>
			<table class='thinlist'>
				<thead>
					<tr>
						<th>Income</th>
						<th>Expense</th>
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
        $cpSiteIdSession = $fn->getSessionParam('cp_site_id');
        $rows = '';
        $total_amount_visit = 0;
        $totaltestamount = 0;
        $totalOverAllLabtest = 0;
        $totalOverAllinPatient = 0;
        $monthValAppendSql = '';
        $yearValAppendSql = '';
        $startDateAppendSql = '';

        $start_date     = $fn->getReqParam('start_date');
        $end_date       = $fn->getReqParam('end_date');
        $monthVal       = $fn->getReqParam('month');
        $yearVal        = $fn->getReqParam('year');
        $month          = date('m');
        $year           = date('Y');
        $current_date   = date('Y-m-d');

        if ($monthVal != '') {
            $monthValAppendSql = "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }
        if ($yearVal != '') {
            $yearValAppendSql = "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }
        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        }
        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND pv.site_id = {$cpSiteIdSession}";
        }

        $SQLSub = "
        SELECT COUNT(ev.patient_visit_id) AS patient_count
              ,SUM(ev.consultation_fees) AS fees_count
        FROM employee_visit ev
        LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
        LEFT JOIN patient_visit pv ON (pv.patient_visit_id = ev.patient_visit_id)
        WHERE e.first_name != ''
          AND pv.status != 'Cancelled'
          {$startDateAppendSql}
          {$monthValAppendSql}
          {$yearValAppendSql}
          {$appendSql}
        ";
        $resultSub = $db->sql_query($SQLSub);
        while ($rowSub = $db->sql_fetchrow($resultSub)) {
            $sum_amount = $rowSub['fees_count'];
        }
        $total_amount_visit += $sum_amount;

        $appendSql = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSql = "AND m.site_id = {$cpSiteIdSession}";
        }

        $monthValAppendSql = '';
        $yearValAppendSql = '';
        $startDateAppendSql = '';

        if ($start_date == '') {
            if ($monthVal != '') {
                $monthValAppendSql = "AND DATE_FORMAT(m.creation_date, '%m') = '{$monthVal}'" ;
            }
        }
        
        if ($yearVal != '') {
            $yearValAppendSql = "AND DATE_FORMAT(m.creation_date, '%Y') = '{$yearVal}'" ;
        }

        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND m.creation_date >= '{$start_date}' AND m.creation_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND m.creation_date >= '{$start_date}' AND m.creation_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND m.creation_date >= '{$start_date}' AND m.creation_date <= '{$end_date}'";
        } else if ($monthVal == '' && $yearVal == ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND m.creation_date >= '{$start_date}' AND m.creation_date <= '{$end_date}'";
        }

        $SQLLabTest = "
        SELECT COUNT(m.medical_test_id) AS count
               ,m.title
               ,SUM(m.fees) AS fees
        FROM medical_test_visit m
        LEFT JOIN (medical_test mt) ON (mt.medical_test_id = m.medical_test_id)
        WHERE m.title != ''
        {$appendSql}
          {$startDateAppendSql}
          {$monthValAppendSql}
          {$yearValAppendSql}
        GROUP BY m.title
        ";
        $resultLabTest = $db->sql_query($SQLLabTest);
        while ($rowLabTest    = $db->sql_fetchrow($resultLabTest)) {
            if($rowLabTest['fees'] == ""){
                $rowLabTest['fees'] = 0;
            }

            $totaltestamount += $rowLabTest['fees'];
        }
        $totalOverAllLabtest      += $totaltestamount; 

        $startDateAppendSql = '';
        if ($start_date != '' && $end_date == '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$current_date}'";
        } else if ($start_date == '' && $end_date != ''){
            $start_date = $year . '-' . $month . '-' . '01';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else if ($start_date != '' && $end_date != '') {
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        } else {
            if($monthVal != ''){
                $month = $monthVal;
            }

            if($yearVal != ''){
                $year = $yearVal;
            }

            $start_date = $year . '-' . $month . '-' . '01';
            $end_date = $year . '-' . $month . '-' . '31';
            $startDateAppendSql = "AND pv.check_up_date >= '{$start_date}' AND pv.check_up_date <= '{$end_date}'";
        }

        if ($monthVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(pv.check_up_date, '%m') = '{$monthVal}'" ;
        }

        if ($yearVal != '') {
            $startDateAppendSql .= "AND DATE_FORMAT(pv.check_up_date, '%Y') = '{$yearVal}'" ;
        }

        $appendSqlSite = '';
        if ($cpCfg['cp.hasMultiUniqueSites']) {
            $appendSqlSite = "AND inp.site_id = {$cpSiteIdSession}";
        }

        $SQLIP = "
        SELECT pv.check_up_date 
              ,inp.amount 
        FROM in_patient inp
        LEFT JOIN (patient_visit pv) ON (pv.patient_visit_id= inp.patient_visit_id)
        WHERE inp.in_patient_id > 0
        {$appendSqlSite}
          {$startDateAppendSql}
         ";
        $resultIP = $db->sql_query($SQLIP);
        while ($rowIP    = $db->sql_fetchrow($resultIP)) {
            $totalOverAllinPatient += $rowIP['amount']; 
        }

        $sqlgroup = "
        SELECT expense_group_id 
              ,title
        FROM expense_group
        ";
        $resultgroup = $db->sql_query($sqlgroup);
        $expense_group = '';
        $amount = 0;
        $expense_amount ='';
        $overAllExpense = 0;
        while ($rowgroup    = $db->sql_fetchrow($resultgroup)) {
            $appendSqlSite = '';
            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $appendSqlSite = "AND e.site_id = {$cpSiteIdSession}";
            }

            $startDateAppendSql = '';
            if ($start_date != '' && $end_date == '') {
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$current_date}'";
            } else if ($start_date == '' && $end_date != ''){
                $start_date = $year . '-' . $month . '-' . '01';
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else if ($start_date != '' && $end_date != '') {
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            } else {
                if($monthVal != ''){
                    $month = $monthVal;
                }

                if($yearVal != ''){
                    $year = $yearVal;
                }

                $start_date = $year . '-' . $month . '-' . '01';
                $end_date = $year . '-' . $month . '-' . '31';
                $startDateAppendSql = "AND e.date >= '{$start_date}' AND e.date <= '{$end_date}'";
            }

            if ($monthVal != '') {
                $startDateAppendSql .= "AND DATE_FORMAT(e.date, '%m') = '{$monthVal}'" ;
            }

            if ($yearVal != '') {
                $startDateAppendSql .= "AND DATE_FORMAT(e.date, '%Y') = '{$yearVal}'" ;
            }

            $sqlexp = "
            SELECT SUM(e.amount) AS amount
                  ,e.group
                  ,es.title AS sub_title
            FROM expense e
            LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
            WHERE e.group = {$rowgroup['expense_group_id']}
            {$appendSqlSite}
            {$startDateAppendSql}
            GROUP BY e.group
            ";
            $resultexp = $db->sql_query($sqlexp);
            $amount = 0;
            while ($rowexp = $db->sql_fetchrow($resultexp)) {
                $amount += $rowexp['amount'];
            }
            $amountFormat = number_format($amount, 2);

            $sqlexp1 = "
            SELECT e.amount
                  ,e.group
                  ,es.title AS sub_title
            FROM expense e
            LEFT JOIN expense_sub_group es ON (es.expense_sub_group_id = e.sub_group)
            WHERE e.group = {$rowgroup['expense_group_id']}
            {$appendSqlSite}
            {$startDateAppendSql}
            ";
            $resultexp1 = $db->sql_query($sqlexp1);
            $subtitle = '';
            while ($rowexp1 = $db->sql_fetchrow($resultexp1)) {
                $subtitle .= "
                <tr>
                <td>{$rowexp1['sub_title']}</td>
                <td align='right'>{$rowexp1['amount']}</td>
                </tr>";
            }
            $expense_group .= "
            <table width=100%>
                <tr>
                    <td width = 70% class='expenseDetailsHead'>
                    <div class='expenseDetails'>+ {$rowgroup['title']}</div>
                    <div class='subTitles'><table>{$subtitle}</table></div>
                    </td>
                    <td width = 30% align='right'>
                    {$amountFormat}
                    </td>
                </tr>
            </table>
            "; 

            $overAllExpense += $amount;
        }
        $overAllIncome = $total_amount_visit + $totalOverAllinPatient;
        $overAllProfit = $overAllIncome - $overAllExpense;
        $overAllIncome = number_format($overAllIncome, 2);
        $overAllExpense = number_format($overAllExpense, 2);
        $overAllProfit = number_format($overAllProfit, 2);
        $totalOverAllinPatient = number_format($totalOverAllinPatient, 2);
        $totalOverAllLabtest = number_format($totalOverAllLabtest, 2);
        $total_amount_visit = number_format($total_amount_visit, 2);
        $text = "
        <tr>
            <td class='incomeReport'>
                <table width=100%>
                    <tr>
                        <td width = 70%><span>Patient Visit</span></td>
                        <td width = 30% align='right'>{$total_amount_visit}</td>
                    </tr>
                    <!--<tr>
                        <td width = 70%><span>Lab Test</span></td>
                        <td width = 30% align='right'>{$totalOverAllLabtest}</td>
                    </tr>-->
                    <tr>
                        <td width = 70%><span>In Patient</span></td>
                        <td width = 30% align='right'>{$totalOverAllinPatient}</td>
                    </tr>
                </table>
            </td>
            <td class='incomeReport'>
                {$expense_group}
            </td>
        </tr>
        <tr>
            <td class='totalValue'>
                <div class='float_left'>Total</div> <div class='float_right'>{$overAllIncome}</div>
            </td>
            <td class='totalValue' align='right'>
                <div class='float_left'>Total</div> <div class='float_right'>{$overAllExpense}</div>
            </td>
        </tr>
        <tr>
            <td class='totalValue lastRowBgColor'>
                <div class='float_left '>Balance</div> <div class='float_right'>{$overAllProfit}</div>
            </td>
            <td class='' align='right'>
            </td>
        </tr>
        ";

        return $text;
    }

}