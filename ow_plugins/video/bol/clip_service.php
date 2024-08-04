<?php
/**
 * Clip Service Class.  
 * 
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow.plugin.video.bol
 * @since 1.0
 */
final class VIDEO_BOL_ClipService
{
    const EVENT_AFTER_DELETE = 'video.after_delete';
    const EVENT_BEFORE_DELETE = 'video.before_delete';
    const EVENT_AFTER_EDIT = 'video.after_edit';
    const EVENT_AFTER_ADD = 'video.after_add';
    const EVENT_CACHE_THUMBNAILS_INCOMPLETE = 'video.cache_thumbnails_incomplete';
    
    const ENTITY_TYPE = 'video_comments';
    
    const TAGS_ENTITY_TYPE = "video";
    const RATES_ENTITY_TYPE = "video_rates";
    const FEED_ENTITY_TYPE = self::ENTITY_TYPE;

    /**
     * @var VIDEO_BOL_ClipDao
     */
    private $clipDao;
    /**
     * @var VIDEO_BOL_ClipFeaturedDao
     */
    private $clipFeaturedDao;
    /**
     * Class instance
     *
     * @var VIDEO_BOL_ClipService
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        $this->clipDao = VIDEO_BOL_ClipDao::getInstance();
        $this->clipFeaturedDao = VIDEO_BOL_ClipFeaturedDao::getInstance();
    }

    /**
     * Returns class instance
     *
     * @return VIDEO_BOL_ClipService
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Adds video clip
     *
     * @param VIDEO_BOL_Clip $clip
     * @return int
     */
    public function addClip( VIDEO_BOL_Clip $clip )
    {
        $this->clipDao->save($clip);
        
        $this->cleanListCache();

        return $clip->id;
    }
    
    public function saveClip( VIDEO_BOL_Clip $clip ) 
    {
        $this->clipDao->save($clip);
        $this->cleanListCache();
    }

    /**
     * Updates video clip
     *
     * @param VIDEO_BOL_Clip $clip
     * @return int
     */
    public function updateClip( VIDEO_BOL_Clip $clip )
    {
        $this->clipDao->save($clip);
        
        $this->cleanListCache();

        $event = new OW_Event('feed.action', array(
            'pluginKey' => 'video',
            'entityType' => self::FEED_ENTITY_TYPE,
            'entityId' => $clip->id,
            'userId' => $clip->userId
        ));
        OW::getEventManager()->trigger($event);

        $event = new OW_Event(self::EVENT_AFTER_EDIT, array('clipId' => $clip->id));
        OW::getEventManager()->trigger($event);
        
        return $clip->id;
    }

    /**
     * Finds clip by id
     *
     * @param int $id
     * @return VIDEO_BOL_Clip
     */
    public function findClipById( $id )
    {
        return $this->clipDao->findById($id);
    }
    
    /**
     * Finds clips by id list
     *
     * @param int $ids
     * @return array
     */
    public function findClipByIds( $ids )
    {
        return $this->clipDao->findByIdList($ids);
    }

    /**
     * Finds clip owner
     *
     * @param int $id
     * @return int
     */
    public function findClipOwner( $id )
    {
        $clip = $this->clipDao->findById($id);

        /* @var $clip VIDEO_BOL_Clip */

        return $clip ? $clip->getUserId() : null;
    }

    /**
     * Find latest clips authors ids
     *
     * @param integer $first
     * @param integer $count
     * @return array
     */
    public function findLatestPublicClipsAuthorsIds($first, $count)
    {
        return $this->clipDao->findLatestPublicClipsAuthorsIds($first, $count);
    }

    public function getVideoFileDir($FileName)
    {
        return OW::getPluginManager()->getPlugin('video')->getUserFilesDir() . $FileName;
    }

    public function prepareCacheFiles($clips = array()) {
        $cachedSecureFileKeyList = array();
        $keyFiles = array();
        $secureFilePluginActive = OW::getUser()->isAuthenticated() && FRMSecurityProvider::checkPluginActive('frmsecurefileurl', true);
        if ($secureFilePluginActive) {
            foreach ($clips as $clip) {
                if ($clip instanceof VIDEO_BOL_Clip) {
                    if ($clip->thumbUrl != null) {
                        $filePathDir = $this->getVideoFileDir($clip->thumbUrl);
                        $filePath = OW::getStorage()->prepareFileUrlByPath($filePathDir);
                        $keyInfo = FRMSECUREFILEURL_BOL_Service::getInstance()->getKeyFileUrl($filePath);
                        if (isset($keyInfo['key']) && !empty($keyInfo['key'])) {
                            $keyFiles[] = $keyInfo['key'];
                        }
                    }
                }
            }
        }
        if ($secureFilePluginActive && sizeof($keyFiles) > 0) {
            $keyList = FRMSECUREFILEURL_BOL_Service::getInstance()->existUrlByKeyList($keyFiles);
            foreach ($keyList as $urlObject) {
                $cachedSecureFileKeyList[$urlObject->key] = $urlObject;
            }
            foreach ($keyFiles as $key) {
                if (!array_key_exists($key, $cachedSecureFileKeyList)) {
                    $cachedSecureFileKeyList[$key] = null;
                }
            }
        }
        return $cachedSecureFileKeyList;
    }

    /**
     * @param $type
     * @param $page
     * @param $limit
     * @return array
     * @throws Redirect404Exception
     */
    public function findClipsList( $type, $page, $limit )
    {
        if ( $type == 'toprated' )
        {
            $first = ( $page - 1 ) * $limit;
            $topRatedList = BOL_RateService::getInstance()->findMostRatedEntityList(self::RATES_ENTITY_TYPE, $first, $limit);

            $clipArr = $this->clipDao->findByIdList(array_keys($topRatedList));

            $clips = array();

            foreach ( $clipArr as $key => $clip )
            {
                $clipArrItem = (array) $clip;
                $clips[$key] = $clipArrItem;
                $clips[$key]['score'] = $topRatedList[$clipArrItem['id']]['avgScore'];
                $clips[$key]['rates'] = $topRatedList[$clipArrItem['id']]['ratesCount'];
            }

            usort($clips, array('VIDEO_BOL_ClipService', 'sortArrayItemByDesc'));
        }
        else
        {
            $clips = $this->clipDao->getClipsList($type, $page, $limit);
        }

        $list = array();
        if ( is_array($clips) )
        {
            $clipIds = array();
            $cachedClipObjects = array();
            foreach ( $clips as $clip ) {
                if ($clip instanceof VIDEO_BOL_Clip) {
                    $clipIds[] = $clip->id;
                    $cachedClipObjects[$clip->id] = $clip;
                } else if (isset($clip['id'])) {
                    $clipIds[] = $clip['id'];
                    $cachedClipObjects[$clip['id']] = $clip;
                }
            }
            $cache['cache']['clips'] = $cachedClipObjects;
            $cache['cache']['secure_files'] = $this->prepareCacheFiles($cachedClipObjects);
            $cache['cache']['video_rates'] = BOL_RateDao::getInstance()->findEntitiesItemRateInfo($clipIds, 'video_rates');

            foreach ( $clips as $key => $clip )
            {
                $clip = (array) $clip;
                $list[$key] = $clip;
                $list[$key]['thumb'] = $this->getClipThumbUrl($clip['id'], $clip['code'], $clip['thumbUrl'], $cache);
            }
        }

        return $list;
    }

    /**
     * Deletes user all clips
     * 
     * @param int $userId
     * @return boolean
     */
    public function deleteUserClips( $userId )
    {
        if ( !$userId )
        {
            return false;
        }

        $clipsCount = $this->findUserClipsCount($userId);

        if ( !$clipsCount )
        {
            return true;
        }

        $clips = $this->findUserClipsList($userId, 1, $clipsCount);

        foreach ( $clips as $clip )
        {
            $event = new OW_Event('videplus.on.user.unregister', array('code'=>$clip['code']));
            OW::getEventManager()->trigger($event);
            $this->deleteClip($clip['id']);
        }

        return true;
    }

    public static function sortArrayItemByDesc( $el1, $el2 )
    {
        if ( $el1['score'] === $el2['score'] )
        {
            if ( $el1['rates'] === $el2['rates'] )
            {
                return 0;
            }
            
            return $el1['rates'] < $el2['rates'] ? 1 : -1;
        }

        return $el1['score'] < $el2['score'] ? 1 : -1;
    }

    /**
     * Finds user other video list
     *
     * @param $userId
     * @param $page
     * @param int $itemsNum
     * @param int $exclude
     * @return array of VIDEO_BOL_Clip
     */
    public function findUserClipsList( $userId, $page, $itemsNum, $exclude = null )
    {
        $clips = $this->clipDao->getUserClipsList($userId, $page, $itemsNum, $exclude);

        if ( is_array($clips) )
        {
            $list = array();
            foreach ( $clips as $key => $clip )
            {
                $clip = (array) $clip;
                $list[$key] = $clip;
                $list[$key]['thumb'] = $this->getClipThumbUrl($clip['id'], $clip['code'], $clip['thumbUrl']);
            }

            return $list;
        }

        return null;
    }

    /**
     * Finds list of tagged clips
     *
     * @param string $tag
     * @param int $page
     * @param int $limit
     * @return array of VIDEO_BOL_Clip
     */
    public function findTaggedClipsList( $tag, $page, $limit )
    {
        $first = ($page - 1 ) * $limit;

        $clipIdList = BOL_TagService::getInstance()->findEntityListByTag(self::TAGS_ENTITY_TYPE, $tag, $first, $limit);

        $clips = $this->clipDao->findByIdList($clipIdList);

        $list = array();
        if ( is_array($clips) )
        {
            foreach ( $clips as $key => $clip )
            {
                $clip = (array) $clip;
                $list[$key] = $clip;
                $list[$key]['thumb'] = $this->getClipThumbUrl($clip['id'], $clip['code'], $clip['thumbUrl']);
            }
        }

        return $list;
    }

    public function getThumbUrlWithoutId($thumbUrl = null){
        if($thumbUrl == null){
            return $this->getClipDefaultThumbUrl();
        }
        $event = new OW_Event('videplus.on.video.list.view.render', array('getThumb'=>true,'thumbUrl'=>$thumbUrl));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['thumbUrl'])){
            return $event->getData()['thumbUrl'];
        }
        return $this->getClipDefaultThumbUrl();
    }

    public function getClipThumbUrl( $clipId, $code = null, $thumbUrl = null, $params = array())
    {
        $event = new OW_Event('videplus.on.video.list.view.render', array('getThumb'=>true,'clipId'=>$clipId, 'params' => $params));
        OW::getEventManager()->trigger($event);
        if(isset($event->getData()['thumbUrl'])){
            return $event->getData()['thumbUrl'];
        }else {
            if (mb_strlen($thumbUrl)) {
                return $thumbUrl;
            }

            if ($code == null) {
                $clip = null;
                if (isset($params['cache']['clips'][$clipId])) {
                    $clip = $params['cache']['clips'][$clipId];
                }
                if ($clip == null) {
                    $clip = $this->findClipById($clipId);
                }
                if ($clip) {
                    if (mb_strlen($clip->thumbUrl)) {
                        return $clip->thumbUrl;
                    }
                    $code = $clip->code;
                }
            }

            $providers = new VideoProviders($code);

            return $providers->getProviderThumbUrl();
        }
    }
    
    public function getClipDefaultThumbUrl()
    {
        return OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'video-no-video.png';
    }
    

    /**
     * Counts clips
     *
     * @param string $type
     * @return int
     */
    public function findClipsCount( $type )
    {
        if ( $type == 'toprated' )
        {
            return BOL_RateService::getInstance()->findMostRatedEntityCount(self::RATES_ENTITY_TYPE);
        }

        return $this->clipDao->countClips($type);
    }

    /**
     * Counts user added clips
     *
     * @param int $userId
     * @return int
     */
    public function findUserClipsCount( $userId )
    {
        return $this->clipDao->countUserClips($userId);
    }

    /**
     * Counts clips with specified tag
     *
     * @param string $tag
     * @return array of VIDEO_BOL_Clip
     */
    public function findTaggedClipsCount( $tag )
    {
        return BOL_TagService::getInstance()->findEntityCountByTag(self::TAGS_ENTITY_TYPE, $tag);
    }

    /**
     * Gets number of clips to display per page
     *
     * @return int
     */
    public function getClipPerPageConfig()
    {
        return (int) OW::getConfig()->getValue('video', 'videos_per_page');
    }

    /**
     * Gets user clips quota
     *
     * @return int
     */
    public function getUserQuotaConfig()
    {
        return (int) OW::getConfig()->getValue('video', 'user_quota');
    }

    /**
     * Updates the 'status' field of the clip object 
     *
     * @param int $id
     * @param string $status
     * @return boolean
     */
    public function updateClipStatus( $id, $status )
    {
        /** @var $clip VIDEO_BOL_Clip */
        $clip = $this->clipDao->findById($id);

        $newStatus = $status == 'approve' ? VIDEO_BOL_ClipDao::STATUS_APPROVED : VIDEO_BOL_ClipDao::STATUS_BLOCKED;

        $clip->status = $newStatus;

        $this->updateClip($clip);

        return $clip->id ? true : false;
    }

    /**
     * Changes clip's 'featured' status
     *
     * @param int $id
     * @param string $status
     * @return boolean
     */
    public function updateClipFeaturedStatus( $id, $status )
    {
        $clip = $this->clipDao->findById($id);

        if ( $clip )
        {
            $clipFeaturedService = VIDEO_BOL_ClipFeaturedService::getInstance();

            if ( $status == 'mark_featured' )
            {
                return $clipFeaturedService->markFeatured($id);
            }
            else
            {
                return $clipFeaturedService->markUnfeatured($id);
            }
        }

        return false;
    }

    /**
     * Deletes video clip
     *
     * @param int $id
     * @return int
     */
    public function deleteClip( $id )
    {
        $clip = $this->findClipById($id);
        if(isset($clip)) {
            $event = new OW_Event('videplus.on.user.unregister', array('code' => $clip->code));
            OW::getEventManager()->trigger($event);
            $path = OW::getPluginManager()->getPlugin('video')->getUserFilesDir();
            if ( OW::getStorage()->fileExists($path. $clip->code) )
            {
                $hash=FRMSecurityProvider::generateUniqueId();
                $pastName=$clip->code;
                $string=explode(".",$clip->code);
                $videoExtention=$string[count($string)-1];
                $clip->code="deleted_".($clip->userId)."_".$hash.".".$videoExtention;
                $this->clipDao->save($clip);
                $newpath=$path.$clip->code;
                OW::getStorage()->renameFile($path.$pastName, $newpath);
            }
        }

        $event = new OW_Event(self::EVENT_BEFORE_DELETE, array('clipId' => $id));
        OW::getEventManager()->trigger($event);
        
        $this->clipDao->deleteById($id);
        OW::getLogger()->writeLog(OW_Log::INFO, 'delete_video', ['actionType'=>OW_Log::DELETE, 'enType'=>'video', 'enId'=>$id]);
        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'video-add_rate',
            'entityId' => $id
        ));

        BOL_CommentService::getInstance()->deleteEntityComments(self::ENTITY_TYPE, $id);
        BOL_RateService::getInstance()->deleteEntityRates($id, self::RATES_ENTITY_TYPE);
        BOL_TagService::getInstance()->deleteEntityTags($id, self::TAGS_ENTITY_TYPE);

        $this->clipFeaturedDao->markUnfeatured($id);

        BOL_FlagService::getInstance()->deleteByTypeAndEntityId(VIDEO_CLASS_ContentProvider::ENTITY_TYPE, $id);
        
        OW::getEventManager()->trigger(new OW_Event('feed.delete_item', array(
            'entityType' => self::FEED_ENTITY_TYPE,
            'entityId' => $id
        )));
        
        $this->cleanListCache();

        $event = new OW_Event(self::EVENT_AFTER_DELETE, array('clipId' => $id));
        OW::getEventManager()->trigger($event);

        return true;
    }
    
    public function cleanupPluginContent( )
    {
        BOL_CommentService::getInstance()->deleteEntityTypeComments(self::ENTITY_TYPE);
        BOL_RateService::getInstance()->deleteEntityTypeRates(self::RATES_ENTITY_TYPE);
        BOL_TagService::getInstance()->deleteEntityTypeTags(self::TAGS_ENTITY_TYPE);
        
        BOL_FlagService::getInstance()->deleteFlagList(self::ENTITY_TYPE);
    }

    /**
     * Adjust clip width and height
     *
     * @param string $code
     * @param int $width
     * @param int $height
     * @return string
     */
    public function formatClipDimensions( $code, $width, $height )
    {
        if ( !strlen($code) )
            return '';

        // remove %
        $code = preg_replace("/width=(\"|')?[\d]+(%)?(\"|')?/i", 'width=${1}' . $width . '${3}', $code);
        $code = preg_replace("/height=(\"|')?[\d]+(%)?(\"|')?/i", 'height=${1}' . $height . '${3}', $code);

        // adjust width and height
        $code = preg_replace("/width=(\"|')?[\d]+(px)?(\"|')?/i", 'width=${1}' . $width . '${3}', $code);
        $code = preg_replace("/height=(\"|')?[\d]+(px)?(\"|')?/i", 'height=${1}' . $height . '${3}', $code);

        $code = preg_replace("/width:( )?[\d]+(px)?/i", 'width:' . $width . 'px', $code);
        $code = preg_replace("/height:( )?[\d]+(px)?/i", 'height:' . $height . 'px', $code);

        return $code;
    }

    /**
     * Validate clip code integrity
     *
     * @param string $code
     * @param null $provider
     * @return string
     */
    public function validateClipCode( $code, $provider = null )
    {
        $textService = BOL_TextFormatService::getInstance();

        $code = UTIL_HtmlTag::stripTagsAndJs($code, $textService->getVideoParamList('tags'), $textService->getVideoParamList('attrs'));

        $objStart = '<object';
        $objEnd = '</object>';
        $objEndS = '/>';

        $posObjStart = stripos($code, $objStart);
        $posObjEnd = stripos($code, $objEnd);

        $posObjEnd = $posObjEnd ? $posObjEnd : stripos($code, $objEndS);

        if ( $posObjStart !== false && $posObjEnd !== false )
        {
            $posObjEnd += strlen($objEnd);
            return substr($code, $posObjStart, $posObjEnd - $posObjStart);
        }
        else
        {
            $embStart = '<embed';
            $embEnd = '</embed>';
            $embEndS = '/>';

            $posEmbStart = stripos($code, $embStart);
            $posEmbEnd = stripos($code, $embEnd) ? stripos($code, $embEnd) : stripos($code, $embEndS);

            if ( $posEmbStart !== false && $posEmbEnd !== false )
            {
                $posEmbEnd += strlen($embEnd);
                return substr($code, $posEmbStart, $posEmbEnd - $posEmbStart);
            }
            else
            {
                $frmStart = '<iframe ';
                $frmEnd = '</iframe>';
                $posFrmStart = stripos($code, $frmStart);
                $posFrmEnd = stripos($code, $frmEnd);
                if ( $posFrmStart !== false && $posFrmEnd !== false )
                {
                    $posFrmEnd += strlen($frmEnd);
                    $code = substr($code, $posFrmStart, $posFrmEnd - $posFrmStart);

                    preg_match('/src=(["\'])(.*?)\1/', $code, $match);
                    if ( !empty($match[2]) )
                    {
                        $src = $match[2];
                        if ( mb_substr($src, 0, 2) == '//' )
                        {
                            $src = 'http:' . $src;
                        }

                        $urlArr = parse_url($src);
                        $parts = explode('.', $urlArr['host']);

                        if ( count($parts) < 2 )
                        {
                            return '';
                        }

                        $d1 = array_pop($parts);
                        $d2 = array_pop($parts);
                        $host = $d2 . '.' . $d1;

                        $resourceList = BOL_TextFormatService::getInstance()->getMediaResourceList();

                        if ( !in_array($host, $resourceList) && !in_array($urlArr['host'], $resourceList) )
                        {
                            return '';
                        }
                    }

                    return $code;
                }
                else
                {
                    return '';
                }
            }
        }
    }

    /**
     * Adds parameter to embed code 
     *
     * @param string $code
     * @param string $name
     * @param string $value
     * @return string
     */
    public function addCodeParam( $code, $name = 'wmode', $value = 'transparent' )
    {
        $repl = $code;

        if ( preg_match("/<object/i", $code) )
        {
            $searchPattern = '<param';
            $pos = stripos($code, $searchPattern);
            if ( $pos )
            {
                $addParam = '<param name="' . $name . '" value="' . $value . '"></param><param';
                $repl = substr_replace($code, $addParam, $pos, strlen($searchPattern));
            }
        }

        if ( preg_match("/<embed/i", isset($repl) ? $repl : $code) )
        {
            $repl = preg_replace("/<embed/i", '<embed ' . $name . '="' . $value . '"', isset($repl) ? $repl : $code);
        }

        return $repl;
    }
    
    public function updateUserClipsPrivacy( $userId, $privacy )
    {
        if ( !$userId || !mb_strlen($privacy) )
        {
            return false;
        }
        
        $clips = $this->clipDao->findByUserId($userId);
        
        if ( !$clips )
        {
            return true;
        }
        
        $this->clipDao->updatePrivacyByUserId($userId, $privacy);
        
        $this->cleanListCache();

        $status = $privacy == 'everybody';
        $event = new OW_Event(
            'base.update_entity_items_status', 
            array('entityType' => 'video_rates', 'entityIds' => $clips, 'status' => $status)
        );
        OW::getEventManager()->trigger($event);
        
        return true;
    }
    
    public function cleanListCache()
    {
        OW::getCacheManager()->clean(array(VIDEO_BOL_ClipDao::CACHE_TAG_VIDEO_LIST));
    }

    public function cacheThumbnails( $limit )
    {
        $clips = $this->clipDao->getUncachedThumbsClipsList($limit);

        if ( !$clips )
        {
            return true;
        }

        foreach ( $clips as $clip )
        {
            $prov = new VideoProviders($clip->code);
            if ( !$clip->provider )
            {
                $clip->provider = $prov->detectProvider();
            }
            $thumbUrl = $prov->getProviderThumbUrl($clip->provider);
            if ( $thumbUrl != VideoProviders::PROVIDER_UNDEFINED )
            {
                $clip->thumbUrl = $thumbUrl;
            }
            $clip->thumbCheckStamp = time();
            $this->clipDao->save($clip);
        }

        if (count($clips) == $limit) {
            OW::getEventManager()->trigger(new OW_Event(self::EVENT_CACHE_THUMBNAILS_INCOMPLETE));
        }

        return true;
    }

    public function deleteComment( OW_Event $event )
    {
        $params = $event->getParams();

        if ( empty($params['entityType']) || $params['entityType'] !== 'video_comments' )
            return;

        $commentId = (int) $params['commentId'];
        OW::getEventManager()->call('notifications.remove', array(
            'entityType' => 'video-add_comment',
            'entityId' => $commentId
        ));
    }

    public function findIdListBySearch( $q, $first, $count )
    {
        $ex = new OW_Example();
        $ex->andFieldLike('title', '%'.$q.'%');
        $ex->setOrder('addDatetime desc')->setLimitClause(0, $first+ $count);
        $list1 = $this->clipDao->findIdListByExample($ex);

        $ex = new OW_Example();
        $ex->andFieldLike('description', '%'.$q.'%');
        $ex->setOrder('addDatetime desc')->setLimitClause(0, $first+ $count);
        $list2 = $this->clipDao->findIdListByExample($ex);

        $list = array_unique(array_merge($list1, $list2));
        return array_splice($list, $first, $count );
    }

    public function userIsAuthorized(){
        $userId = null;
        $showAll= false;
        $user = OW::getUser();
        if(isset($user)) {
            $userId = $user->getId();
            if($user->isAdmin() || $user->isAuthorized('video'))
                $showAll = true;
        }
        return array($userId,$showAll);
    }


    public function onCollectSearchItems(OW_Event $event){
        if (!OW::getUser()->isAdmin() && !OW::getUser()->isAuthorized('video', 'view') && !OW::getUser()->isAuthorized('video'))
        {
            return;
        }
        $searchValue = '';
        $params = $event->getParams();
        $selected_section = null;
        if(!empty($params['selected_section']))
            $selected_section = $params['selected_section'];
        if( isset($selected_section) && $selected_section != OW_Language::getInstance()->text('frmadvancesearch','all_sections') && $selected_section!= OW::getLanguage()->text('frmadvancesearch', 'videos_label') )
            return;

        if ( !empty($params['q']) )
        {
            $searchValue = $params['q'];
        }
        $maxCount = empty($params['maxCount'])?10:$params['maxCount'];
        $first= empty($params['first'])?0:$params['first'];
        $first=(int)$first;
        $count=empty($params['count'])?$first+$maxCount:$params['count'];
        $count=(int)$count;
        /**
         * @TODO replace it with proper method
         */
        list($userId,$showAll) = $this->userIsAuthorized();
        $resultData = array();

        if (!isset($params['do_query']) || $params['do_query']) {
            $resultData=$this->clipDao->findListAllPrivacy($userId,strip_tags(UTIL_HtmlTag::stripTags($searchValue)),$showAll,$first,$count);
        }
        $result = array();
        $count = 0;
        $userIdList = array_column($resultData, 'userId');
        $userIdListUnique = array_unique($userIdList);
        $displayNames = BOL_UserService::getInstance()->getDisplayNamesForList($userIdListUnique);
        $userUrls = BOL_UserService::getInstance()->getUserUrlsForList($userIdListUnique);
        foreach($resultData as $item){
            $emptyImage = $item['thumbUrl'] == null ? true : false;
            $itemInformation = array();
            $itemInformation['title'] = $item['title'];
            $itemInformation['id'] = $item['id'];
            $itemInformation['createdDate'] = $item['addDatetime'];
            $userId = $item['userId'];
            $itemInformation['userId'] = $userId;
            $itemInformation['displayName'] = $displayNames[$userId];
            $itemInformation['userUrl'] = $userUrls[$userId];
            $itemInformation['link'] = OW::getRouter()->urlForRoute('view_clip', array('id'=>$item['id']));
            $itemInformation['url'] = FRMVIDEOPLUS_BOL_Service::getInstance()->getVideoFilePath($item['code']);
            $itemInformation['image'] = $this->getThumbUrlWithoutId($item['thumbUrl']);
            $itemInformation['emptyImage'] = $emptyImage;
            $itemInformation['label'] = OW::getLanguage()->text('frmadvancesearch', 'videos_label');
            $result[] = $itemInformation;
            $count++;
            if($count == $maxCount){
                break;
            }
        }

        $data = $event->getData();
        $data['video'] = array('label' => OW::getLanguage()->text('frmadvancesearch', 'videos_label'), 'data' => $result);
        $event->setData($data);
    }

    public function getVideoUrl($clip)
    {
        return OW::getRouter()->urlForRoute('view_clip', array('id'=>$clip->getId()));
    }

    public function canUserSeeVideoOfUserId($viewerId, $clipId, $clip = null){
        if(OW::getUser()->isAdmin()){
            return true;
        }
        if($clip == null){
            $clip = VIDEO_BOL_ClipService::getInstance()->findClipById($clipId);
        }

        if($clip == null){
            return false;
        }

        $ownerId = $clip->userId;
        if($viewerId == $ownerId){
            return true;
        }

        if(!VIDEO_BOL_ClipService::getInstance()->checkVideoPrivacy($ownerId, $clip->privacy)){
            return false;
        }

        $modPermissions = OW::getUser()->isAuthorized('video');

        if ( $clip->status != VIDEO_BOL_ClipDao::STATUS_APPROVED && !$modPermissions)
        {
            return false;
        }

        return true;
    }

    public function checkVideoPrivacy($ownerId, $privacy){
        if(OW::getUser()->isAdmin()){
            return true;
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)){
            $canView = FRMSECURITYESSENTIALS_BOL_Service::getInstance()->checkPrivacyOfObject($privacy, $ownerId, null, false);
            if(!$canView){
                return false;
            }
        }

        return true;
    }

    /***
     * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
     * @param $clip
     * @param $params
     */
    public function addJSONLD($clip, $params = array()){
        $id = $clip->id;

        // rating
        $info = array();
        if (isset($params['cache']['video_rates']) && array_key_exists($id, $params['cache']['video_rates'])) {
            if ($params['cache']['video_rates'][$id] != null) {
                $info = $params['cache']['video_rates'][$id];
            }
        } else {
            $info = BOL_RateService::getInstance()->findRateInfoForEntityItem($id, 'video_rates');
        }
        $info['avgScore'] = !isset($info['avg_score']) ? 5 : round($info['avg_score'], 2);
        $info['ratesCount'] = !isset($info['rates_count']) ? 0 : (int) $info['rates_count'];

        //set JSON-LD
        $clipUrl = $this->getVideoUrl($clip);
        $clipThumbUrl = $this->getClipThumbUrl($id, null, null, $params);
        OW::getDocument()->addJSONLD("VideoObject", $clip->title, $clip->userId, $clipUrl, $clipThumbUrl,
            [
                "publisher" => [
                    "@type" => "Organization",
                    "name" => OW::getConfig()->getValue('base', 'site_name'),
                    "logo" => ["@type"=>"ImageObject","url"=>OW_URL_HOME.'favicon.ico']
                ],
                "description" => empty($clip->description)?$clip->title:UTIL_HtmlTag::stripTagsAndJs($clip->description),
                "thumbnailUrl" => $this->getClipThumbUrl($id, null, null, $params),
                "datePublished" => date('Y-m-d', $clip->addDatetime),
                "uploadDate" => date('Y-m-d', $clip->addDatetime),
                "@id" => $clipUrl,
                "url" => $clipUrl,
                "aggregateRating" => [
                    "@type" => "AggregateRating",
                    "ratingValue" => $info['avgScore'],
                    "reviewCount" => $info['ratesCount']+1
                ],
                "interactionStatistic" => [
                    "@type" => "InteractionCounter",
                    "interactionType" => "http://schema.org/WatchAction",
                    "userInteractionCount" => $info['ratesCount']*4 + 3,
                    "interactionService" => [
                        "@type" => "WebSite",
                        "name" => OW::getConfig()->getValue('base', 'site_name'),
                        "@id" => $clipUrl
                    ]
                ]
            ]
        );
    }

    public function verifyAparatVideoProvider(OW_Event $event)
    {
        $params = $event->getParams();
        $verificationData['video_found'] = true;
        $clipCode = $params['clip_code'];
        $start_code = strpos($clipCode,'videohash/')+10;
        $end_code = strpos($clipCode,"/vt/frame");
        $vid = substr($clipCode, $start_code,$end_code-$start_code);
        $aparatUrl = 'https://www.aparat.com/etc/api/video/videohash/' . $vid;
        $response = json_decode(UTIL_HttpResource::getContents($aparatUrl),true);
        if(!isset($response['video']['id']))
        {
            $verificationData['video_found'] = false;
        }
        $event->setData($verificationData);
    }
}