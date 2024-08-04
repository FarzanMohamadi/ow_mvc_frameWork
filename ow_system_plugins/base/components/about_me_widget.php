<?php
/**
 * About Me widget
 *
 * @package ow_system_plugins.base.components
 * @since 1.0
 */
class BASE_CMP_AboutMeWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $param )
    {
        parent::__construct();

        $userId = $param->additionalParamList['entityId'];

        if ( isset($param->customParamList['content']) )
        {
            $content = $param->customParamList['content'];
        }
        else
        {
            $settings = BOL_ComponentEntityService::getInstance()->findSettingList($param->widgetDetails->uniqName, $userId, array(
                'content'
            ));

            $content = empty($settings['content']) ? null : $settings['content'];
        }

        if ( $param->additionalParamList['entityId'] == OW::getUser()->getId() )
        {
            $this->assign('ownerMode', true);
            $this->assign('noContent', $content === null);

            $this->addForm(new AboutMeForm($param->widgetDetails->uniqName, $content));
        }
        else
        {
            if ( empty($content) )
            {
                $this->setVisible(false);

                return;
            }

            $this->assign('ownerMode', false);
        }

        $content = empty($content)?'':UTIL_HtmlTag::autoLink($content);
        $content = str_replace("\r\n", '<br />', $content);
        $this->assign('contentText', nl2br($content));
    }

    public function render()
    {
        return parent::render();
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['content'] = array(
            'presentation' => self::PRESENTATION_HIDDEN,
            'label' => '',
            'value' => null
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('base', 'about_me_widget_default_title'),
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_INFO,
            self::SETTING_FREEZE => true
        );
    }

    public static function processForm( $data )
    {
        $form = new AboutMeForm();
        return $form->process($data);
    }
}

class AboutMeForm extends Form
{
    private $widgetUniqName;

    public function __construct( $widgetUniqName = null, $content = null )
    {
        parent::__construct('about_me_form');

        $this->widgetUniqName = $widgetUniqName;

        $this->setAjax(true);
        $this->setAction(OW::getRouter()->urlFor('BASE_CTRL_ComponentPanel', 'ajaxSaveAboutMe'));

        $input = new Textarea('about_me');
        $input->addAttribute('style', 'width: 97%;');
        $input->setId('about_me_widget_input');
        $input->setHasInvitation(true);
        $input->setInvitation(OW::getLanguage()->text('base', 'about_me_widget_inv_text'));
        //$input->setRequired(true);
        $input->setValue($content);
        $this->addElement($input);

        $hidden = new HiddenField('widget_uniq_name');
        $hidden->setValue($widgetUniqName);

        $this->addElement($hidden);

        $submit = new Submit('save');

        //$submit->setLabel(OW::getLanguage()->text('base', 'widget_about_me_save_btn'));

        $this->addElement($submit);

        OW::getDocument()->addOnloadScript('
           window.owForms["about_me_form"].bind("success", function(data){
                OW.info(data.message);
           });
           window.owForms["about_me_form"].reset = false;
        ');
    }

    public function process( $data )
    {
        if ( !$this->isValid($data) )
        {
            return false;
        }

        $userId = OW::getUser()->getId();

        if ( !$userId )
        {
            return false;
        }

        $status = $data['about_me'];

        /**
         * replace unicode emoji characters
         */
        $replaceUnicodeEmoji= new OW_Event('frm.replace.unicode.emoji', array('text' => $status));
        OW::getEventManager()->trigger($replaceUnicodeEmoji);
        if(isset($replaceUnicodeEmoji->getData()['correctedText'])) {
            $status = $replaceUnicodeEmoji->getData()['correctedText'];
        }
        /**
         * remove remaining utf8 unicode emoji characters
         */
        $removeUnicodeEmoji= new OW_Event('frm.remove.unicode.emoji', array('text' => $status));
        OW::getEventManager()->trigger($removeUnicodeEmoji);
        if(isset($removeUnicodeEmoji->getData()['correctedText'])) {
            $status = $removeUnicodeEmoji->getData()['correctedText'];
        }

        $content = UTIL_HtmlTag::stripTagsAndJs($status);
//        $content = str_replace("\r\n", '<br />', $content);

        BOL_ComponentEntityService::getInstance()->saveComponentSettingList($data['widget_uniq_name'], $userId, array('content' => $content));
        BOL_ComponentEntityService::getInstance()->clearEntityCache(BOL_ComponentEntityService::PLACE_PROFILE, $userId);

        return array('message' => OW::getLanguage()->text('base', 'about_me_widget_content_saved'));
    }
}
