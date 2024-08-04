<?php
/**
 * FRMSHASTA widget
 *
 * @since 1.0
 */
class FRMSHASTA_CMP_MyFilesWidget extends BASE_CLASS_Widget
{

    /**
     * FRMSHASTA_CMP_CategoriesWidget constructor.
     * @param BASE_CLASS_WidgetParameter $params
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();
        $this->assignList($params);
        FRMSHASTA_BOL_Service::getInstance()->addStaticFiles();
    }

    private function assignList($params)
    {

        if (!OW::getUser()->isAuthenticated()) {
            return null;
        }
        $service = FRMSHASTA_BOL_Service::getInstance();

        $userFiles = $service->getUserFiles(OW::getUser()->getId());

        $fileIds = array();
        foreach ($userFiles as $userFile) {
            $fileIds[] = $userFile->id;
        }

        $service = FRMSHASTA_BOL_Service::getInstance();
        if (!$service->hasUserAccessManager()) {
            $this->assign('manageAccess', true);
        }
        $this->addComponent('files', new FRMSHASTA_CMP_Files(array('fileIds' => $fileIds, 'additionalId' => 'my_file_widget')));

        $service->addStaticFiles();
        $this->assign('allMyFilesUrl', OW::getRouter()->urlForRoute('frmshasta_view_all_my_files'));
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmshasta', 'my_files'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}