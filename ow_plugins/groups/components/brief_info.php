<?php
/**
 * Group Brief Info
 *
 * @package ow_plugins.groups.components
 * @since 1.0
 */
class GROUPS_CMP_BriefInfo extends OW_Component
{
    /**
     * GROUPS_CMP_BriefInfo constructor.
     * @param $groupId
     * @param array $additionalInfo
     */
    public function __construct($groupId, $additionalInfo = array())
    {
        parent::__construct();
        
        $this->addComponent('content', new GROUPS_CMP_BriefInfoContent($groupId, $additionalInfo));
        
        $this->assign('box', $this->getBoxParmList($groupId));
    }
    
    private function getBoxParmList($groupId)
    {
        $settings = GROUPS_CMP_BriefInfoWidget::getStandardSettingValueList();
        $defaultSettings = BOL_ComponentAdminService::getInstance()->findSettingList('group-GROUPS_CMP_BriefInfoWidget');
        $customSettings = BOL_ComponentEntityService::getInstance()->findSettingList('group-GROUPS_CMP_BriefInfoWidget', $groupId);
        
        $out = array_merge($settings, $defaultSettings, $customSettings);
        $out['type'] = $out['wrap_in_box'] ? '' : 'empty';
        
        return $out;
    }
}