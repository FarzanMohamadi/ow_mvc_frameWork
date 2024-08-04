<?php
/**
 * RSS widget
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
require_once OW_DIR_LIB . 'rss' . DS . 'rss.php';

class BASE_CMP_RssWidget extends BASE_CLASS_Widget
{
    private $rss = array();

    private $titleOnly = false;

    private static $countInterval = array(1, 10);

    private $count = 5;

    public function __construct( BASE_CLASS_WidgetParameter $param )
    {
        parent::__construct();

        $this->titleOnly = (bool)$param->customParamList['title_only'];
        $this->assign('titleOnly', $this->titleOnly);
        $url = trim($param->customParamList['rss_url']);

        if ( !$url )
        {
            return;
        }

        $cacheKey = 'rss_widget_cache_' . $url;
        $cachedState = OW::getCacheService()->get($cacheKey);

        if ( $cachedState === false )
        {
            try
            {
                $rssLoading = OW::getConfig()->getValue('base', 'rss_loading');

                if ( !empty($rssLoading) && ( time() - $rssLoading ) < ( 60 * 5 ) )
                {
                    return;
                }
                OW::getConfig()->saveConfig('base', 'rss_loading', time(), null, false);

                $rssIterator = RssParcer::getIterator($param->customParamList['rss_url'], self::$countInterval[1]);

                OW::getConfig()->saveConfig('base', 'rss_loading', 0, null, false);
            }
            catch (Exception $e)
            {
                OW::getConfig()->saveConfig('base', 'rss_loading', 0, null,false);

                return;
            }

            foreach ( $rssIterator as $item )
            {
                if(strtotime($item->date)!=false) {
                    $item->time = strtotime($item->date);
                }else{
                    $item->time = $item->date;
                }
                $this->rss[] = (array) $item;
            }

            try
            {
                $updateTime = trim($param->customParamList['rss_updateTime']);
                if(!empty($updateTime)  && (int)$updateTime>0){
                    OW::getCacheService()->set($cacheKey, json_encode($this->rss), 60 * $updateTime);
                }else {
                    OW::getCacheService()->set($cacheKey, json_encode($this->rss), 60 * 60);
                }
            }
            catch (Exception $e) {}
        }
        else
        {
            $this->rss = (array) json_decode($cachedState, true);
        }

        $this->count = intval($param->customParamList['item_count']);
    }

    public function render()
    {
        $rss = array_slice($this->rss, 0, $this->count);
        $this->assign('rss', $rss);

        $toolbars = array();
        if ( !$this->titleOnly )
        {
            foreach ( $rss as $key => $item )
            {
                if(strtotime($item['date'])!=false){
                    $toolbars[$key] = array(array('label' => UTIL_DateTime::formatDate($item['time'])));
                }else {
                    $toolbars[$key] = array(array('label' => $item['time']));
                }
            }
        }
        $this->assign('toolbars', $toolbars);

        return parent::render();
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['rss_url'] = array(
            'presentation' => self::PRESENTATION_TEXT,
            'label' => OW::getLanguage()->text('base', 'rss_widget_url_label'),
            'value' => ''
        );

        $settingList['item_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('base', 'rss_widget_count_label'),
            'value' => 5
        );

        for ( $i = self::$countInterval[0]; $i <= self::$countInterval[1]; $i++ )
        {
            $settingList['item_count']['optionList'][$i] = $i;
        }

        $settingList['title_only'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('base', 'rss_widget_title_only_label'),
            'value' => false
        );

        $settingList['rss_updateTime'] = array(
            'presentation' => self::PRESENTATION_TEXT,
            'label' => OW::getLanguage()->text('base', 'rss_widget_update_time_label'),
            'value' => 60
        );
        return $settingList;
    }

    public static function validateSettingList( $settingList )
    {
        parent::validateSettingList($settingList);

        if ( !UTIL_Validator::isUrlValid($settingList['rss_url']) )
        {
            throw new WidgetSettingValidateException(OW::getLanguage()->text('base', 'rss_widget_url_invalid_msg'), 'rss_url');
        }
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'rss_widget_default_title'),
            self::SETTING_ICON => self::ICON_RSS
        );
    }


}