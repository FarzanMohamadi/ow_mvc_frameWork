<?php
/**
 * Video list component
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.components
 * @since 1.0
 */
class VIDEO_MCMP_VideoList extends OW_MobileComponent
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

        $listType = isset($params['type']) ? $params['type'] : '';
        $count = isset($params['count']) ? $params['count'] : 5;
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
            $tagsLabel = array();
            $this->assign('no_content', null);
            foreach ($clips as $key => $clip) {
                if(isset( $clips[$key]['description'])) {
                    $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $clips[$key]['description'])));
                    if(isset($stringRenderer))
                        $clips[$key]['description']=$stringRenderer->getData()['string'];
                }

            }

            $this->assign('clips', $clips);

            $userIds = array();
            if ( is_array($clips) ) {
                $clipIds = array();
                foreach ($clips as $key => $clip) {
                    $clip = (array)$clip;
                    $clipIds[] = $clip['id'];
                }
                $cache['video_tags'] = BOL_EntityTagDao::getInstance()->findEntityTagItemsByEntityIds($clipIds, 'video');
                foreach ($clips as $key => $clip) {
                    $clip = (array)$clip;
                    $tags = array();
                    if (isset($cache['video_tags'][$clip['id']])) {
                        $tags = $cache['video_tags'][$clip['id']];
                    } else {
                        $tags = BOL_TagService::getInstance()->findEntityTags($clip['id'],'video');
                    }
                    if(sizeof($tags)>0){
                        $labels = " ";
                        $comma = OW::getLanguage()->text('base', 'comma').' ';
                        foreach($tags as $tag)
                        {
                            $labels .= '<a href="'.OW::getRouter()->urlForRoute('view_tagged_list', array('tag' => $tag->getLabel())).'">'.$tag->getLabel().'</a>'.$comma;
                        }
                        $labels = rtrim($labels, $comma);
                        $tagsLabel[$clip['id']]=$labels;
                    }
                    if (!in_array($clip['userId'], $userIds))
                        array_push($userIds, $clip['userId']);
                }
            }
            $this->assign('tags', $tagsLabel);

            $names = BOL_UserService::getInstance()->getDisplayNamesForList($userIds);
            $this->assign('displayNames', $names);
            $usernames = BOL_UserService::getInstance()->getUserNamesForList($userIds);
            $this->assign('usernames', $usernames);

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
            $paging = new BASE_CMP_PagingMobile($page, $pages, 10);
            $this->assign('paging', $paging->render());

            $this->assign('count', $count);
        }
        else
        {
            $this->assign('no_content', OW::getLanguage()->text('video', 'no_video_found'));
        }
    }
}