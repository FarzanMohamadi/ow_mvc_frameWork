<?php
/**
 * Video list component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.components
 * @since 1.0
 */
class VIDEO_CMP_VideoList extends OW_Component
{
    /**
     * @var VIDEO_BOL_ClipService 
     */
    private $clipService;

    /**
     * Class constructor
     *
     * @param array $params
     */
    public function __construct( array $params )
    {
        parent::__construct();

        OW::getDocument()->addStyleSheet(OW_PluginManager::getInstance()->getPlugin("video")->getStaticCssUrl() . 'video.css');
        $listType = isset($params['type']) ? $params['type'] : '';
        $count = isset($params['count']) ? $params['count'] : 4;
        $tag = isset($params['tag']) ? $params['tag'] : '';
        $userId = isset($params['userId']) ? $params['userId'] : null;

        $this->clipService = VIDEO_BOL_ClipService::getInstance();

        $page = !empty($_GET['page']) && (int) $_GET['page'] ? abs((int) $_GET['page']) : 1;

        $clipsPerPage = $this->clipService->getClipPerPageConfig();

        if ( $userId )
        {
            $clips = $this->clipService->findUserClipsList($userId, $page, $clipsPerPage);
            $records = $this->clipService->findUserClipsCount($userId);
        }
        else if ( strlen($tag) )
        {
            $clips = $this->clipService->findTaggedClipsList($tag, $page, $clipsPerPage);
            $records = $this->clipService->findTaggedClipsCount($tag);
        }
        else
        {
            $clips = $this->clipService->findClipsList($listType, $page, $clipsPerPage);
            $records = $this->clipService->findClipsCount($listType);
        }

        $this->assign('listType', $listType);
        if ( $clips )
        {
            $this->assign('no_content', null);
            $this->assign('clips', $clips);

            $userIds = array();
            $clipIds = array();
            foreach ( $clips as $clip ) {
                $clipIds[] = $clip['id'];
                if (isset($clip["thumbUrl"]) && $clip["thumbUrl"] != null) {
                    $temp = explode(".", $clip["thumbUrl"]);
                    $clip["thumb"] = dirname($clip["thumb"]) . DS. $temp[0] . "_small." . $temp[1];
                }
            }

            $cache = array();
            $cachedClipObjects = array();
            if (sizeof($clipIds) > 0) {
                $clipObjects = VIDEO_BOL_ClipDao::getInstance()->findByIdList($clipIds);
                foreach ($clipObjects as $clipObject) {
                    $cachedClipObjects[$clipObject->id] = $clipObject;
                }
            }
            $cache['cache']['clips'] = $cachedClipObjects;
            $cache['cache']['secure_files'] = $this->clipService->prepareCacheFiles($cachedClipObjects);
            $cache['cache']['video_rates'] = BOL_RateDao::getInstance()->findEntitiesItemRateInfo($clipIds, 'video_rates');

            foreach ( $clips as $clip )
            {
                if ( !in_array($clip['userId'], $userIds) )
                    array_push($userIds, $clip['userId']);

                //set JSON-LD
                $clipObj = null;
                if (isset($cachedClipObjects[$clip['id']])) {
                    $clipObj = $cachedClipObjects[$clip['id']];
                }
                if ($clipObj == null) {
                    $clipObj = $this->clipService->findClipById($clip['id']);
                }
                $this->clipService->addJSONLD($clipObj, $cache);
            }

            $names = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
            $this->assign('displayNames', $names);
            $userNames = BOL_UserService::getInstance()->getUserNamesForList($userIds);
            $avatars =  BOL_AvatarService::getInstance()->getAvatarsUrlList($userIds);
            $this->assign('usernames', $userNames);
            $this->assign('avatars', $avatars);

            $avatarsImageInfo = array();
            for ($loop_index = 0; $loop_index < sizeof($avatars); $loop_index++){
                $userIdForAvatarImageInfo = $userIds[$loop_index];
                $avatarsImageInfo[$userIdForAvatarImageInfo] = BOL_AvatarService::getInstance()->
                    getAvatarInfo($userIdForAvatarImageInfo, $avatars[$userIdForAvatarImageInfo]);
            }
            $this->assign('avatarsImageInfo', $avatarsImageInfo);

            // Paging
            $pages = (int) ceil($records / $clipsPerPage);
            $paging = new BASE_CMP_Paging($page, $pages, 10);
            $this->assign('paging', $paging->render());

            $this->assign('count', $count);
        }
        else
        {
            $this->assign('no_content', OW::getLanguage()->text('video', 'no_video_found'));
        }
    }
}