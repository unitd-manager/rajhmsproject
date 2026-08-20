<?
class CPL_Admin_Themes_Angle_View extends CP_Admin_Themes_Angle_View
{
    var $jssKeys = array('blend-2.2', 'jscrollpane-2.0', 'noty-2.0.3');

    /**
     *
     */
    function getHeaderPanel() {
        $cpCfg = Zend_Registry::get('cpCfg');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $ln = Zend_Registry::get('ln');
        $db = Zend_Registry::get('db');
        $dbUtil = Zend_Registry::get('dbUtil');
        $cpUtil = Zend_Registry::get('cpUtil');
        $modulesArr = Zend_Registry::get('modulesArr');

        $module_name = $tv['module'];
        $module_title = $modulesArr[$module_name]['title'];

        $mainNav = includeCPClass('Lib', 'Room', 'Room');
        Zend_Registry::set('mainNav', $mainNav);

        $SERVER = $_SERVER['HTTP_HOST'];

        $logoText = '';

        if (isLoggedInAdmin()) {
            $logoText = "<h1 class='siteTitle'>{$cpCfg['cp.siteTitle']}</h1>";

            if ($cpCfg['cp.hasAdminOnly'] == false) {
                $logoText = "<a href='/' target='_blank'>{$logoText}</a>";
            }

            if($module_title == 'Home'){
                $logoText = $logoText;
            }else{
                if($tv['action'] != 'list') {
                    $logoText = "<h1 class='siteTitle'>".$modulesArr[$module_name]['title'] ." ". "Details</h1>";
                }else{
                    $logoText = "<h1 class='siteTitle'>{$module_title}</h1>";
                }

            }

            $logoText = "
            <div class='logo float_left'>
                {$logoText}
            </div>
            ";

        } else {
            //$logoText = "<img src='images/HMS logo.png' width='50' height='50'/>";
        }

        $rightText = '';
        $cpMultiYearText = '';
        $cpAdminInterfaceLangText = '';
        $cpMultiUniqueSiteText = '';

        //multi country widget
        if (isLoggedInAdmin() && $cpCfg['cp.multiCountry']) {
            if (!in_array($tv['module'], $cpCfg['w.common_multiCountry.ignoreModules'])) {
                $wMultiCountry = getCPWidgetObj('common_multiCountry');

                $cpMultiYearText = "
                <div class='float_left'>
                    <div class='multi-country'>{$wMultiCountry->getWidget()}</div>
                </div>
                ";
            }
        }

        //admin interface langs
        if (isLoggedInAdmin() && $cpCfg['cp.hasAdminInterfaceLangs']) {
            $wAdminTranslation = getCPWidgetObj('common_adminTranslation');

            $cpAdminInterfaceLangText = "
            <div class='float_left'>
                <div class='admin-langs'>{$wAdminTranslation->getWidget()}</div>
            </div>
            ";
        }

        if (isLoggedInAdmin() && $cpCfg['cp.hasMultiYears']) {
            if (!in_array($tv['module'], $cpCfg['w.common_multiYear.ignoreModules'])) {
                $wMultiYear = getCPWidgetObj('common_multiYear');

                $cpMultiYearText = "
                <div class='float_left'>
                    <div class='multi-year'>{$wMultiYear->getWidget()}</div>
                </div>
                ";
            }
        }

        $userGroupType = $fn->getSessionParam('userGroupType');

        //&& $userGroupType == 'Super Administrator'

        if (isLoggedInAdmin() && $cpCfg['cp.hasMultiUniqueSites'] && $_SESSION['isDeveloper'] == 1) {
            if (!in_array($tv['module'], $cpCfg['w.common_multiUniqueSite.ignoreModules'])) {
                $wMultiUniqueSite = getCPWidgetObj('common_multiUniqueSite');

                $cpMultiUniqueSiteText = "
                <div class='float_right'>
                    <div class='multi-unique-site'>{$wMultiUniqueSite->getWidget()}</div>
                </div>
                ";
            }
        }

        $logged_IN_text_Logout = '';
        $homeMenuDisplay   = '';
        $leftMenuShowHide  = '';
        $helpAndGetStarted = '';
        $siteName          = '';
        $TimeoutButton     = '';
        $TimeinButton     = '';
        $TimeinButton2     = '';
        $TimeoutButton2     = '';
        if (isLoggedInAdmin()) {

            $leftMenuShowHide = "<a class='leftnavShowHide leftnavShowHideicon'></a>";

            $homeMenuDisplay = "<div>
                                    {$this->getHomeMenuDisplay()}
                                </div>";

            $helpAndGetStarted = "
            <div class='float_right helpAndGetStarted'>
                <div class='getStarted float_left'>
                    <a class='getStartedContentTask button btn btn-default' href='#'>Get Started</a>
                </div>

            </div>
            ";

            $TimeoutButton = "
            <div class='float_right TimeoutButtonHeader'>
                <a class='btn btn-danger TimeoutHeaderButton' href='#' staff_id={$_SESSION['staff_id']}>Time Out</a>
            </div>
            ";

            $TimeoutButton2 = "
            <div class='float_right TimeoutButtonHeader'>
                <a class='btn btn-primary TimeoutHeaderButton2' href='#' staff_id={$_SESSION['staff_id']}>Time Out</a>
            </div>
            ";

            $today = date("Y-m-d");

            $SQLAttend = "
            SELECT *
            FROM attendance
            WHERE record_date = '{$today}'
            AND staff_id = {$_SESSION['staff_id']}";
            $resultAttend = $db->sql_query($SQLAttend);
            $rowAttend = $db->sql_fetchrow($resultAttend);

            if($rowAttend['time_in'] == ""){
                $TimeinButton = "
                <div class='float_left TimeinButtonHeader'>
                    <input class='btn btn-success TimeinHeaderButton' staff_id={$_SESSION['staff_id']} value='TIME IN DAY SHIFT'/>
                </div>
                ";
            } else {
                $TimeinButton = "
                <div class='float_left TimeinButtonHeader'>
                    <input class='btn btn-success TimeinHeaderButton' staff_id={$_SESSION['staff_id']} value='TIME IN DAY SHIFT' disabled/>
                </div>
                ";                
            }
            if($rowAttend['time_in_shift2'] == ""){
                $TimeinButton2 = "
                <div class='float_left TimeinButtonHeader'>
                    <input class='btn btn-primary TimeinHeaderButton2' staff_id={$_SESSION['staff_id']} value='TIME IN NIGHT SHIFT' />
                </div>
                ";
            }else{
                $TimeinButton2 = "
                <div class='float_left TimeinButtonHeader'>
                    <input class='btn btn-success TimeinHeaderButton2' staff_id={$_SESSION['staff_id']} value='TIME IN NIGHT SHIFT' disabled/>
                </div>
                ";
            }

            /*<div class='helpContent float_right'>
                    <a class='helpContentTask button btn btn-default' module_name='{$module_title}' href='#'>Help</a>
                </div>*/
            if(!changePasswordOnLogin()){
                $logged_IN_text_Logout = "
                    <div class='float_right logoutWrap'>
                        <span class='username mr5'>{$ln->gd('cp.lbl.welcome', 'Welcome')} {$_SESSION['userFullName']}</span>
                        <div class='txtRight mr10 ul'>
                            <a href='index.php?plugin=common_login&_spAction=logout' class='logout'>
                                {$ln->gd('cp.lbl.logout', 'Logout')}
                            </a>
                        </div>
                    </div>
                ";
            }

            if ($cpCfg['cp.hasMultiUniqueSites']) {
                $cpSiteIdSession = $fn->getSessionParam('cp_site_id');

                $SQLSite = "
                SELECT title
                FROM site 
                WHERE site_id = {$cpSiteIdSession}
                ";
                $resultSite = $db->sql_query($SQLSite);
                $rowSite    = $db->sql_fetchrow($resultSite);

                $siteName = "
                <div class='float_right SiteNameOnHeader'>
                    - {$rowSite['title']}
                </div>
                ";
            }
        }

        $leftText = "
        <div id='header-left'>
            <div class='floatbox'>
                {$logged_IN_text_Logout}
                {$helpAndGetStarted}
                {$cpMultiUniqueSiteText}
                {$cpMultiYearText}
                {$cpAdminInterfaceLangText}
                {$leftMenuShowHide}
                {$homeMenuDisplay}
                <div class='float_left'>
                    {$logoText}{$siteName}
                </div>
            </div>
        </div>
        ";

        if (isLoggedInAdmin()) {
            $topRooms = '';
            if(!changePasswordOnLogin()){
                $topRooms = "
                <div class='float_right'>
                    <div class='hlist noBg'>
                        {$mainNav->getTopRooms()}
                    </div>
                </div>
                ";
            }
            $rightText = "
            <div id='topnav'>
            {$topRooms}
            </div>
            ";
        }

        $actions = '';
        if ($cpCfg['cp.showActionPanelInHeader']) {
            $action = Zend_Registry::get('action');

            if ($tv['action'] != 'new') {
                $actions = "
                <div class='hlist actionBtns noBg'>
                    {$action->getActionButtons()}
                </div>
                ";
            }
        }

        $getStartedPopupOnloadSession = $fn->getSessionParam('getStartedPopupOnloadSession');
        $getStartedPopuphidden = "<input type='hidden' id='getStartedPopupOnloadSession' value={$getStartedPopupOnloadSession}>";

        $getLocationPopupOnloadhidden = '';
        /*$locationSetPopup = '';
        if (isLoggedInAdmin()) {
            $getLocationPopupOnloadSession = $fn->getSessionParam('getLocationPopupOnloadSession');
            $getLocationPopupOnloadhidden  = "<input type='hidden' id='getLocationPopupOnloadSession' value={$getLocationPopupOnloadSession}>";

            $cp_site_id = $fn->getSessionParam('cp_site_id');
        
            $SQL = "
            SELECT site_id
                 , title
            FROM site
            ";
            $arr = $dbUtil->getArrayFromSQLForVL($SQL);

            $locationSetPopup = "
            <div id='locationChoosemodal' class='modal fade' data-backdrop='static'>
              <div class='modal-dialog'>
                <div class='modal-content'>
                  <div class='modal-header'>
                    <h4 class='modal-title'>Please Choose the Location below (Location is the clinic where you work)</h4>
                  </div>
                  <div class='modal-body'>
                    <div class='onLoadLocationSelectDropdown'>
                        <label>{$cpCfg['cp.chooseSiteLbl']}</label>
                        <select name='chooseLocationByUserDropdown'>
                            {$cpUtil->getDropDownFromArr($arr, $cp_site_id)}
                        </select>
                    </div>
                  </div>
                  <div class='modal-footer'>
                    <button type='button' class='btn btn-primary chooseLocationByUserSubmit' data-dismiss='modal'>Set Location</button>
                  </div>
                </div>
              </div>
            </div>
            ";
        }*/

        //{$locationSetPopup} 


        $text = "
        {$getStartedPopuphidden}
        {$leftText}
        {$actions}
        {$getLocationPopupOnloadhidden}
        ";

        return $text;
    }

    /**
     *
     */
    function getMainThemeOutput() {
        $modulesArr = Zend_Registry::get('modulesArr');
        $cpCfg = Zend_Registry::get('cpCfg');
        $ln = Zend_Registry::get('ln');
        $fn = Zend_Registry::get('fn');
        $tv = Zend_Registry::get('tv');
        $db = Zend_Registry::get('db');
        $viewHelper = Zend_Registry::get('viewHelper');

        $headerPanel    = $this->getHeaderPanel();
        $navPanel       = $this->getNavPanel();
        $leftPanel      = $this->getLeftPanel();
        $moduleName     = $tv['module'];
        $moduleTitle = $modulesArr[$moduleName]['title'];
        if($tv['action'] != 'list') {
            $moduleTitle = $modulesArr[$moduleName]['title'] .' '. 'Details';
        }

        $rightPanel = '';
        if ($tv['action'] == 'list') {
            $rightPanel = $this->getListRightPanel();
        }
        $bodyPanel      = $this->getBodyPanel();
        $navPanel2      = $this->getNavPanel2(); //note: this line has to be below the $bodyPanel
        $extendedPanel  = $this->getExtendedPanel();
        $footerPanel    = $this->getFooterPanel();
        $pageCSSClass   = $viewHelper->getPageCSSClass();
        $pagerPanel = '';
        if($cpCfg['cp.showPagerPanelInFooter']){
            $pagerPanel = "
            <div class='float_left pagelinksBottom'>
                {$this->getPagerPanel()}
            </div>
            ";
        }
        $leftCol = '';
        if($tv['module'] != 'hms_home'){
            $leftCol = "
            <aside id='col1' style='display: none;'>
                <div id='col1_content' class='clearfix'>
                    {$leftPanel}
                </div>
            </aside>
            ";
        }

        $patient_queue_display = '';
        if($tv['module'] == 'hms_patientVisit'){
            /*$patient_queue_display = "
            <div class = 'float_right'>{$this->getPatientQueueNo()}</div>
            ";*/
        }

        $paymentReminder = '';
        if ($cpCfg['paymentReminder'] == 1) {
            $paymentReminder ="
            <div><marquee><strong>KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. PLEASE CONTACT 7530012968. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. PLEASE CONTACT 7530012968. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. PLEASE CONTACT 7530012968. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </strong></marquee></div>
            ";
        }

        $mainInner = "
        <div class='mainInner'>
            {$paymentReminder}
            {$leftCol}
            <aside id='col2'>
                <div id='col2_content' class='clearfix'>
                    {$rightPanel}
                </div>
            </aside>

            <div id='col3' class='fullleftlist'>
                <div id='col3_content' class='clearfix contentScroller'>
                <div class='moduleTitle'>
                    <div class = 'float_left moduleName'>{$moduleTitle}</div>
                    {$patient_queue_display}
                </div>
                <nav id='nav2'>
                <div id='goTop'></div>
                    <div class=''>
                        <div class='page'>
                            {$navPanel2}
                        </div>
                    </div>
                </nav>
                    {$bodyPanel}
                </div>
                <div id='ie_clearing'>&nbsp;</div>
            </div>
        </div>
        ";

        if($cpCfg['cp.fullWidthTemplte']){
            $SQLActivation = "
            SELECT published
            FROM staff
            WHERE staff_id = {$_SESSION['staff_id']}
            AND published  = 1
            ";
            $resultActivation  = $db->sql_query($SQLActivation);
            $numRowsActivation = $db->sql_numrows($resultActivation);
            $rowActivation     = $db->sql_fetchrow($resultActivation);

            $activation_expiry_status = "";
            if($numRowsActivation == 0){
                $activation_expiry_status = "Expired";
            }

            /*if($_SESSION['isDeveloper'] == 1){
                $activation_expiry_status = "";
            }*/
            
            $inputHiddenForActivation = "
            <input type='hidden' name='activation_expiry_status' value='{$activation_expiry_status}' />
            <input type='hidden' name='paymentReminder2' value='{$cpCfg['paymentReminder2']}' />
            ";
            
            $text = "
            <header id='header'>
                <div class='page_margins'>
                    <div class='page'>
                        {$headerPanel}
                    </div>
                </div>
            </header>
            <div id='main' class='{$pageCSSClass} clearfix'>
                <div class='page_margins'>
                    <div class='page'>
                        {$mainInner}
                    </div>
                </div>
                {$inputHiddenForActivation}
            </div>
            <div id='extended'>
                <div class='page_margins'>
                    <div class='page'>
                        {$extendedPanel}
                    </div>
                </div>
            </div>
            <a href='#goTop' class='scrollToTop'>
                <i class='fa fa-arrow-up'></i>
            </a>
            <footer id='footer'>
                <div class='page_margins'>
                    <div class='page'>
                        {$footerPanel}
                    </div>
                </div>
            </footer>
            ";

        } else {

            if ($navPanel != ''){
                $navPanel = "
                <nav id='nav'>
                    {$navPanel}
                </nav>
                ";
            }

            if ($navPanel2 != ''){
                $navPanel2 = "
                <nav id='nav2'>
                    {$navPanel2}
                </nav>
                ";
            }

            $text = "
            <div class='page_margins'>
                <div class='page'>
                    <header id='header'>
                        {$headerPanel}
                    </header>
                    {$navPanel}
                    {$navPanel2}

                    <div id='main' class='{$pageCSSClass} clearfix'>
                        {$mainInner}
                    </div>
                    <a href='#' class='scrollToTop'>
                        <i class='fa fa-arrow-up'></i>
                    </a>
                    {$pagerPanel}
                    <footer id='footer'>
                        {$footerPanel}
                    </footer>
                </div>
            </div>
            ";
        }

        $logged_in = $fn->getReqParam('logged_in');
        $random_id = $fn->getReqParam('random_id');
        if ($logged_in == 1 && $random_id != "" && $cpCfg['cp.autoLoginToIntranet'] == 1){
            $autoLoginUrl = $cpCfg['intranetUrl'] . "index.php?_spAction=autoLoginUserByRandomID&showHTML=0&random_id={$random_id}";
            $text .= "<iframe id='utilFrame' name='utilFrame' class='utilFrame' src='{$autoLoginUrl}'></iframe>";
        }

        return $text;
    }

    /**
     *
     */
    function getPatientQueueNo() {
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $currentDate  = date("Y-m-d");

        $SQL ="
        SELECT MIN(pq.queue_no) AS queue_no
              ,pv.employee_id
              ,CONCAT_WS(' ', p.first_name, p.middle_name, p.last_name) AS Patient_Name
              ,CONCAT_WS(' ', e.first_name, e.middle_name, e.last_name) AS Doctor_Name
              ,pv.patient_visit_id
        FROM `patient_queue` pq
        LEFT JOIN patient_visit pv ON (pv.patient_information_id = pq.patient_information_id)
        LEFT JOIN employee_visit ev ON (ev.patient_visit_id = pv.patient_visit_id)
        LEFT JOIN employee e ON (e.employee_id = ev.employee_id)
        LEFT JOIN patient_information p ON (p.patient_information_id = pv.patient_information_id)
        WHERE pv.check_up_date = '{$currentDate}'
        AND pv.status NOT IN ('Visited', 'Cancelled', 'Closed')
        GROUP BY pv.employee_id
        ";

        $result  = $db->sql_query($SQL);
        $numRows = $db->sql_numrows($result);

        $text = '';
        $queueDisplay = "<div class='queueNumberDisplay'>
                            {$text}
                         </div>
                         ";

        if($numRows > 0){
            while ($row = $db->sql_fetchrow($result)) {
                $doctorCode = substr($row['Doctor_Name'], 0, 2);
                $SQLQueue ="
                SELECT pq.patient_visit_id
                FROM patient_queue pq
                LEFT JOIN patient_visit pv ON (pv.patient_visit_id = pq.patient_visit_id)
                WHERE pv.employee_id = {$row['employee_id']}
                AND pv.check_up_date = '{$currentDate}'
                AND pv.status NOT IN ('Visited', 'Cancelled')
                AND pv.patient_visit_id != {$row['patient_visit_id']}
                ";
                $resultQueue  = $db->sql_query($SQLQueue);
                $numRowsQueue = $db->sql_numrows($resultQueue);

                $nextQueueNoLink = '';
                if($numRowsQueue >= 1){
                    $nextQueueNoLink = "<a class='nextQueueNo' queue_no={$row['queue_no']} employee_id={$row['employee_id']} >Next</a>";
                }

                $patientVisitLink = "index.php?_topRm=main&module=hms_patientVisit&_action=edit&patient_visit_id={$row['patient_visit_id']}";
                $createVisit = "<a class = 'viewVisitRecord' href='{$patientVisitLink}'>
                                    {$row['Patient_Name']}
                                </a>
                ";

                $text .= "
                <div class = 'queueNoTable divtoBlink'>
                    <div class='float_left'>{$doctorCode}: QUE{$row['queue_no']}</div>
                    <div class='float_right'>Waiting: {$numRowsQueue}</div><br/>
                    <div class='float_left'>Patient Name: <u>{$createVisit}</u></div>
                    <div class='float_right'>{$nextQueueNoLink}</div>
                </div>
                ";
            }

            $queueDisplay = "
                <div class='queueNumberDisplay'>
                    {$text}
                </div>
            ";
        }

        return $queueDisplay;
    }

    /**
     *
     */
    function getUpdateQueueNoNext(){
        $db = Zend_Registry::get('db');
        $tv = Zend_Registry::get('tv');
        $fn = Zend_Registry::get('fn');
        $dbUtil = Zend_Registry::get('dbUtil');

        $queue_no    = $fn->getReqParam('queue_no');
        $employee_id = $fn->getReqParam('employee_id');
        $currentDate  = date("Y-m-d");

        $SQLQueue = "
        SELECT pq.patient_visit_id
        FROM patient_queue pq
        LEFT JOIN patient_visit pv ON (pv.patient_visit_id = pq.patient_visit_id)
        WHERE pq.queue_no = {$queue_no}
        AND pv.employee_id = {$employee_id}
        AND pv.check_up_date = '{$currentDate}'
        ";
        $resultQueue  = $db->sql_query($SQLQueue);
        $rowQueue     = $db->sql_fetchrow($resultQueue);

        $updatePatientVisitSQL = "
        UPDATE patient_visit SET status = 'Visited'
        WHERE patient_visit_id = {$rowQueue['patient_visit_id']}
        ";
        $resultPatientVisitSQL = $db->sql_query($updatePatientVisitSQL);

        $fa = array();
        $whereCondition = "WHERE patient_visit_id = {$rowQueue['patient_visit_id']}";
        $updatePatientVisitSQL = $dbUtil->getUpdateSQLStringFromArray($fa, 'patient_visit', $whereCondition);
        $db->sql_query($updatePatientVisitSQL);
    }

    /**
     *
     */
    function getLoginThemeOutput() {
        $login = getCPPluginObj('common_login');
        $fn = Zend_Registry::get('fn');
        $cpCfg = Zend_Registry::get('cpCfg');

        $headerPanel = $this->getHeaderPanel();
        $footerPanel = $this->getFooterPanel();

        $loginTitle ="
        <div class='floatbox'>
            <div class='float_left'><img src='images/logo-print.jpg' width='250'></div>
            <div class='float_left loginTitle'><h1>Welcome to Dr Raj Kanna Clinic Management System</h1></div>
        </div>
        ";

        $paymentReminder = '';
        if ($cpCfg['paymentReminder'] == 1) {
            $paymentReminder ="
            <div><marquee><strong>KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. PLEASE CONTACT 7530012968. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. PLEASE CONTACT 7530012968. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            KIND ATTENTION: PLEASE PAY YOUR SUBSCRIPTION DUE AMOUNT WITHIN 10TH TO AVOID DISCONNECTION OF SERVICE. PLEASE CONTACT 7530012968. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            </strong></marquee></div>
            ";
        }

        $mainInner = "
        <div class='mainInner'>
            {$paymentReminder}
            <div id='col3'>
                <div id='col3_content' class='clearfix'>
                    {$loginTitle}
                    {$login->getLoginForm()}
                </div>
                <div id='ie_clearing'>&nbsp;</div>
            </div>
        </div>
        ";

        if($cpCfg['cp.fullWidthTemplte']){
            $text = "
            <div class='tplLogin'>
            <header id='header'>
                <div class='page_margins'>
                    <div class='page'>
                        {$headerPanel}
                    </div>
                </div>
            </header>
            <div id='main' class='clearfix'>
                <div class='page_margins'>
                    <div class='page'>
                        {$mainInner}
                    </div>
                </div>
            </div>
            <footer id='footer'>
                <div class='page_margins'>
                    <div class='page'>
                        {$footerPanel}
                    </div>
                </div>
            </footer>
            </div>
            ";

        } else {
            $text = "
            <div class='page_margins tplLogin'>
                <div class='page'>
                    <header id='header'>
                        {$headerPanel}
                    </header>
                    <div id='main' class='clearfix floatbox'>
                        {$mainInner}
                    </div>
                    <footer id='footer'>
                        {$footerPanel}
                    </footer>
                </div>
            </div>
            ";
        }

        return $text;
    }

    /**
     *
     */
    function getNavPanel2(){
        $tv = Zend_Registry::get('tv');
        $cpCfg = Zend_Registry::get('cpCfg');
        $modulesArr = Zend_Registry::get('modulesArr');
        $searchHTML = Zend_Registry::get('searchHTML');
        $action = Zend_Registry::get('action');
        $pager = Zend_Registry::get('pager');

        $langBtns = '';
        $searchText = '';
        if ($tv['action'] == 'list') {
            $searchText = $searchHTML->getSearchHTML($tv['module']);
        }

        if (($tv['action'] == 'edit' || $tv['action'] == 'detail')
            && $modulesArr[$tv['module']]['hasMultiLang'] == 1
            && $cpCfg['cp.multiLang'] == 1
                ){
            $wLang = getCPWidgetObj('common_language');
            $langBtns = $wLang->getWidget();
        }

        $actions = '';
        if ($tv['action'] != 'new') {
            $actions = $action->getActionButtons();

            if($tv['action'] == 'edit' || $tv['action'] == 'detail'){
                $actions .=" 
                <div class='float_right backToList'>
                    {$pager->getBackButton()}
                </div>";
            }
        }

        $text = "
        <div class='floatbox'>
            <div class='float_left'>
                {$searchText}
                {$langBtns}
            </div>
            <div class='float_right'>
                <div class='hlist actionBtns'>
                    {$actions}
                </div>
            </div>
        </div>
        ";

        return $text;
    }

    /**
     *
     */
    function getBodyPanel() {
        $tv = Zend_Registry::get('tv');
        $clsInst = Zend_Registry::get('currentModule');

        $modulesArr = Zend_Registry::get('modulesArr');
        $module = $modulesArr[$tv['module']];
        $scrollContent = $module['scrollContent'];

        $actionName = ucfirst($tv['action']);
        $actionTemp = "get{$actionName}";  //eg: getList
        if (!method_exists($clsInst, $actionTemp)) {
            $clsName = ucfirst($tv['module']);

            $error = includeCPClass('Lib', 'Errors', 'Errors');
            $exp = array(
                'replaceArr' => array(
                    'clsName' => $clsName
                    , 'funcName' => $actionTemp
                )
            );
            print $error->getError('themeMethodNotFound', $exp);
            exit();
        }

        $text = $clsInst->$actionTemp();
        if ($scrollContent) {
            $text = "
            <div class='listTblWrapper'>
                {$text}
            </div>
            ";
        }

        return $text;
    }
}