<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmhashtag
 * @since 1.0
 */
class FRMHASHTAG_MCLASS_EventHandler
{
    /**
     * Singleton instance.
     *
     * @var FRMHASHTAG_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMHASHTAG_MCLASS_EventHandler
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
        $service = FRMHASHTAG_BOL_Service::getInstance();
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, 'onBeforeDocumentRender'));

        //new content added
        OW::getEventManager()->bind('feed.after_comment_add', array($service, 'onAddComment'));
        OW::getEventManager()->bind('feed.action', array($service, 'onEntityUpdate'),1500);
        OW::getEventManager()->bind('feed.delete_item', array($service, 'onEntityUpdate'));
        OW::getEventManager()->bind('hashtag.on_entity_change', array($service,'onEntityUpdate'));
        OW::getEventManager()->bind('hashtag.edit_newsfeed', array($service, 'onEntityUpdate'));
        OW::getEventManager()->bind('base_delete_comment', array($service, 'onCommentDelete'));
//        OW::getEventManager()->bind('feed.hashtag', array($service, 'feedHashtag'));

        //rendering content
        OW::getEventManager()->bind('base.comment_item_process', array($service, 'renderComments')); //comments, images
//        OW::getEventManager()->bind(FRMEventManager::ON_FEED_ITEM_RENDERER, array($service,'renderNewsfeed') ); //newsfeed
//        OW::getEventManager()->bind(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ, array($service,'renderString')); //frmnews
        OW::getEventManager()->bind(FRMEventManager::ON_BEFORE_RENDER_STRING, array($service,'renderString')); //groups, event, video, forum, frmnews
        OW::getEventManager()->bind(FRMEventManager::ON_AFTER_RABITMQ_QUEUE_RELEASE, array($service, "onRabbitMQNotificationRelease"));

    }

    public function onBeforeDocumentRender( OW_Event $event )
    {
        //  if (!startsWith(OW::getRouter()->getUri(), "forum/"))
        {
            OW::getDocument()->addStyleSheet( OW::getPluginManager()->getPlugin('frmhashtag')->getStaticCssUrl() . 'frmhashtag.css' );

            $js = ";var frmhashtagLoadTagsUrl='". OW::getRouter()->urlForRoute('frmhashtag.load_tags')."/';";
            $js = $js.";var frmhashtagMaxCount=". OW::getConfig()->getValue('frmhashtag', 'max_count').";";
            $friends = "var frmhashtag_friends = [{tag: 'i.moradnejad', count: '5'}];";
            $js = $js.";".$friends.";";
            OW::getDocument()->addScriptDeclarationBeforeIncludes($js);
            OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmhashtag')->getStaticJsUrl() . 'suggest.js' );
            OW::getDocument()->addScript( OW::getPluginManager()->getPlugin('frmhashtag')->getStaticJsUrl() . 'frmhashtag-mobile.js' );
        }
    }
}