<?php
/**
 * @package ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Rate extends OW_ActionController
{

    public function __construct()
    {
        parent::__construct();
    }

    public function updateRate()
    {
        if ( empty($_POST['entityId']) || empty($_POST['entityType']) || empty($_POST['rate']) || empty($_POST['ownerId']) )
        {
            exit(json_encode(array('errorMessage' => 'Invalid request')));
        }

        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'update_rate')));
        }
        $service = BOL_RateService::getInstance();

        $entityId = (int) $_POST['entityId'];
        $entityType = trim($_POST['entityType']);
        $rate = (int) $_POST['rate'];

        if ( !OW::getUser()->isAuthenticated() )
        {
            exit(json_encode(array('errorMessage' => OW::getLanguage()->text('base', 'rate_cmp_auth_error_message'))));
        }

        $rateObj = $service->processUpdateRate($entityId, $entityType, $rate, OW::getUser()->getId());
        if ($rateObj['valid'] == false) {
            if (isset($rateObj['reason'])) {
                if ($rateObj['reason'] == 'same_user') {
                    exit(json_encode(array('errorMessage' => OW::getLanguage()->text('base', 'rate_cmp_owner_cant_rate_error_message'))));
                } else if ($rateObj['reason'] == 'user_block') {
                    exit(json_encode(array('errorMessage' => OW::getLanguage()->text('base', 'user_block_message'))));
                } else if ($rateObj['reason'] == 'no_access') {
                    exit(json_encode(array('errorMessage' => OW::getLanguage()->text('base', 'no_access_to_rate'))));
                }
            }
            exit(json_encode(array('errorMessage' => OW::getLanguage()->text('base', 'rate_cmp_auth_error_message'))));
        }

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true && ($entityType=='photo_rates' || $entityType=='blog-post')) {
            $totalScoreCmp = new PHOTO_MCMP_TotalScore($entityId, $entityType);
        }else {
            $totalScoreCmp = new BASE_CMP_TotalScore($entityId, $entityType);
        }

        exit(json_encode(array('totalScoreCmp' => $totalScoreCmp->render(), 'message' => OW::getLanguage()->text('base', 'rate_cmp_success_message'))));
    }

    public static function displayRate( array $params )
    {
        $service = BOL_RateService::getInstance();

        $minRate = 1;
        $maxRate = $service->getConfig(BOL_RateService::CONFIG_MAX_RATE);

        if ( !isset($params['avg_rate']) || (float) $params['avg_rate'] < $minRate || (float) $params['avg_rate'] > $maxRate )
        {
            return '_INVALID_RATE_PARAM_';
        }

        $width = (int) floor((float) $params['avg_rate'] / $maxRate * 100);

        return '<div class="inactive_rate_list"><div class="active_rate_list" style="width:' . $width . '%;"></div></div>';
    }
}