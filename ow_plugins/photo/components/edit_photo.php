<?php
/**
 * Edit photo component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.photo.components
 * @since 1.3.2
 */
class PHOTO_CMP_EditPhoto extends OW_Component
{
    public function __construct( $photoId )
    {
        parent::__construct();

        if ( ($photo = PHOTO_BOL_PhotoDao::getInstance()->findById($photoId)) === NULL ||
            ($album = PHOTO_BOL_PhotoAlbumDao::getInstance()->findById($photo->albumId)) === null ||
            !($album->userId == OW::getUser()->getId() || OW::getUser()->isAuthorized('photo')) )
        {
            $this->setVisible(FALSE);
            
            return;
        }
        
        $this->addForm(new PHOTO_CLASS_EditForm($photo->id));
        
        OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_UPLOAD_FORM_RENDERER,array('this' => $this,'form' => $this->getForm('photo-edit-form'))));
        $newsfeedAlbum = PHOTO_BOL_PhotoAlbumService::getInstance()->getNewsfeedAlbum($album->userId);
        $exclude = array();
        
        if ( !empty($newsfeedAlbum) )
        {
            $exclude[] = $newsfeedAlbum->id;
        }

        $this->addComponent('albumNameList', OW::getClassInstance('PHOTO_CMP_AlbumNameList', $album->userId, $exclude));
        $language = OW::getLanguage();
        
        OW::getDocument()->addOnloadScript(
            UTIL_JsGenerator::composeJsString(';var panel = $(document.getElementById("photo_edit_form"));
                var albumList = $(".ow_dropdown_list", panel);
                var albumInput = $("input[name=\'album\']", panel);
                var album = {$album};
                var hideAlbumList = function()
                {
                    albumList.hide();
                    $(".upload_photo_spinner", panel).removeClass("ow_dropdown_arrow_up").addClass("ow_dropdown_arrow_down");
                };
                var showAlbumList = function()
                {
                    albumList.show();
                    $(".upload_photo_spinner", panel).removeClass("ow_dropdown_arrow_down").addClass("ow_dropdown_arrow_up");
                };

                $(".upload_photo_spinner", panel).add(albumInput).on("click", function( event )
                {
                    if ( albumList.is(":visible") )
                    {
                        hideAlbumList();
                    }
                    else
                    {
                        showAlbumList();
                    }

                    event.stopPropagation();
                });

                albumList.find("li").on("click", function()
                {
                    hideAlbumList();
                    owForms["photo-edit-form"].removeErrors();
                }).eq(0).on("click", function()
                {
                    albumInput.val({$create_album});
                    $(".new-album", panel).show();
                    $("input[name=\'album-name\']", panel).val({$album_name});
                    //$("textarea", panel).val({$album_desc});
                }).end().slice(2).on("click", function()
                {
                    albumInput.val($(this).data("name"));
                    $(".new-album", panel).hide();
                    $("input[name=\'album-name\']", panel).val(albumInput.val());
                    //$("textarea", panel).val("");
                });

                $(document).on("click", function( event )
                {
                    if ( event.target.id === "ajax-upload-album" )
                    {
                        event.stopPropagation();

                        return false;
                    }

                    hideAlbumList();
                });
                
                OW.bind("base.onFormReady.photo-edit-form", function()
                {
                    if ( album.name == {$newsfeedAlbumName} )
                    {
                        this.getElement("album-name").validators.length = 0;
                        this.getElement("album-name").addValidator({
                            validate : function( value ){
                            if(  $.isArray(value) ){ if(value.length == 0  ) throw {$required}; return;}
                            else if( !value || $.trim(value).length == 0 ){ throw {$required}; }
                            },
                            getErrorMessage : function(){ return {$required} }
                        });
                        this.bind("submit", function()
                        {
                            
                        });
                    }
                });
                '
            ,
            array(
                'create_album' => $language->text('photo', 'create_album'),
                'album_name' => $language->text('photo', 'album_name'),
                'album_desc' => $language->text('photo', 'album_desc'),
                'album' => get_object_vars($album),
                'newsfeedAlbumName' => OW::getLanguage()->text('photo', 'newsfeed_album'),
                'required' => OW::getLanguage()->text('base', 'form_validator_required_error_message')
            ))
        );
    }
}
