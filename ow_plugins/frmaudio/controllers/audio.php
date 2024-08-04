<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmaudio
 * @since 1.0
 */
class FRMAUDIO_CTRL_Audio extends OW_ActionController
{
    /***
     * @param $params
     * @throws Redirect404Exception
     */
    public function addAudio($params)
    {
        $form = FRMAUDIO_BOL_Service::getInstance()->getAddAudioForm();
        $this->addForm($form);

        if (OW::getRequest()->isAjax()) {
            if ($form->isValid($_POST) && OW::getUser()->isAuthenticated()) {
                $values = $form->getValues();
                $audio = FRMAUDIO_BOL_Service::getInstance()->findAudioById($values["audioId"]);
                if($audio==null){
                    exit(json_encode(array('result ' => false)));
                }
                exit(json_encode(array('result' => true, 'audioId' => $audio->id , 'audioData' => FRMAUDIO_BOL_Service::getInstance()->getAudioFileUrl($audio->hash), 'name' => $values["name"])));
            }
            exit(json_encode(array('result ' => false)));
        }
        throw new Redirect404Exception();
    }

    /***
     * @param $params
     * @throws Redirect404Exception
     */
    public function viewList($params)
    {
        $this->setPageTitle(OW::getLanguage()->text('frmaudio', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('frmaudio', 'description_audio_page'));

        $page = (!empty($_GET['page']) && intval($_GET['page']) > 0) ? $_GET['page'] : 1;
        $count = 5;
        $first = ($page - 1) * $count;


        $this->addForm(FRMAUDIO_BOL_Service::getInstance()->getAddAudioForm());
        if (OW::getUser()->isAuthenticated()) {
            $service = FRMAUDIO_BOL_Service::getInstance();
            $allAudiosListOfUser = FRMAUDIO_BOL_Service::getInstance()->findAudiosByUserId(OW::getUser()->getId());
            $sizeOfAllAudiosOfUser = 0;
            if ($allAudiosListOfUser != null) {
                $sizeOfAllAudiosOfUser = sizeof($allAudiosListOfUser);
            }
            $list = FRMAUDIO_BOL_Service::getInstance()->findListOrderedByDate(OW::getUser()->getId(), $first, $count);
            $tplList = array();
            foreach ($list as $listItem) {
                $audioForumUrl = null;
                $autherUserName = BOL_UserService::getInstance()->findUserById($listItem->userId)->getUsername();
                $audioType = '';
                if ($listItem->object_type == 'newsfeed') {
                    $audioType = OW::getLanguage()->text('frmaudio', 'newsfeed_audio_located');
                } else if ($listItem->object_type == 'forum-post') {
                    $audioType = OW::getLanguage()->text('frmaudio', 'forum_audio_located');
                    $postDto = FORUM_BOL_ForumService::getInstance()->findPostById($listItem->object_id);
                    if ($postDto != null) {
                        $audioForumUrl = FORUM_BOL_ForumService::getInstance()->getPostUrl($postDto->topicId, $listItem->object_id);
                    }
                }
                $tplList[] = array(
                    "title" => $listItem->title,
                    "autherName" => $autherUserName,
                    "autherUrl" => OW::getRouter()->urlForRoute('base_user_profile', array('username' => $autherUserName)),
                    "autherAvatar" => BOL_AvatarService::getInstance()->getDataForUserAvatars(array($listItem->userId))[$listItem->userId],
                    "addDateTime" => UTIL_DateTime::formatDate($listItem->addDateTime),
                    "audioUrl" => $service->getAudioFileUrl($listItem->hash),
                    "audioType" => $audioType,
                    "audioForumUrl" => $audioForumUrl,
                    'deleteUrl' => "if(confirm('" . OW::getLanguage()->text('frmaudio', 'delete_item_warning') . "')){location.href='" . OW::getRouter()->urlForRoute('frmaudio-audio-delete-item', array('id' => $listItem->getId())) . "';}"
                );
            }
            $this->assign("list", $tplList);
            if(sizeof($tplList)==0){
                $this->assign('isListItemEmpty', true);
            }

            $paging = new BASE_CMP_Paging($page, ceil($sizeOfAllAudiosOfUser / $count), 5);
            $this->addComponent('paging', $paging);
        } else {
            throw new Redirect404Exception();
        }
    }

    public function saveTempItem(){
        if(isset($_POST['data'])) {
            $data = $_POST['data'];
            $audio = FRMAUDIO_BOL_Service::getInstance()->saveTempAudio($data);
            if($audio!=null){
                exit(json_encode(array(
                    'result' => true,
                    'id' => $audio->id,
                    'url' => FRMAUDIO_BOL_Service::getInstance()->getAudioFileUrl($audio->hash))
                ));
            }
        }
        exit(json_encode(array('result' => false)));
    }
    public function saveTempBlobItem(){
        if(isset($_FILES['file']) and !$_FILES['file']['error']){
            $audio = FRMAUDIO_BOL_Service::getInstance()->saveTempBlob($_FILES['file']);
            if($audio!=null){
                exit(json_encode(array(
                        'result' => true,
                        'id' => $audio->id,
                        'url' => FRMAUDIO_BOL_Service::getInstance()->getAudioFileUrl($audio->hash))
                ));
            }
        }
        exit(json_encode(array('result' => false)));
    }

    /***
     * @param $params
     * @throws Redirect404Exception
     */
    public function deleteItem($params)
    {
        if (!isset($params['id']) && !OW::getUser()->isAuthenticated()) {
            throw new Redirect404Exception();
        } else {
            $service = FRMAUDIO_BOL_Service::getInstance();
            $audio = $service->findAudioById($params['id']);
            if (OW::getUser()->getId() != $audio->userId) {
                throw new Redirect404Exception();
            } else {
                $service->deleteDatabaseRecord($params['id']);
                OW::getFeedback()->info(OW::getLanguage()->text('frmaudio', 'database_record_deleted'));
            }
        }
        $this->redirect(OW::getRouter()->urlForRoute('frmaudio-audio'));
    }


}