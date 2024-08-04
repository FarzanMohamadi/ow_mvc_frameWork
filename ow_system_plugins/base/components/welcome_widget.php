<?php
/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_system_plugins.base.components
 * @since 1.7.2
 */
class BASE_CMP_WelcomeWidget extends BASE_CLASS_Widget
{
    CONST PATTERN = '/<li>.+{$key}.+<\/li>/i';

    CONST KEY_PHOTO_UPLOAD = 'photo_upload';
    consT KEY_CHANGE_AVATAR = 'change_avatar';

    public function __construct( BASE_CLASS_WidgetParameter $paramObject )
    {
        parent::__construct();

        $text = OW::getLanguage()->text('base', 'welcome_widget_content');
        $text = str_replace('</li>', "</li>\n", $text); //if the tags are written in a line it is necessary to make a compulsory hyphenation?
        $photoKey = str_replace('{$key}', self::KEY_PHOTO_UPLOAD, self::PATTERN);
        $avatarKey = str_replace('{$key}', self::KEY_CHANGE_AVATAR, self::PATTERN);
        
        if ( OW::getPluginManager()->isPluginActive('photo') && mb_stripos($text, self::KEY_PHOTO_UPLOAD) !== false )
        {
            $label = OW::getLanguage()->text('photo', 'upload_photos');
            $js = OW::getEventManager()->call('photo.getAddPhotoURL');
            $langLabel = $this->getLangLabel($photoKey, $text, self::KEY_PHOTO_UPLOAD);
            
            if ( $langLabel != NULL )
            {
                $label = $langLabel;
            }
            
            $text = preg_replace($photoKey, '<li><a href="javascript://" onclick="' . $js . '();">' . $label . '</a></li>', $text);
        }
        else
        {
            $text = preg_replace($photoKey, '', $text);
        }

        if ( mb_stripos($text, self::KEY_CHANGE_AVATAR) !== false )
        {
            $label = OW::getLanguage()->text('base', 'avatar_change');
            
            $js =  ' $("#welcomeWinget_loadAvatarChangeCmp").click(function(){'
                    . 'document.avatarFloatBox = OW.ajaxFloatBox("BASE_CMP_AvatarChange", [], {width: 749, title: ' . json_encode($label) . '});'
                    . '});';
            OW::getDocument()->addOnloadScript($js);
            
            $langLabel = $this->getLangLabel($avatarKey, $text, self::KEY_CHANGE_AVATAR);
            
            if ( $langLabel != NULL )
            {
                $label = $langLabel;
            }
            
            $text = preg_replace($avatarKey, '<li><a id="welcomeWinget_loadAvatarChangeCmp" href="javascript://">' . $label . '</a></li>', $text);
        }

        else
        {
            $text = preg_replace($avatarKey, '', $text);
        }
        
        $this->assign('text', $text);
    }
    
    private function getLangLabel( $pattern, $text, $key )
    {
        preg_match($pattern, $text, $matches);
            
        if ( !empty($matches) )
        {
            preg_match('/<a[^>]*' . $key . '[^>]*>(.+)<\/a[^>]*>/i', $matches[0], $langLabel);

            if ( isset($langLabel[1]) )
            {
                return $langLabel[1];
            }
        }
        
        return NULL;
    }

    public static function getSettingList()
    {
        $settingList = array();

        $settingList['text'] = array(
            'presentation' => self::PRESENTATION_CUSTOM,
            'render' => 'BASE_CMP_WelcomeWidget::renderTextField',
            'value' => 'base+welcome_widget_content'
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_ICON => self::ICON_INFO,
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'welcome_widget_title')
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }

    public static function processSettingList( $settingList, $place, $isAdmin )
    {
        BOL_LanguageService::getInstance()->addOrUpdateValue(OW::getLanguage()->getCurrentId(), 'base', 'welcome_widget_content', $settingList['text']);

        return $settingList;
    }

    public static function renderTextField( $widgetName, $name, $value )
    {
        $content = OW::getLanguage()->text('base', 'welcome_widget_content');
        $legend = nl2br(OW::getLanguage()->text('base', 'welcome_widget_legend'));

        return '<textarea name="' . $name . '">' . $content . '</textarea><br /><div>' . $legend . '</div>';
    }
}
