<?php
/**
 * 
 * All rights reserved.
 */

/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmvideoplus.bol
 * @since 1.0
 */
class FRMWORDSCORRECTION_CLASS_EventHandler
{
    private static $classInstance;

    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    }

    public function init()
    {
        $eventManager = OW::getEventManager();
        $eventManager->bind('frmwordscorrection.correct_words', array($this, 'correctWords'));
    }

    public function correctWords(OW_Event $event)
    {
        $params = $event->getParams();
        if (!isset($params['words']))
            return;
        $words = $params['words'];
        $correctedWords = array();
        foreach ($words as $key => $word) {
            $correctedWord = FRMWORDSCORRECTION_BOL_Service::getInstance()->correctWordString($word);
            if (isset($correctedWord))
                $correctedWords[$key] = $correctedWord;
            else
                $correctedWords[$key] = $word;
        }
        $event->setData($correctedWords);
    }

}