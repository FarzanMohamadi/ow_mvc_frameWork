<?php
/**
 *
 */

/**
 * frmticketing Service.
 *
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmticketing.bol
 * @since 1.0
 */
final class FRMTICKETING_BOL_TicketService
{

    /**
     * @var FRMTICKETING_BOL_TicketDao
     */
    private $ticketDao;

    private $ticketPostDao;

    const TICKET_PER_PAGE=10;

    const POST_PER_PAGE=10;


    /**
     * Search Elements
     */
    private $searchTitle = null;
    private $searchCategory = null;
    private $searchOrder= null;
    private $searchLock= null;
    private $page=null;

    /**
     * Constructor.
     */
    protected function __construct()
    {
        $this->ticketDao = FRMTICKETING_BOL_TicketDao::getInstance();
        $this->ticketPostDao= FRMTICKETING_BOL_TicketPostDao::getInstance();
    }

    /**
     * Singleton instance.
     *
     * @var FRMTICKETING_BOL_TicketService
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTICKETING_BOL_TicketService
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function getSections($currentSection = null){
        if($currentSection==null){
            $currentSection = 1;
        }

        $sectionsInformation = array();

        for ($i = 1; $i <= 2; $i++) {
            $sections[] = array(
                'sectionId' => $i,
                'active' => $currentSection == $i ? true : false,
                'url' => OW::getRouter()->urlForRoute('frmticketing.admin-currentSection', array('currentSection' => $i)),
                'label' => $this->getPageHeaderLabel($i)
            );
        }

        $sectionsInformation['sections'] = $sections;
        $sectionsInformation['currentSection'] = $currentSection;
        return $sectionsInformation;
    }

    public function getPageHeaderLabel($sectionId)
    {
        if ($sectionId == 1) {
            return OW::getLanguage()->text('frmticketing', 'category_setting');
        } else if ($sectionId == 2) {
            return OW::getLanguage()->text('frmticketing', 'order_setting');
        }
    }

    public function deleteTicketCategoryInformation($categoryId)
    {
        $this->ticketDao->deleteTicketCategoryInformation($categoryId);
    }

    public function deleteTicketOrderInformation($categoryId)
    {
        $this->ticketDao->deleteTicketOrderInformation($categoryId);
    }

    /**
     * Add attachments
     *
     * @param $entityId
     * @param string $attachmentUid
     * @param string $entityType
     * @return void
     */
    protected function addAttachments($entityId, $attachmentUid,$entityType)
    {

        $filesArray = BOL_AttachmentService::getInstance()->getFilesByBundleName('frmticketing', $attachmentUid);

        if ( $filesArray )
        {
            $attachmentService = FRMTICKETING_BOL_TicketAttachmentService::getInstance();

            foreach ( $filesArray as $file )
            {
                $attachmentDto = new FRMTICKETING_BOL_TicketAttachment();
                $attachmentDto->entityId = $entityId;
                $attachmentDto->entityType = $entityType;
                $attachmentDto->fileName = $file['dto']->origFileName;
                $attachmentDto->fileNameClean = $file['dto']->fileName;
                $attachmentDto->fileSize = $file['dto']->size * 1024;
                $attachmentDto->hash = FRMSecurityProvider::generateUniqueId();

                $attachmentService->addAttachment($attachmentDto, $file['path']);
            }
            BOL_AttachmentService::getInstance()->deleteAttachmentByBundle('frmticketing', $attachmentUid);
        }
    }

    public function addTicket($data)
    {
        $ticketDto = new FRMTICKETING_BOL_Ticket();
        $additionArray=array();
        $ticketDto->title=strip_tags($data['title']);
        $ticketDto->userId=OW::getUser()->getId();
        $ticketDto->description = UTIL_HtmlTag::stripTagsAndJs($data['description'], array('form', 'input', 'button'), null, true);
        $ticketDto->timeStamp = time();
        $categoryData = explode('_',$data['category']);
        switch ($categoryData[0])
        {
            case 'network':
                if(FRMSecurityProvider::checkPluginActive('frmsaas', true)) {
                    $saasService = FRMSAAS_BOL_Service::getInstance();
                    $network=$saasService->getNetworkWithId($categoryData[1]);
                    $additionArray['networkUid'] = $network->uid;
                    $additionArray['networkName'] = $network->name;
                    $ticketDto->networkId = $categoryData[1];
                }
                break;
            case 'category':
                $ticketDto->categoryId=$categoryData[1];
                break;
        }
        $ticketDto->orderId=$data['order'];
        $ticketDto->description = str_replace('&lt;!--more--&gt;', '<!--more-->', $ticketDto->description);
        $ticketDto->addition=json_encode($additionArray);
        $this->ticketDao->save($ticketDto);

        $this->addAttachments($ticketDto->id, $data['attachmentUid'],FRMTICKETING_BOL_TicketAttachmentDao::TICKET_TYPE);
        return $ticketDto;
    }

    public function findAllTickets($page)
    {
        if(isset($this->page))
        {
            $page=$this->page;
        }
        $count = self::TICKET_PER_PAGE;
        $first = ($page - 1) * $count;
        return $this->ticketDao->findAllTickets($this->searchTitle,$this->searchCategory,$this->searchLock,$this->searchOrder,$first, $count);
    }

    public function findAllTicketsCount()
    {
        return $this->ticketDao->findAllTicketsCount($this->searchTitle,$this->searchCategory,$this->searchLock,$this->searchOrder);
    }


    public function  findTicketInfoById($ticketId)
    {
        return $this->ticketDao->findTicketInfoById($ticketId);
    }

    public function findTicketsByUserId($userId,$page)
    {
        $count = self::TICKET_PER_PAGE;
        $first = ($page - 1) * $count;
        return $this->ticketDao->findTicketsByUserId($userId,$this->searchTitle,$this->searchCategory,$this->searchOrder,$first, $count);
    }
    public function findTicketsByUserIdCount($userId)
    {
        return $this->ticketDao->findTicketsByUserIdCount($userId,$this->searchTitle,$this->searchCategory,$this->searchOrder);
    }



    public function validateCategoryData(OW_Event $event)
    {
        $params = $event->getParams();
        if(!isset($params['categoryData']) || $params['categoryData'][0]!='category' || !isset($params['userId']))
        {
            return;
        }

        $categoryService=FRMTICKETING_BOL_TicketCategoryService::getInstance();
        $category=$categoryService->findCategoryById($params['categoryData'][1]);
        if(!isset($category))
        {
            $event->setData(array('violationOccured'=>true));
        }
    }

    /**
     * Returns FRMTICKETING_BOL_Ticket
     *
     * @param $ticketId
     * @return FRMTICKETING_BOL_Ticket
     */
    public function findTicketById($ticketId)
    {
        return $this->ticketDao->findById($ticketId);
    }

    /**
     * Returns ticket's post list
     *
     * @param int $ticketId
     * @param integer $page
     * @return array
     */
    public function findTicketPostList( $ticketId, $page )
    {
        $count = self::POST_PER_PAGE;
        $first = ($page - 1) * $count;

        $ticketPostDtoList = $this->ticketPostDao->findTicketPostList($ticketId, $first, $count);
        $ticketPostList = array();
        $ticketPostIds = array();

        //prepare ticket posts
        foreach ( $ticketPostDtoList as $ticketPostDto )
        {
            $post = array(
                'id' => $ticketPostDto->id,
                'ticketId' => $ticketPostDto->ticketId,
                'userId' => $ticketPostDto->userId,
                'text' => $this->formatQuote($ticketPostDto->text),
                'createStamp' => UTIL_DateTime::formatDate($ticketPostDto->createStamp),
                'createStampRaw' => $ticketPostDto->createStamp,
                'postUrl' => $this->getPostUrl($ticketPostDto->ticketId, $ticketPostDto->id),
                'edited' => array()
            );

            $ticketPostList[$ticketPostDto->id] = $post;

            $ticketPostIds[] = $ticketPostDto->id;
        }

        $editedPostDtoList = ( $ticketPostIds ) ? FRMTICKETING_BOL_TicketEditPostDao::getInstance()->findByPostIdList($ticketPostIds) : array();

        //get edited posts array
        foreach ( $editedPostDtoList as $editedPostDto )
        {
            $editedPost = array(
                'postId' => $editedPostDto->postId,
                'userId' => $editedPostDto->userId,
                'editStamp' => UTIL_DateTime::formatDate($editedPostDto->editStamp)
            );

            $ticketPostList[$editedPostDto->postId]['edited'] = $editedPost;
        }

        return $ticketPostList;
    }

    /**
     * Returns post url
     *
     * @param int $ticketId
     * @param int $postId
     * @param boolean $anchor
     * @param int $page
     * @return string
     */
    public function getPostUrl( $ticketId, $postId, $anchor = true, $page = null )
    {
        if ( empty($page) || !$page )
        {
            $count = self::POST_PER_PAGE;
            $postNumber = $this->ticketPostDao->findPostNumber($ticketId, $postId);
            $page = ceil($postNumber / $count);
        }

        $ticketUrl = OW::getRouter()->urlForRoute('frmticketing.view_ticket',array('ticketId'=>$ticketId));
        $anchor_str = ($anchor) ? "#post-$postId" : "";
        $postUrl = $ticketUrl . "?page=$page" . $anchor_str;

        return $postUrl;
    }


    /**
     * Add post
     *
     * @param FRMTICKETING_BOL_Ticket $ticketDto
     * @param array $data
     *      string text
     *      string attachmentUid
     * @return FRMTICKETING_BOL_TicketPost
     */
    public function addPost( FRMTICKETING_BOL_Ticket $ticketDto, array $data )
    {
        $ticketPostDto = new FRMTICKETING_BOL_TicketPost();
        $ticketPostDto->ticketId = $ticketDto->id;
        $ticketPostDto->userId = OW::getUser()->getId();
        $ticketPostDto->text = UTIL_HtmlTag::stripTagsAndJs($data['text'], array('form', 'input', 'button'), null, true);
        $ticketPostDto->createStamp = time();
        $this->saveOrUpdatePost($ticketPostDto);

        $this->addAttachments($ticketPostDto->id, $data['attachmentUid'],FRMTICKETING_BOL_TicketAttachmentDao::POST_TYPE);

        /*
         * TODO send notification for adding post to a ticket
         */
        $event = new OW_Event('ticket.add_post', array('postId' => $ticketPostDto->id, 'ticketId' => $ticketDto->id, 'userId' => $ticketPostDto->userId));
        OW::getEventManager()->trigger($event);

        return $ticketPostDto;
    }

    /**
     * Edit post
     *
     * @param integer $userId
     * @param array $data
     *      string text
     *      string attachmentUid
     * @param FRMTICKETING_BOL_TicketPost $postDto
     * @return void
     */
    public function editPost( $userId, array $data, FRMTICKETING_BOL_TicketPost $postDto )
    {
        //save post
        $postDto->text = UTIL_HtmlTag::stripTagsAndJs($data['text'], array('form', 'input', 'button'), null, true);
        $this->saveOrUpdatePost($postDto);

        //save post edit info
        $editPostDto = $this->findEditPost($postDto->id);

        if ( $editPostDto === null )
        {
            $editPostDto = new FRMTICKETING_BOL_TicketEditPost();
        }

        $editPostDto->postId = $postDto->id;
        $editPostDto->userId = $userId;
        $editPostDto->editStamp = time();

        $this->saveOrUpdateEditPost($editPostDto);
        $this->addAttachments($postDto->id, $data['attachmentUid'],FRMTICKETING_BOL_TicketAttachmentDao::POST_TYPE);
    }

    /**
     * Returns edit post
     *
     * @param int $postId
     * @return FRMTICKETING_BOL_TicketEditPost
     */
    public function findEditPost( $postId )
    {
        $editPostDao = FRMTICKETING_BOL_TicketEditPostDao::getInstance();

        return $editPostDao->findByPostId($postId);
    }

    /**
     * Saves or updates edit post
     *
     * @param FRMTICKETING_BOL_TicketEditPost $editPostDto
     */
    public function saveOrUpdateEditPost( $editPostDto )
    {
        $editPostDao = FRMTICKETING_BOL_TicketEditPostDao::getInstance();

        $editPostDao->save($editPostDto);
    }

    /**
     * Edit ticket
     *
     * @param integer $userId
     * @param array $data
     *      string text
     *      string attachmentUid
     * @param FRMTICKETING_BOL_Ticket $ticketDto
     * @return void
     */
    public function editTicket($userId, array $data, FRMTICKETING_BOL_Ticket $ticketDto)
    {
        //save ticket
        $ticketDto->title = strip_tags($data['title']);
        $ticketDto->description = UTIL_HtmlTag::stripTagsAndJs(trim($data['description']), array('form', 'input', 'button'), null, true);
        $additionArray=array();
        $categoryData = explode('_',$data['category']);
        switch ($categoryData[0])
        {
            case 'network':
                if(FRMSecurityProvider::checkPluginActive('frmsaas', true)) {
                    $saasService = FRMSAAS_BOL_Service::getInstance();
                    $network=$saasService->getNetworkWithId($categoryData[1]);
                    $additionArray['networkUid'] = $network->uid;
                    $additionArray['networkName'] = $network->name;
                    $ticketDto->networkId = $categoryData[1];
                }
                $ticketDto->categoryId=null;
                break;
            case 'category':
                $ticketDto->categoryId=$categoryData[1];
                $ticketDto->networkId=null;
                break;
        }
        $ticketDto->orderId=$data['order'];
        $ticketDto->description = str_replace('&lt;!--more--&gt;', '<!--more-->', $ticketDto->description);
        $ticketDto->addition=json_encode($additionArray);

        $this->saveOrUpdateTicket($ticketDto);

        $this->addAttachments($ticketDto->id, $data['attachmentUid'],FRMTICKETING_BOL_TicketAttachmentDao::TICKET_TYPE);
    }


    /**
     * Returns post
     *
     * @param int $postId
     * @return FRMTICKETING_BOL_TicketPost
     */
    public function findTicketPostById( $postId )
    {
        return $this->ticketPostDao->findById($postId);
    }

    /**
     * Returns previous post
     *
     * @param int $ticketId
     * @param int $postId
     * @return FRMTICKETING_BOL_TicketPost
     */
    public function findPreviousPost( $ticketId, $postId )
    {
        return $this->ticketPostDao->findPreviousPost($ticketId, $postId);
    }

    /**
     * Deletes post
     *
     * @param int $postId
     */
    public function deletePost( $postId )
    {
        $editTicketPostDao = FRMTICKETING_BOL_TicketEditPostDao::getInstance();

        //delete post edit info
        $editTicketPostDao->deleteByPostId($postId);

        //delete post
        $this->ticketPostDao->deleteById($postId);

        //delete attachments
        FRMTICKETING_BOL_TicketAttachmentService::getInstance()->deleteAttachmentsByTypeAndId(FRMTICKETING_BOL_TicketAttachmentDao::POST_TYPE,$postId);

    }

    /**
     * Saves or updates ticket
     *
     * @param FRMTICKETING_BOL_Ticket $ticketDto
     */
    public function saveOrUpdateTicket( $ticketDto)
    {
        $this->ticketDao->save($ticketDto);
    }

    /**
     * Deletes ticket
     *
     * @param int $ticketId
     */
    public function deleteTicket( $ticketId )
    {

        $editTicketPostDao = FRMTICKETING_BOL_TicketEditPostDao::getInstance();

        $postIds = $this->ticketPostDao->findTicketPostIdList($ticketId);

        if ( $postIds )
        {
            //delete ticket posts edit info
            $editTicketPostDao->deleteByPostIdList($postIds);

            //delete ticket posts
            foreach ( $postIds as $post )
            {
                $this->deletePost($post);
            }
        }

        //delete attachments
        FRMTICKETING_BOL_TicketAttachmentService::getInstance()->deleteAttachmentsByTypeAndId(FRMTICKETING_BOL_TicketAttachmentDao::TICKET_TYPE,$ticketId);

        //delete ticket
        $this->ticketDao->deleteById($ticketId);

    }

    /**
     * Returns post
     *
     * @param int $postId
     * @return FRMTICKETING_BOL_TicketPost
     */
    public function findPostById( $postId )
    {
        return $this->ticketPostDao->findById($postId);
    }

    /**
     * Returns ticket's post count
     *
     * @param int $ticketId
     * @return int
     */
    public function findTicketPostCount( $ticketId )
    {
        return (int) $this->ticketPostDao->findTicketPostCount($ticketId);
    }

    public function formatQuote( $text )
    {
        $quote_reg = "#\<blockquote\sfrom=.?([^\"]*).?\>#i";

        //replace quote tag
        if ( preg_match_all($quote_reg, $text, $text_arr) )
        {
            $key = 0;
            foreach ( $text_arr[0] as $key => $value )
            {
                $quote = '<blockquote class="ow_quote">' .
                    '<span class="ow_small ow_author">' . OW::getLanguage()->text('frmticketing', 'ticket_quote') . ' ' .
                    OW::getLanguage()->text('frmticketing', 'ticket_quote_from') . ' <b>' . $text_arr[1][$key] . '</b></span><br />';
                $text = str_replace($value, $quote, $text);
            }

            $is_closed = $key - substr_count($text, '</blockquote>') - 1;

            if ( $is_closed && $is_closed > 0 )
            {
                for ( $i = 0; $is_closed > $i; $i++ )
                    $text .= "</blockquote>";
            }

            $text = nl2br($text);
        }

        return $text;
    }

    /**
     * Saves or updates post
     *
     * @param FRMTICKETING_BOL_TicketPost $postDto
     */
    public function saveOrUpdatePost( $postDto )
    {
        $postDto->text = str_replace('&lt;!--more--&gt;', '<!--more-->', $postDto->text);
        $this->ticketPostDao->save($postDto);
    }

    /***
     * @param $name
     * @return string
     */
    public function getIconUrl($name){
        return OW::getPluginManager()->getPlugin('frmticketing')->getStaticUrl(). 'images/'.$name.'.svg';
    }

    /***
     * @param $ext
     * @return string
     */
    public function getProperIcon($ext){
        $videoFormats = array('mov','mkv','mp4','avi','flv','ogg','mpg','mpeg');

        $wordFormats = array('docx','doc','docm','dotx','dotm');

        $excelFormats = array('xlsx','xls','xlsm');

        $zipFormats = array('zip','rar');

        $imageFormats =array('jpg','jpeg','gif','tiff','png');

        if(in_array($ext,$videoFormats)){
            return $this->getIconUrl('avi');
        }
        else if(in_array($ext,$wordFormats)){
            return $this->getIconUrl('doc');
        }
        else if(in_array($ext,$excelFormats)){
            return $this->getIconUrl('xls');
        }
        else if(in_array($ext,$zipFormats)){
            return $this->getIconUrl('zip');
        }
        else if(in_array($ext,$imageFormats)){
            return $this->getIconUrl('jpg');
        }
        else if(strcmp($ext,'pdf')==0){
            return $this->getIconUrl('pdf');
        }
        else if(strcmp($ext,'txt')==0){
            return $this->getIconUrl('txt');
        }
        else{
            return $this->getIconUrl('file');
        }
    }

    public function addIconsToTicketAttachments($attachments){
        foreach ($attachments as &$postAttachment)
        {
            foreach ($postAttachment as &$attachment) {
                $fileNameArr = explode('.', $attachment['fileName']);
                $fileNameExt = end($fileNameArr);
                $iconUrl = $this->getProperIcon(strtolower($fileNameExt));
                $attachment['iconUrl'] = $iconUrl;
            }
        }
        return $attachments;
    }


    /**
     * @param $name
     * @return Form
     */
    public function getTicketFilterForm($name)
    {
        $form = new Form($name);
        $form->setMethod(Form::METHOD_GET);
        $searchTitle = new TextField('searchTitle');
        $searchTitle->addAttribute('placeholder',OW::getLanguage()->text('frmticketing', 'search_title'));
        $searchTitle->addAttribute('class','search_title');
        $searchTitle->addAttribute('id','searchTitle');
        if(trim($this->searchTitle)!=null) {
            $searchTitle->setValue($this->searchTitle);
        }
        $searchTitle->setHasInvitation(false);
        $form->addElement($searchTitle);



        $categories = FRMTICKETING_BOL_TicketCategoryService::getInstance()->getTicketCategoryList();
        $categoryField = new Selectbox('searchCategory');
        $option = array();
        $option[null] = OW::getLanguage()->text('frmticketing','select_category');
        foreach ($categories as $category) {
            $option[$category->id] = $category->title;
        }

        $categoryOptionEvent= OW::getEventManager()->trigger(new OW_Event('ticket.category.option',array('option'=>$option,'userId'=>OW::getUser()->getId())));
        if(isset($categoryOptionEvent->getData()['option']))
        {
            $option=$categoryOptionEvent->getData()['option'];
        }
        $categoryField->setHasInvitation(false);
        $categoryField->setOptions($option);
        $categoryField->addAttribute('id','searchCategory');
        if(trim($this->searchCategory)!=null) {
            $categoryField->setValue($this->searchCategory);
        }else{

        }
        $form->addElement($categoryField);

        $orders = FRMTICKETING_BOL_TicketOrderService::getInstance()->getTicketOrderList();
        $orderField = new Selectbox('searchOrder');
        $option = array();
        $option[null] = OW::getLanguage()->text('frmticketing','select_order');
        foreach ($orders as $order) {
            $option[$order->id] = $order->title;
        }
        $orderField->setHasInvitation(false);
        $orderField->setOptions($option);
        $orderField->addAttribute('id','searchOrder');
        if(trim($this->searchOrder)!=null) {
            $orderField->setValue($this->searchOrder);
        }
        $form->addElement($orderField);

        $lockField = new Selectbox('searchLock');
        $option = array();
        $option[null] = OW::getLanguage()->text('frmticketing','select_locked_unlocked');
        $option['unlocked'] = OW::getLanguage()->text('frmticketing','select_unlocked');
        $option['locked'] = OW::getLanguage()->text('frmticketing','select_locked');
        $lockField->setHasInvitation(false);
        $lockField->setOptions($option);
        $lockField->addAttribute('id','searchLock');
        if(trim($this->searchLock)!=null) {
            $lockField->setValue($this->searchLock);
        }
        $form->addElement($lockField);


        $submit = new Submit('save');
        $form->addElement($submit);
        $form->setAction(OW::getRouter()->urlForRoute('frmticketing.view_tickets'));

        return $form;
    }

    public function setFilterParameters(){
        $this->page=null;
        if (OW::getRequest()->isPost()) {
            $this->page=1;
            $this->getFilterParameters($_POST);
        }else{
            $this->getFilterParameters( $_GET);
        }
    }

    public function getFilterParameters( $data){
        if(isset($data['searchTitle'])) {
            $this->searchTitle = $data['searchTitle'];
        }

        if(isset($data['searchCategory'])) {
            $this->searchCategory = $data['searchCategory'];
        }
        if(isset($data['searchOrder'])) {
            $this->searchOrder = $data['searchOrder'];
        }
        if(isset($data['searchLock'])) {
            $this->searchLock = $data['searchLock'];
        }
    }

    public function deleteAttachment($attachmentId)
    {
        $result = array('result' => false);

        $attachmentService = FRMTICKETING_BOL_TicketAttachmentService::getInstance();
        $lang = OW::getLanguage();

        $attachment = $attachmentService->findTicketAttachmentById($attachmentId);

        if ( $attachment )
        {
            $userId = OW::getUser()->getId();
            $isModerator = OW::getUser()->isAuthorized('frmticketing');
            $isOwner = false;
            if ($attachment->entityType == 'ticket'){
                $ticket = $this->ticketDao->findById($attachment->entityId);
                if (isset($ticket)){
                    $isOwner = $ticket->userId == $userId;
                }
            }
            else if ($attachment->entityType == 'post'){
                $post = $this->ticketPostDao->findById($attachment->entityId);
                if (isset($post)){
                    $isOwner = $post->userId == $userId;
                }
            }
            if ( $isModerator || $isOwner )
                {
                    $attachmentService->deleteAttachment($attachment->id);

                    $result = array('result' => true, 'msg' => $lang->text('frmticketing', 'attachment_deleted'));
                }
        }
        else
        {
            $result = array('result' => false);
        }

        return (json_encode($result));
    }

}