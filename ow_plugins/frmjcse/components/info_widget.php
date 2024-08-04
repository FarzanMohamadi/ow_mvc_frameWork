<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class FRMJCSE_CMP_InfoWidget extends BASE_CLASS_Widget
{

    private static $items = ['count_article','count_author','count_estenad', 'count_shomare', 'count_dore', 'h-index',
        'count_dl', 'count_view', 'rate_accept', 'average_time'];

    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        // frmgraph is neccessary for more statistics
        if(FRMSecurityProvider::checkPluginActive('frmgraph', true)) {
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmgraph')->getStaticJsUrl() . 'countUp.js', 'text/javascript', (-100));
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmgraph')->getStaticCssUrl() . 'countup.css');
        }

        $infoItems = array();

        foreach (self::$items as $item){
            $val = false;
            if($item=='count_view'){
                $val = FRMJCSE_BOL_ArticleDao::getInstance()->getAllViewedCount();
            }
            if($item=='count_dl'){
                $val = FRMJCSE_BOL_ArticleDao::getInstance()->getAllDownloadCount();
            }
            if($item=='count_article'){
                $val = FRMJCSE_BOL_ArticleDao::getInstance()->countPublishedPapers();
            }
            if($item=='count_author'){
                $val = FRMJCSE_BOL_AuthorDao::getInstance()->countAll();
            }
            if($item=='count_shomare'){
                $val = FRMJCSE_BOL_IssueDao::getInstance()->countPublishedIssues();
            }

            $val = !empty($paramObj->customParamList[$item]) ? $paramObj->customParamList[$item] : $val;

            //--------
            if(empty($val)){
                continue;
            }
            $infoItems[] = array(
                'class' => $item,
                'count' => $val,
                'title' => OW::getLanguage()->text('frmjcse', 'info_'.$item),
            );
            if(is_numeric($val)) {
                OW::getDocument()->addOnloadScript("countUpProcess('statistical_info_item_{$val}', {$val});");
            }
        }

        $this->assign('items', $infoItems);

    }

    public static function getSettingList()
    {
        $settingList = array();

        foreach (self::$items as $item){
            $settingList[$item] = array(
                'presentation' => self::PRESENTATION_TEXT,
                'label' => OW::getLanguage()->text('frmjcse', 'info_'.$item),
                'value' => ''
            );
        }
        return $settingList;
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW_Language::getInstance()->text('frmjcse', 'info_widget_title'),
            self::SETTING_ICON => self::ICON_COMMENT,
            self::SETTING_SHOW_TITLE => true
        );
    }
}