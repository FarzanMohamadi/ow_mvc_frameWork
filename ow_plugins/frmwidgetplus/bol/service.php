<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmwidgetplus
 * @since 1.0
 */
final class FRMWIDGETPLUS_BOL_Service
{
    private function __construct()
    {
    }

    /***
     * @var
     */
    private static $classInstance;

    /***
     * @return FRMWIDGETPLUS_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function beforeNewsListViewRender(OW_EVENT $event){
        $param = $event->getParams();
        if(isset($param['newsView'])){
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticJsUrl().'frmwidgetplus.js');
            $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));

            if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
                $imgInfoSrc = OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticUrl() . 'img/info.svg';
                OW::getDocument()->addOnloadScript('addChangeVisibilityOfNewsWidgets("' . $imgInfoSrc . '")');
            }
        }
    }

    public function beforeGroupListViewRender(OW_EVENT $event){
        $param = $event->getParams();
        if(isset($param['GroupListView'])){
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticJsUrl().'frmwidgetplus.js');

            $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
            if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
                $frmgroupplus = FRMSecurityProvider::checkPluginActive('frmgroupsplus', true);
                $imgInfoSrc = OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticUrl() . 'img/info.svg';
                OW::getDocument()->addOnloadScript('addChangeVisibilityOfGroupListWidgets("' . $imgInfoSrc . '","'.$frmgroupplus.'")');
            }
        }
    }

    public function beforeGroupViewRender(OW_EVENT $event){
        $param = $event->getParams();
        if (isset($param['groupView'])) {
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticJsUrl() . 'frmwidgetplus.js');

            $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION, array('check' => true)));
            if (isset($mobileEvent->getData()['isMobileVersion']) && $mobileEvent->getData()['isMobileVersion'] == true) {
                $imgInfoSrc = OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticUrl() . 'img/info.svg';
                OW::getDocument()->addOnloadScript('addChangeVisibilityOfGroupWidgets("' . $imgInfoSrc . '")');

                $imgInviteSrc = OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticUrl() . 'img/invite.svg';
                OW::getDocument()->addStyleDeclaration("input#GROUPS_InviteLink {background-image: url('" . $imgInviteSrc . "');}");

                $imgForumSrc = OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticUrl() . 'img/forum.svg';
                OW::getDocument()->addStyleDeclaration("input#GROUPS_ForumLink {background-image: url('" . $imgForumSrc . "');}");
            }
        }
    }
    public function addWidgetJS()
    {
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticJsUrl() . 'highlight.pack.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticJsUrl() . 'jquery.cookie.js');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticJsUrl() . 'jquery.collapsible.js');
        if(OW::getConfig()->getValue('frmwidgetplus', 'add_select2')==1) {
            OW::getLanguage()->addKeyForJs('frmwidgetplus', 'no_result');
            OW::getLanguage()->addKeyForJs('frmwidgetplus', 'error_loading');
            OW::getLanguage()->addKeyForJs('frmwidgetplus', 'input_too_short');
            OW::getLanguage()->addKeyForJs('frmwidgetplus', 'input_too_long');
            OW::getLanguage()->addKeyForJs('frmwidgetplus', 'searching');
            OW::getLanguage()->addKeyForJs('frmwidgetplus', 'loading_more');
            OW::getLanguage()->addKeyForJs('frmwidgetplus', 'maximum_selected');
            OW::getLanguage()->addKeyForJs('frmwidgetplus', 'remove_all_items');
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticJsUrl() . 'select2.js');
            OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticCssUrl() . 'select2.css');
            OW::getDocument()->addOnloadScript("
            $('select').each(function (index, value) {
                if($(this).find('option').length >= 5 && $(this).attr('name') != 'day_birthdate'){
                    $(this).select2( {
                        placeholder: '".OW::getLanguage()->text('frmwidgetplus', 'select')."',
                        allowClear: true
                    });
                }
            });
            ");
        }
        OW::getDocument()->addOnloadScript("
        $.fn.slideFadeToggle = function(speed, easing, callback) {
            return this.animate({opacity: 'toggle', height: 'toggle'}, speed, easing, callback);
        };

        $('.page_collapsible').collapsible({
            defaultOpen: 'body_open',
            cookieName: 'body2',
            speed: 'slow',
            animateOpen: function (elem, opts) { 
                elem.next().slideFadeToggle(opts.speed);
            },
            animateClose: function (elem, opts) { 
                elem.next().slideFadeToggle(opts.speed);
            },
            loadOpen: function (elem) { 
                elem.next().show();
            },
            loadClose: function (elem, opts) {
                elem.next().hide();
            }

        });
        ");

        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticCssUrl() . 'frmwidgetplus.css');
        OW::getDocument()->addOnloadScript("$('.owm_group_view .owm_brief_info .owm_box span.page_collapsible').click();");
        OW::getDocument()->addOnloadScript("$('.owm_group_view .owm_view_file_list .owm_box span.page_collapsible').click();");
        OW::getDocument()->addOnloadScript("$('.owm_group_view .owm_view_subgroups_list .owm_box span.page_collapsible').click();");
        OW::getDocument()->addOnloadScript("$('.owm_group_view .owm_view_user_list .owm_box span.page_collapsible').click();");
        OW::getDocument()->addOnloadScript("$('.owm_frmnews_page .owm_frmnews_widgets .owm_box span.page_collapsible').click();");
        OW::getDocument()->addOnloadScript("$('.owm_group_view .owm_frmreport_widget  .owm_box span.page_collapsible').click();");
        OW::getDocument()->addOnloadScript("$('.owm_group_view .owm_pending_users_list_cmp  .owm_box span.page_collapsible').click();");

        OW::getDocument()->addOnloadScript("
                $('.owm_group_view .owm_brief_info .owm_box .owm_box_cap').click(function(){\$('.owm_group_view .owm_brief_info .owm_box span.page_collapsible').click()});
                 ");
        OW::getDocument()->addOnloadScript("
                $('.owm_group_view .owm_view_file_list .owm_box .owm_box_cap').click(function(){\$('.owm_group_view .owm_view_file_list .owm_box span.page_collapsible').click()});
                 ");
        OW::getDocument()->addOnloadScript("
                $('.owm_group_view .owm_view_subgroups_list .owm_box .owm_box_cap').click(function(){\$('.owm_group_view .owm_view_subgroups_list .owm_box span.page_collapsible').click()});
                 ");
        OW::getDocument()->addOnloadScript("
                $('.owm_group_view .owm_view_user_list .owm_box .owm_box_cap').click(function(){\$('.owm_group_view .owm_view_user_list .owm_box span.page_collapsible').click()});
                 ");

        OW::getDocument()->addOnloadScript("
                $('.owm_group_view .owm_frmreport_widget  .owm_box .owm_box_cap').click(function(){\$('.owm_group_view .owm_frmreport_widget  .owm_box span.page_collapsible').click()});
        ");
        OW::getDocument()->addOnloadScript("
                $('.owm_group_view .owm_pending_users_list_cmp .owm_box .owm_box_cap').click(function(){\$('.owm_group_view .owm_pending_users_list_cmp .owm_box span.page_collapsible').click()});
                 ");

        $mobileEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::IS_MOBILE_VERSION,array('check' => true)));
        if(isset($mobileEvent->getData()['isMobileVersion'])&& $mobileEvent->getData()['isMobileVersion']==true) {
            $js = '
            var enable_send_btn = function(text, btn_sel){
                if(text == ""){
                    $(btn_sel).removeClass("owm_send_btn_available");
                }else{
                    $(btn_sel).addClass("owm_send_btn_available");
                }
            }
            $(\'#mailboxConversationFooter #newMessageText\').on(\'input\', function(){
                enable_send_btn(this.value, "#mailboxConversationFooter #newMessageSendBtn");
            });
            $(\'*[name=commentText]\').on(\'input\', function(){
                enable_send_btn(this.value, ".owm_newsfeed_comments input[name=comment-submit]");
            });
            ';
            OW::getDocument()->addScriptDeclaration( $js );

            $defaultImg = OW::getPluginManager()->getPlugin('frmwidgetplus')->getStaticUrl() . 'img/default.svg';
            OW::getDocument()->addOnloadScript("$('#newMessageText').parent().prepend($('#newMessageSendBtn'));");
            OW::getDocument()->addOnloadScript("$('#newMessageText').parent().prepend($('#mailbox_att_btn_c'));");
            OW::getDocument()->addOnloadScript("$(\".owm_mail_add_name img\").attr(\"onerror\",\"this.src='".$defaultImg."'\");");
        }
    }
}
