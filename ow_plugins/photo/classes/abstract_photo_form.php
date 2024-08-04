<?php
/**
 *
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.classes
 * @since 1.7.6
 */
abstract class PHOTO_CLASS_AbstractPhotoForm extends Form
{
    /**
     * @return array
     */
    abstract public function getOwnElements();

    public function getExtendedElements()
    {
        $arrayDiff = array_diff(
            array_keys($this->getElements()),
            array_merge(array('form_name'), $this->getOwnElements())
        );
        if (($key = array_search(FORM::ELEMENT_CSRF_TOKEN, $arrayDiff)) !== false || ($key = array_search('csrf_hash', $arrayDiff)) !== false) {
            unset($arrayDiff[$key]);
        }
        if (($key = array_search('csrf_hash', $arrayDiff)) !== false) {
            unset($arrayDiff[$key]);
        }
        return $arrayDiff;
    }

    public function triggerReady( array $data = null )
    {
        OW::getEventManager()->trigger(
            new OW_Event(PHOTO_CLASS_EventHandler::EVENT_ON_FORM_READY, array('form' => $this), $data)
        );
    }

    public function triggerComplete( array $data = null )
    {
        OW::getEventManager()->trigger(
            new OW_Event(PHOTO_CLASS_EventHandler::EVENT_ON_FORM_COMPLETE, array('form' => $this), $data)
        );
    }
}