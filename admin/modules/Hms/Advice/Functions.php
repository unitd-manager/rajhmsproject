<?
class CPL_Admin_Modules_Hms_Advice_Functions {

    /**
     *
     */
    function setModuleArray($modules){

        $modObj = $modules->getModuleObj('hms_advice');
        $modObj['tableName'] = 'advice';
        $modObj['keyField']  = 'advice_id';
        $modules->registerModule($modObj, array(
            'actBtnsList'   => array('new')
           ,'actBtnsDetail' => array('edit')
           ,'actBtnsEdit'   => array('apply','save')
           ,'relatedTables' => array('media')
           ,'title'         => 'Advice List'
           ,'hasFlagInList' => 0
        ));
    }
    /**
     *
     */

    function setMediaArray($mediaArr) {

        $mediaObj = $mediaArr->getMediaObj('hms_advice', 'attachment', 'attachment');

        $mediaArr->registerMedia($mediaObj, array(
        ));
    }


}
