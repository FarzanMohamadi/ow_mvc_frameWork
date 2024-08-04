<?php
/**
 * Avatar action controller
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.ow_system_plugins.base.controllers
 * @since 1.0
 */
class BASE_CTRL_Avatar extends OW_ActionController
{
    use BASE_CLASS_UploadTmpAvatarTrait;

    /**
     * @var BOL_AvatarService
     */
    private $avatarService;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->avatarService = BOL_AvatarService::getInstance();
    }

    /**
     * Method acts as ajax responder. Calls methods using ajax
     *
     * @return string
     */
    public function ajaxResponder()
    {
        $request = $_POST;

        if ( isset($request['ajaxFunc']) && OW::getRequest()->isAjax() )
        {
            $callFunc = (string) $request['ajaxFunc'];

            $result = call_user_func(array($this, $callFunc), $request);
        }
        else
        {
            exit();
        }

        exit(json_encode($result));
    }

    public function ajaxUploadImage( $params )
    {
        $displayPhotoUpload = OW::getConfig()->getValue('base', 'join_display_photo_upload');
        if ( !OW::getUser()->isAuthenticated() && $displayPhotoUpload == BOL_UserService::CONFIG_JOIN_NOT_DISPLAY_PHOTO_UPLOAD)
        {
            return array('result' => false, 'error' => OW::getLanguage()->text('base', 'user_is_not_authenticated'));
        }
        if ( isset($_FILES['file']) )
        {
            return $this->uploadTmpAvatar($_FILES['file']);
        }

        return array('result' => false);
    }

    public function ajaxDeleteImage( $params )
    {
        $displayPhotoUpload = OW::getConfig()->getValue('base', 'join_display_photo_upload');
        if ( !OW::getUser()->isAuthenticated() && $displayPhotoUpload == BOL_UserService::CONFIG_JOIN_NOT_DISPLAY_PHOTO_UPLOAD)
        {
            return array('result' => false, 'error' => OW::getLanguage()->text('base', 'user_is_not_authenticated'));
        }
        $avatarService = BOL_AvatarService::getInstance();

        $key = $avatarService->getAvatarChangeSessionKey();
        $avatarService->deleteUserTempAvatar($key);

        return array('result' => true);
    }

    public function ajaxLoadMore( $params )
    {
        if ( isset($params['entityType']) && isset($params['entityId']) && isset($params['offset']) )
        {
            $entityType = $params['entityType'];
            $entityId = $params['entityId'];
            $offset = $params['offset'];

            $section = BOL_AvatarService::getInstance()->getAvatarChangeSection($entityType, $entityId, $offset);

            if ( $section )
            {
                $cmp = new BASE_CMP_AvatarLibrarySection($section['list'], $offset, $section['count']);
                $markup = $cmp->render();

                return array('result' => true, 'markup' => $markup, 'count' => $section['count']);
            }
        }

        return array('result' => false);
    }

    public function ajaxCropPhoto( $params )
    {
        if ( !isset($params['coords']) || !isset($params['view_size']) )
        {
            return array('result' => false, 'case' => 0);
        }

        $changeUserAvatar = isset($params['changeUserAvatar']) && (int) !$params['changeUserAvatar'] ? false : true;
        $coords = $params['coords'];
        $viewSize = $params['view_size'];
        $path = null;

        $localFile = false;

        $avatarService = BOL_AvatarService::getInstance();

        if ( !empty($params['entityType']) && !empty($params['id']) )
        {
            $item = $avatarService->getAvatarChangeGalleryItem($params['entityType'], $params['entityId'], $params['id']);
            
            if ( !$item || empty($item['path']) || !OW::getStorage()->fileExists($item['path']) )
            {
                return array('result' => false, 'case' => 1);
            }

            $path = $item['path'];
        }
        else if ( isset($params['url']) ) 
        {
            $path = UTIL_Url::getLocalPath($params['url']);
            
            if ( !OW::getStorage()->fileExists($path)  )
            {
                if ( !OW::getStorage()->fileExists($path) )
                {
                    return array('result' => false, 'case' => 2);
                }
                
                $localFile = true;
            }
        }

        $userId = !empty($params['userId']) ? $params['userId'] : OW_Auth::getInstance()->getUserId();

        if ( $userId && $changeUserAvatar)
        {
            $avatar = $avatarService->findByUserId($userId);

            try
            {
                if ( !$avatarService->cropAvatar($userId, $path, $coords, $viewSize, array('isLocalFile' => $localFile )) )
                {
                    return array(
                        'result' => false,
                        'case' => 6
                    );
                }

                $avatar = $avatarService->findByUserId($userId, false);

                return array(
                    'result' => true,
                    'modearationStatus' => $avatar->status,
                    'url' => $avatarService->getAvatarUrl($userId, 1, null, false, false),
                    'bigUrl' => $avatarService->getAvatarUrl($userId, 2, null, false, false)
                );
            }
            catch ( Exception $e )
            {
                return array('result' => false, 'case' => 4);
            }
        }
        else
        {
            $key = $avatarService->getAvatarChangeSessionKey();
            $path = $avatarService->getTempAvatarPath($key, 3);
            
            if ( !OW::getStorage()->fileExists($path) )
            {
                return array('result' => false, 'case' => 5);
            }
            
            $avatarService->cropTempAvatar($key, $coords, $viewSize);

            return array(
                'result' => true,
                'url' => $avatarService->getTempAvatarUrl($key, 1),
                'bigUrl' => $avatarService->getTempAvatarUrl($key, 2)
            );
        }
    }

    public function ajaxAvatarApprove( $params )
    {
        if ( isset($params['avatarId']) && OW::getUser()->isAuthorized('base') )
        {
            $entityId = $params['avatarId'];
            $entityType = BASE_CLASS_ContentProvider::ENTITY_TYPE_AVATAR;

            $event = new OW_Event("moderation.approve", array(
                "entityType" => $entityType,
                "entityId" => $entityId
            ));

            OW::getEventManager()->trigger($event);

            $data = $event->getData();
            
            if ( empty($data) )
            {
                return array('result' => true);
            }
            
            if ( !empty($data["message"]) )
            {
                return array('result' => true, 'message' => $data["message"]);
            }
            else
            {
                return array('result' => false, 'error' => $data["error"]);
            }
        }

        return array('result' => false);
    }
    public function ajaxRemoveAvatar($params)
    {
        $userId = !empty($params['userId']) ? $params['userId'] : OW::getUser()->getId();
        if (OW::getUser()->isAuthenticated())
        {
            $valid = BOL_AvatarService::getInstance()->deleteUserAvatar($userId);
            return array('result' => $valid);
        }
        return array('result' => false, 'message' => 'authorization_error');
    }
}
