<?php
/**
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_Rate extends OW_Component
{

    public function __construct( $pluginKey, $entityType, $entityId, $ownerId )
    {
        parent::__construct();
        if(OW::getPluginManager()->isPluginActive('frmwidgetplus') && OW::getConfig()->getValue('frmwidgetplus', 'displayRateWidget')==2 && !OW::getUser()->isAuthenticated()) {
            $this->assign('display', false);
            return;
        }
        else
            $this->assign('display', true);
        $service = BOL_RateService::getInstance();

        $maxRate = $service->getConfig(BOL_RateService::CONFIG_MAX_RATE);

        $cmpId = FRMSecurityProvider::generateUniqueId();

        $entityId = (int) $entityId;
        $entityType = trim($entityType);
        $ownerId = (int) $ownerId;

        if ( OW::getUser()->isAuthenticated() )
        {
            $userRateItem = $service->findRate($entityId, $entityType, OW::getUser()->getId());

            if ( $userRateItem !== null )
            {
                $userRate = $userRateItem->getScore();
            }
            else
            {
                $userRate = null;
            }
        }
        else
        {
            $userRate = null;
        }

        $this->assign('maxRate', $maxRate);
        $this->addComponent('totalScore', new BASE_CMP_TotalScore($entityId, $entityType, $maxRate));
        $this->assign('cmpId', $cmpId);

        $jsParamsArray = array(
            'cmpId' => $cmpId,
            'userRate' => $userRate,
            'entityId' => $entityId,
            'entityType' => $entityType,
            'itemsCount' => $maxRate,
            'respondUrl' => OW::getRouter()->urlFor('BASE_CTRL_Rate', 'updateRate'),
            'ownerId' => $ownerId
        );
        $code='';
        $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
            array('senderId'=>OW::getUser()->getId(),'receiverId'=>$entityId,'isPermanent'=>true,'activityType'=>'update_rate')));
        if(isset($frmSecuritymanagerEvent->getData()['code'])){
            $code = $frmSecuritymanagerEvent->getData()['code'];
            $jsParamsArray['respondUrl'] = OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlFor('BASE_CTRL_Rate', 'updateRate'),array('code' =>$code));
        }
        OW::getDocument()->addOnloadScript("var rate$cmpId = new OwRate(" . json_encode($jsParamsArray) . "); rate$cmpId.init();");
    }
}