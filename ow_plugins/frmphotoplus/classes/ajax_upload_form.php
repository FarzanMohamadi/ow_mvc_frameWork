<?php
/**
 * 
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.photo.classes
 * @since 1.6.1
 */
class FRMPHOTOPLUS_CLASS_AjaxUploadForm extends PHOTO_CLASS_AbstractPhotoForm
{
    const FORM_NAME = 'ajax-upload';
    const ELEMENT_ALBUM = 'album';
    const ELEMENT_ALBUM_NAME = 'album-name';
    const ELEMENT_DESCRIPTION = 'description';

    public function __construct( $entityType, $entityId, $albumId = null, $albumName = null, $albumDescription = null, $url = null, $data = null)
    {
        parent::__construct(self::FORM_NAME);
        
        $this->setAjax(true);
        $this->setAjaxResetOnSuccess(false);
        $this->setAction(OW::getRouter()->urlForRoute('frmphotoplus.ajax_upload_submit'));
        $this->bindJsFunction(self::BIND_SUCCESS, 'function(data){formUploadSuccess(data);}');
        
        $language = OW::getLanguage();

        $albumField = new TextField(self::ELEMENT_ALBUM);
        $albumField->setRequired();
        $albumField->addAttribute(FormElement::ATTR_CLASS, 'ow_dropdown_btn ow_inputready ow_cursor_pointer');
        $albumField->addAttribute('autocomplete', 'off');
        $albumField->addAttribute(FormElement::ATTR_READONLY);
        
        $albumNameField = new TextField(self::ELEMENT_ALBUM_NAME);
        $albumNameField->setRequired();
        $albumNameField->addValidator(new PHOTO_CLASS_AlbumNameValidator(false));
        $albumNameField->addAttribute('placeholder', $language->text('photo', 'album_name'));
        $this->addElement($albumNameField);

        $desc = new Textarea(self::ELEMENT_DESCRIPTION);
        $desc->addAttribute('placeholder', $language->text('photo', 'album_desc'));
        $desc->setValue(!empty($albumDescription) ? $albumDescription : null);
        $this->addElement($desc);

        $userId = OW::getUser()->getId();
        $albumService = PHOTO_BOL_PhotoAlbumService::getInstance();

        if ( !empty($albumId) && ($album = $albumService->findAlbumById($albumId)) !== null && $album->userId == $userId && !$albumService->isNewsfeedAlbum($album) )
        {
            $albumField->setValue($album->name);
            $albumNameField->setValue($album->name);
        }
        elseif ( !empty($albumName) )
        {
            $albumField->setValue($albumName);
            $albumNameField->setValue($albumName);
        }
        else
        {
            $event = OW::getEventManager()->trigger(new BASE_CLASS_EventCollector(PHOTO_CLASS_EventHandler::EVENT_SUGGEST_DEFAULT_ALBUM, array(
                'userId' => $userId,
                'entityType' => $entityType,
                'entityId' => $entityId
            )));
            $eventData = $event->getData();

            if ( !empty($eventData) )
            {
                $value = array_shift($eventData);
                $albumField->setValue($value);
                $albumNameField->setValue($value);
            }
            else
            {
                $albumField->setValue($language->text('photo', 'choose_existing_or_create'));
            }
        }

        $this->addElement($albumField);

        $submit = new Submit('submit');
        $submit->addAttribute('class', 'ow_ic_submit ow_positive');
        $this->addElement($submit);
    }

    public function getOwnElements()
    {
        return array(
            self::ELEMENT_ALBUM,
            self::ELEMENT_ALBUM_NAME,
            self::ELEMENT_DESCRIPTION,
            'statusPrivacy'
        );
    }
}
