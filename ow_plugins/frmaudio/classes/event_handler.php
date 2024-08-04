<?php
/**
 * 
 * All rights reserved.
 */

/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio
 * @since 1.0
 */
class FRMAUDIO_CLASS_EventHandler
{
    private static $classInstance;

    /***
     * @return FRMAUDIO_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * FRMAUDIO_CLASS_EventHandler constructor.
     */
    private function __construct()
    {
    }

    /***
     *
     */
    public function init()
    {
        $service = FRMAUDIO_BOL_Service::getInstance();
        $eventManager = OW::getEventManager();
        $eventManager->bind(FRMEventManager::ON_FEED_ITEM_RENDERER, array($service, 'appendAudioPlayerToFeed'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_UPDATE_STATUS_FORM_RENDERER, array($service, 'addAudioInputFieldsToNewsfeed'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_FORUM_POST_RENDER, array($service,'AudioRenderInPostForum'));
        $eventManager->bind(FRMEventManager::ON_BEFORE_FORUM_POST_FORM_CREATE, array($service,'addAudioInputFieldsToForum'));
        $eventManager->bind('feed.on_entity_action', array($service,'saveInsertedAudio'));
        $eventManager->bind('forum.add_post', array($service,'saveInsertedAudio'));
        $eventManager->bind('frmaudio.audioForward', array($service,'forwardAudio'));
        $eventManager->bind('base.on.before.forward.status.create', array($service, 'onForward'));
        $eventManager->bind('on.status.update.check.data', array($service, 'onStatusUpdateCheckData'));
    }
}