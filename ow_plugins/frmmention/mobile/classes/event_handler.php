<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmmention
 * @since 1.0
 */
class FRMMENTION_MCLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FRMMENTION_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMMENTION_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function genericInit()
    {
        $service = FRMMENTION_BOL_Service::getInstance();
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));
        //OW::getEventManager()->bind( OW_EventManager::ON_AFTER_ROUTE, array($this, 'onBeforeDocumentRender') );
        //OW::getEventManager()->bind('plugin.privacy.get_action_list', array($service, 'privacyAddAction'));

        //new content added
        OW::getEventManager()->bind('feed.after_comment_add', array($service, 'onAddComment'));
        OW::getEventManager()->bind('feed.action', array($service, 'onEntityUpdate') , 1500);
        OW::getEventManager()->bind('feed.delete_item', array($service, 'onEntityUpdate'));
        OW::getEventManager()->bind('hashtag.on_entity_change', array($service,'onEntityUpdate'));
        OW::getEventManager()->bind('hashtag.edit_newsfeed', array($service, 'onEntityUpdate'));
        OW::getEventManager()->bind('base_delete_comment', array($service, 'onCommentDelete'));

        //rendering content
        OW::getEventManager()->bind('base.comment_item_process', array($service, 'renderComments')); //comments, images
//        OW::getEventManager()->bind(FRMEventManager::ON_FEED_ITEM_RENDERER, array($service,'renderNewsfeed') ); //newsfeed
//        OW::getEventManager()->bind(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array($service,'renderString')); //frmnews
        OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_RENDER_STRING, array($service,'renderString')); //groups, event, video, forum

        //rendering notifications
        OW::getEventManager()->bind('notifications.collect_actions', array($service, 'onNotifyActions'));
        OW::getEventManager()->bind('mobile.notifications.on_item_render', array($service, 'onNotificationRender'));
        OW::getEventManager()->bind(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array($service, "onRabbitMQNotificationRelease"));
    }

    public function onBeforeDocumentRender( OW_Event $event )
    {
        //  if (!startsWith(OW::getRouter()->getUri(), "forum/"))
//        {
            OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('frmmention')->getStaticCssUrl() . 'frmmention.css' );

            $js = ";var mentionLoadUsernamesUrl='". OW::getRouter()->urlForRoute('frmmention.load_usernames')."/';";

            $js = $js . FRMMENTION_BOL_Service::getInstance()->addGroupIdToJs();

            $js = $js.";var mentionMaxCount=". OW::getConfig()->getValue('frmmention', 'max_count').";";
            $friends = "var mention_friends = [{username: 'i.moradnejad', fullname: 'Issa Moradnejad'}];";
            $js = $js.";".$friends.";";
            OW::getDocument()->addScriptDeclarationBeforeIncludes($js);
            OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmmention')->getStaticJsUrl() . 'suggest.js' );
            OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmmention')->getStaticJsUrl() . 'frmmention-mobile.js' );
//        }
    }
}