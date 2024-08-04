<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.admin.components
 * @since 1.7.5
 */
class ADMIN_CMP_UploadedFilesFilter extends OW_Component
{

    public function __construct($params = array())
    {
        parent::__construct();
    }

    private function getDates($images)
    {
        $dates = array();
        foreach ($images as $image)
        {
            if ( $image->addDatetime )
            {
                $tmpDateArray = explode('/', date('Y/m/d',$image->addDatetime));
                $jalaliDate = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_DEFAULT_DATE_VALUE_SET, array('changeTojalali' => true, 'yearTochange' =>  (int) $tmpDateArray[0], 'monthTochange'=> (int) $tmpDateArray[1] ,'dayTochange'=> (int)$tmpDateArray[2], 'monthWordFormat' =>false)));
                $convertedToJalali =false;
                if($jalaliDate->getData() && isset($jalaliDate->getData()['changedYear'])) {
                    $faYear = $jalaliDate->getData()['changedYear'];
                    $convertedToJalali = true;
                }
                if($jalaliDate->getData() && isset($jalaliDate->getData()['changedMonth'])){
                    $faMonth= $jalaliDate->getData()['changedMonth'];
                    $convertedToJalali = true;
                }
                if($jalaliDate->getData() && isset($jalaliDate->getData()['changedDay'])){
                    $faDay = $jalaliDate->getData()['changedDay'];
                    $convertedToJalali = true;
                }

                if($convertedToJalali){
                    $changeMonthToWordFormatEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_DEFAULT_DATE_VALUE_SET, array('changeJalaliMonthToWord' => true, 'faYear' =>  (int) $faYear, 'faMonth'=> (int) $faMonth ,'faDay'=> (int) $faDay)));
                    $cfMonth = $changeMonthToWordFormatEvent->getData()['jalaliWordMonth'];
                    $maxDayOfMonthEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_DEFAULT_DATE_VALUE_SET, array('jalaliMaxMonthDay' => true, 'jalaliYear' =>  (int) $faYear, 'jalaliMonth'=> (int) $faMonth)));
                    if($maxDayOfMonthEvent->getData() && isset($maxDayOfMonthEvent->getData()['lastDay'])){
                        if($maxDayOfMonthEvent->getData()['lastDay']<$faDay){
                            $faDay=$maxDayOfMonthEvent->getData()['lastDay'];
                        }
                    }
                    $dates[$faYear. '-' .$faMonth. '-' .$faDay] = $faDay . ' ' .$cfMonth . ' ' . $faYear;
                    //$dates[date('Y-m-t', $image->addDatetime)] = date('F Y', $image->addDatetime);
                }
                else {
                    $dates[date('Y-m-t', $image->addDatetime)] =  $tmpDateArray[0] . ' ' . OW::getLanguage()->getInstance()->text('base','date_time_month_short_'.(int)$tmpDateArray[1]);
                }
            }
        }
        ksort($dates);
        return array_reverse($dates, true);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $id = FRMSecurityProvider::generateUniqueId('filter');
        $this->assign('id', $id);
        $images = BOL_ThemeService::getInstance()->findAllCssImages();
        $this->assign('dates', $this->getDates($images));
        $jsString = ";$('#{$id} ul li a').click(function(e){
            e.preventDefault();
            window.browsePhoto.filter({'date': $(this).data('date')});
            $(this).parents('.ow_context_action').find('.ow_context_action_value span').html($(this).html());
        });
        ";
        OW::getDocument()->addOnloadScript($jsString);
    }
}
