<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * @package ow_plugins.frmcfp.controllers
 * @since 1.0
 */
class FRMCFP_CTRL_Base extends OW_ActionController
{
    /**
     * @var FRMCFP_BOL_Service
     */
    private $eventService;

    public function __construct()
    {
        parent::__construct();
        $this->eventService = FRMCFP_BOL_Service::getInstance();
    }

    public function index()
    {
        $this->redirect(OW::getRouter()->urlForRoute('frmcfp.view_event_list', array('list' =>'latest')));
    }
    /**
     * Add new event controller
     */
    public function add()
    {
        $language = OW::getLanguage();
        $this->setPageTitle($language->text('frmcfp', 'add_page_title'));
        $this->setPageHeading($language->text('frmcfp', 'add_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_add');

        OW::getDocument()->setDescription(OW::getLanguage()->text('frmcfp', 'add_event_meta_description'));

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmcfp', 'main_menu_item');

        // check permissions for this page
        if ( !OW::getUser()->isAuthenticated() || (!OW::getUser()->isAuthorized('frmcfp', 'add_event') && !OW::getUser()->isAuthorized('frmcfp') && !OW::getUser()->isAdmin() ))
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmcfp', 'add_event');
            throw new AuthorizationException($status['msg']);
        }

        $form = $this->eventService->getAddForm('event_add');

        if ( date('n', time()) == 12 && date('j', time()) == 31 )
        {
            $defaultDate = (date('Y', time()) + 1) . '/1/1';
        }
        else if ( ( date('j', time()) + 1 ) > date('t') )
        {
            $defaultDate = date('Y', time()) . '/' . ( date('n', time()) + 1 ) . '/1';
        }
        else
        {
            $defaultDate = date('Y', time()) . '/' . date('n', time()) . '/' . ( date('j', time()) + 1 );
        }

        $form->getElement('start_date')->setValue($defaultDate);
        $form->getElement('end_date')->setValue($defaultDate);
        $form->getElement('start_time')->setValue('all_day');
        $form->getElement('end_time')->setValue('all_day');

        $checkboxId = UTIL_HtmlTag::generateAutoId('chk');
        $tdId = UTIL_HtmlTag::generateAutoId('td');
        $this->assign('tdId', $tdId);
        $this->assign('chId', $checkboxId);

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();
                $event = FRMCFP_BOL_Service::getInstance()->createEvent($data);
                if ( isset($event) )
                {
                    OW::getFeedback()->info($language->text('frmcfp', 'add_form_success_message'));
                    $this->redirect(OW::getRouter()->urlForRoute('frmcfp.view', array('eventId' => $event->getId())));
                }
            }
        }

        $this->addForm($form);
    }

    /**
     * Get event by params(eventId)
     *
     * @param array $params
     * @return FRMCFP_BOL_Event
     * @throws Redirect404Exception
     */
    private function getEventForParams( $params )
    {
        if ( empty($params['eventId']) )
        {
            throw new Redirect404Exception();
        }

        $event = $this->eventService->findEvent($params['eventId']);

        if ( $event === null )
        {
            throw new Redirect404Exception();
        }

        return $event;
    }

    /**
     * @param $params
     * @throws Redirect404Exception
     */
    public function edit( $params )
    {
        if( !OW::getUser()->isAuthenticated() )
        {
            throw new Redirect404Exception();
        }

        $userId = OW::getUser()->getId();
        $isModerator = OW::getUser()->isAuthorized('frmcfp');
        $event = $this->getEventForParams($params);

        if( $userId != $event->userId && !OW::getUser()->isAdmin() &&  !$isModerator)
        {
            throw new Redirect404Exception();
        }

        $language = OW::getLanguage();
        $form = $this->eventService->getAddForm('event_edit');
        $form->getElement('title')->setValue(html_entity_decode($event->getTitle()));
        $form->getElement('desc')->setValue($event->getDescription());
        $form->getElement('who_can_view')->setValue($event->getWhoCanView());
        $form->getElement('file_disabled')->setValue($event->getFileDisabled());
        $form->getElement('file_note')->setValue($event->getFileNote());

        $startTimeArray = array('hour' => date('G', $event->getStartTimeStamp()), 'minute' => date('i', $event->getStartTimeStamp()));
        $form->getElement('start_time')->setValue($startTimeArray);

        $startDate = date('Y', $event->getStartTimeStamp()) . '/' . date('n', $event->getStartTimeStamp()) . '/' . date('j', $event->getStartTimeStamp());
        $form->getElement('start_date')->setValue($startDate);

        if ( $event->getEndTimeStamp() !== null )
        {
            $endTimeArray = array('hour' => date('G', $event->getEndTimeStamp()), 'minute' => date('i', $event->getEndTimeStamp()));
            $form->getElement('end_time')->setValue($endTimeArray);


            $endTimeStamp = $event->getEndTimeStamp();
            if ( $event->getEndTimeDisable() )
            {
                $endTimeStamp = strtotime("-1 day", $endTimeStamp);
            }

            $endDate = date('Y', $endTimeStamp) . '/' . date('n', $endTimeStamp) . '/' . date('j', $endTimeStamp);
            $form->getElement('end_date')->setValue($endDate);
        }

        if ( $event->getStartTimeDisable() )
        {
            $form->getElement('start_time')->setValue('all_day');
        }

        if ( $event->getEndTimeDisable() )
        {
            $form->getElement('end_time')->setValue('all_day');
        }

        $form->getSubmitElement('submit')->setValue(OW::getLanguage()->text('frmcfp', 'edit_form_submit_label'));

        $checkboxId = UTIL_HtmlTag::generateAutoId('chk');
        $tdId = UTIL_HtmlTag::generateAutoId('td');
        $this->assign('tdId', $tdId);
        $this->assign('chId', $checkboxId);

        if ( $event->getImage() )
        {
            $this->assign('imgsrc', $this->eventService->generateImageUrl($event->getImage(), true));
        }

        if ( $event->getFile() )
        {
            $this->assign('filesrc', $this->eventService->generateFileUrl($event->getFile(), true));
        }

        if ( OW::getRequest()->isPost() )
        {
            if ( $form->isValid($_POST) )
            {
                $data = $form->getValues();

                $serviceEvent = new OW_Event(FRMCFP_BOL_Service::EVENT_BEFORE_EVENT_EDIT, array('eventId' => $event->id), $data);
                OW::getEventManager()->trigger($serviceEvent);
                $data = $serviceEvent->getData();

                $dateArray = explode('/', $data['start_date']);

                $startStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);

                if ( $data['start_time'] != 'all_day' )
                {
                    $startStamp = mktime($data['start_time']['hour'], $data['start_time']['minute'], 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                }

                $dateArray = explode('/', $data['end_date']);
                $endStamp = mktime(0, 0, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                $endStamp = strtotime("+1 day", $endStamp);

                if ( $data['end_time'] != 'all_day' )
                {
                    $hour = 0;
                    $min = 0;

                    if( $data['end_time'] != 'all_day' )
                    {
                        $hour = $data['end_time']['hour'];
                        $min = $data['end_time']['minute'];
                    }
                    $dateArray = explode('/', $data['end_date']);
                    $endStamp = mktime($hour, $min, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
                }

                $event->setStartTimeStamp($startStamp);

                if ( empty($endStamp) )
                {
                    $endStamp = strtotime("+1 day", $startStamp);
                    $endStamp = mktime(0, 0, 0, date('n',$endStamp), date('j',$endStamp), date('Y',$endStamp));
                }

                if ( $startStamp > $endStamp )
                {
                    OW::getFeedback()->error($language->text('frmcfp', 'add_form_invalid_end_date_error_message'));
                    $this->redirect();
                }

                $event->setEndTimeStamp($endStamp);

                if ( !empty($_FILES['image']['name']) )
                {
                    if ( (int) $_FILES['image']['error'] !== 0 || !is_uploaded_file($_FILES['image']['tmp_name']) || !UTIL_File::validateImage($_FILES['image']['name']) )
                    {
                        OW::getFeedback()->error($language->text('base', 'not_valid_image'));
                        $this->redirect();
                    }
                    else
                    {
                        $event->setImage(FRMSecurityProvider::generateUniqueId());
                        $this->eventService->saveEventImage($_FILES['image']['tmp_name'], $event->getImage());
                    }
                }

                if ( !empty($_FILES['file']['name']) )
                {
                    if ($this->eventService->checkIfFileValid($_FILES['file']))
                    {
                        OW::getFeedback()->error($language->text('frmcfp', 'not_valid_file'));
                        $this->redirect();
                    }
                    else
                    {
                        $newFileId = FRMSecurityProvider::generateUniqueId('file');
                        $newFileId = $newFileId . '_' . preg_replace("/[^A-Za-z0-9\.]/", '', $_FILES['file']['name']);
                        $this->eventService->saveEventFile($_FILES['file']['tmp_name'], $event->getFile(), $newFileId);
                        $event->setFile($newFileId);
                    }
                }

                $event->setTitle(UTIL_HtmlTag::stripTagsAndJs($data['title']));
                $event->setWhoCanView((int) $data['who_can_view']);
                $event->setDescription($data['desc']);
                $event->setStartTimeDisable( $data['start_time'] == 'all_day' );
                $event->setEndTimeDisable( $data['end_time'] == 'all_day' );
                $event->setFileDisabled($data['file_disabled']);
                $event->setFileNote($data['file_note']);

                $this->eventService->saveEvent($event);

                $e = new OW_Event(FRMCFP_BOL_Service::EVENT_AFTER_EVENT_EDIT, array('eventId' => $event->id));
                OW::getEventManager()->trigger($e);

                OW::getFeedback()->info($language->text('frmcfp', 'edit_form_success_message'));
                $this->redirect(OW::getRouter()->urlForRoute('frmcfp.view', array('eventId' => $event->getId())));
            }
        }

        $this->setPageHeading($language->text('frmcfp', 'edit_page_heading'));
        $this->setPageTitle($language->text('frmcfp', 'edit_page_title'));
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmcfp', 'main_menu_item');
        $this->addForm($form);
    }

    /**
     * Delete controller
     *
     * @param array $params
     * @throws Redirect404Exception
     */
    public function delete( $params )
    {
        if(!OW::getUser()->isAuthenticated())
        {
            throw new Redirect404Exception();
        }
        if(FRMSecurityProvider::checkPluginActive('frmsecurityessentials', true)) {
            $code =$_GET['code'];
            if(!isset($code)){
                throw new Redirect404Exception();
            }
            OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.check.request.manager',
                array('senderId' => OW::getUser()->getId(), 'code'=>$code,'activityType'=>'delete_event')));
        }
        $event = $this->getEventForParams($params);
        if (OW::getUser()->getId() != $event->getUserId() && !OW::getUser()->isAdmin()  && !OW::getUser()->isAuthorized('frmcfp') )
        {
            throw new Redirect404Exception();
        }

        $this->eventService->deleteEvent($event->getId());
        OW::getFeedback()->info(OW::getLanguage()->text('frmcfp', 'delete_success_message'));
        $this->redirect(OW::getRouter()->urlForRoute('frmcfp.main_menu_route'));
    }


    /**
     * @param $params
     * @throws AuthorizationException
     * @throws Redirect404Exception
     */
    public function view( $params )
    {
        $event = $this->getEventForParams($params);

        $cmpId = UTIL_HtmlTag::generateAutoId('cmp');

        $this->assign('contId', $cmpId);

        if ( !OW::getUser()->isAuthorized('frmcfp', 'view_event') && $event->getUserId() != OW::getUser()->getId() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmcfp', 'view_event');
            throw new AuthorizationException($status['msg']);
        }

        if ( $event->status != 1 && !OW::getUser()->isAuthorized('frmcfp') && $event->getUserId() != OW::getUser()->getId()  )
        {
            throw new Redirect404Exception();
        }

        // guest gan't view private events
        if ( (int) $event->getWhoCanView() === FRMCFP_BOL_Service::CAN_VIEW_INVITATION_ONLY && !OW::getUser()->isAuthenticated() )
        {
            throw new Redirect404Exception();
        }

        $eventUser = $this->eventService->findEventUser($event->getId(), OW::getUser()->getId());

        // check if user can view event
        if ( (int) $event->getWhoCanView() === FRMCFP_BOL_Service::CAN_VIEW_INVITATION_ONLY && $eventUser === null && !OW::getUser()->isAuthorized('frmcfp') )
        {
            throw new Redirect404Exception();
        }

        $buttons = array();

        $this->assign('isModerator', false);
        if ( OW::getUser()->isAuthorized('frmcfp') || OW::getUser()->getId() == $event->getUserId() )
        {
            $this->assign('isModerator', true);
            $this->assign('view_all_files', OW::getRouter()->urlForRoute('frmcfp.file-list', array('eventId' => $event->getId())));

            $code='';
            $frmSecuritymanagerEvent= OW::getEventManager()->trigger(new OW_Event('frmsecurityessentials.on.generate.request.manager',
                array('senderId'=>OW::getUser()->getId(),'receiverId'=>$event->id,'isPermanent'=>true,'activityType'=>'delete_event')));
            if(isset($frmSecuritymanagerEvent->getData()['code'])){
                $code = $frmSecuritymanagerEvent->getData()['code'];
            }
            $buttons = array(
                'edit' => array('url' => OW::getRouter()->urlForRoute('frmcfp.edit', array('eventId' => $event->getId())), 'label' => OW::getLanguage()->text('frmcfp', 'edit_button_label')),
                'delete' =>
                array(
                    'url' => OW::getRequest()->buildUrlQueryString(OW::getRouter()->urlForRoute('frmcfp.delete',
                        array('eventId' => $event->getId())),array('code'=>$code)),
                    'label' => OW::getLanguage()->text('frmcfp', 'delete_button_label'),
                    'confirmMessage' => OW::getLanguage()->text('frmcfp', 'delete_confirm_message')
                )
            );
        }
        $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ADD_LEAVE_BUTTON, array('eventId' => $event->getId(), 'creatorId' => $event->getUserId())));
        if(isset($resultsEvent->getData()['leaveButton'])) {
            $this->assign('leaveArray', $resultsEvent->getData()['leaveButton']);
        }
        $this->assign('editArray', $buttons);

        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmcfp', 'main_menu_item');

        $moderationStatus = '';

        if ( $event->status == 2  )
        {
            $moderationStatus = " <span class='ow_remark ow_small'>(".OW::getLanguage()->text('frmcfp', 'moderation_status_pending_approval').")</span>";
        }

        $this->setPageHeading($event->getTitle(). $moderationStatus);
//        $this->setPageTitle(OW::getLanguage()->text('frmcfp', 'event_view_page_heading', array('event_title' => $event->getTitle())));
        $this->setPageHeadingIconClass('ow_ic_calendar');
//        OW::getDocument()->setDescription(UTIL_String::truncate(strip_tags($event->getDescription()), 200, '...'));

        $desc = $event->getDescription();
        $stringRenderer = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_RENDER_STRING, array('string' => $desc)));
        if(isset($stringRenderer->getData()['string'])){
            $desc = ($stringRenderer->getData()['string']);
        }

        $infoArray = array(
            'id' => $event->getId(),
            'image' => ( $event->getImage() ? $this->eventService->generateImageUrl($event->getImage(), false) : null ),
            'file' => ( $event->getFile() ? $this->eventService->generateFileUrl($event->getFile()) : null ),
            'fileDownloadImg' => OW::getPluginManager()->getPlugin('base')->getStaticUrl() . 'css/images/ic_download.svg',
            'date' =>    UTIL_DateTime::formatSimpleDate($event->getStartTimeStamp(), $event->getStartTimeDisable()),
            'endDate' => UTIL_DateTime::formatSimpleDate($event->getEndTimeStamp(), $event->getEndTimeDisable()),
            'desc' => UTIL_HtmlTag::autoLink($desc),
            'title' => $event->getTitle(),
            'creatorName' => BOL_UserService::getInstance()->getDisplayName($event->getUserId()),
            'creatorLink' => BOL_UserService::getInstance()->getUserUrl($event->getUserId()),
            'loginToParticipateText' => empty($event->getFileNote())?OW::getLanguage()->text('frmcfp', 'login_to_participate'):$event->getFileNote(),
            'moderationStatus' => $event->status
        );
        $this->assign('info', $infoArray);

        // participate form
        $this->assign('uploadSection', !((bool)$event->getFileDisabled()));
        $this->assign('isOpen', ( time() > $event->getStartTimeStamp() && time() < $event->getEndTimeStamp() ));
        $this->assign('isEnded', ( time() > $event->getEndTimeStamp() ));
        $this->assign('canUpload', ( OW::getUser()->isAuthenticated() ));


        if ( $event->status == FRMCFP_BOL_Service::MODERATION_STATUS_ACTIVE )
        {
            $cmntParams = new BASE_CommentsParams('frmcfp', 'cfp');
            $cmntParams->setEntityId($event->getId());
            $cmntParams->setOwnerId($event->getUserId());
            $this->addComponent('comments', new BASE_CMP_Comments($cmntParams));
        }

        $ev = new BASE_CLASS_EventCollector(FRMCFP_BOL_Service::EVENT_COLLECT_TOOLBAR, array(
            "frmcfp" => $event
        ));

        OW::getEventManager()->trigger($ev);

        $this->assign("toolbar", $ev->getData());

        $eventFiles = new OW_Event(FRMCFP_BOL_Service::EVENT_ADD_FILE_WIDGET, array('controller' => $this, 'eventId' => $event->id));
        OW::getEventManager()->trigger($eventFiles);

        $decodedString=$event->getDescription();
        $stringDecode = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_AFTER_NEWSFEED_STATUS_STRING_READ,array('toDecode' => $decodedString)));
        if(isset($stringDecode->getData()['decodedString'])){
            $decodedString = $stringDecode->getData()['decodedString'];
        }

        $params = array(
            "sectionKey" => "frmcfp",
            "entityKey" => "eventView",
            "title" => "frmcfp+meta_title_event_view",
            "description" => "frmcfp+meta_desc_event_view",
            "keywords" => "frmcfp+meta_keywords_event_view",
            "vars" => array( "event_title" => $event->getTitle(), "event_description" => $decodedString)
        );

        OW::getEventManager()->trigger(new OW_Event("base.provide_page_meta_info", $params));
    }

    /**
     * @param $params
     * @throws AuthorizationException
     * @throws Redirect404Exception
     */
    public function eventsList( $params )
    {
        if ( empty($params['list']) )
        {
            throw new Redirect404Exception();
        }

        if ( !OW::getUser()->isAuthorized('frmcfp', 'view_event') )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmcfp', 'view_event');
            throw new AuthorizationException($status['msg']);
        }

        $configs = $this->eventService->getConfigs();
        $page = ( empty($_GET['page']) || (int) $_GET['page'] < 0 ) ? 1 : (int) $_GET['page'];

        $language = OW::getLanguage();
        $toolbarList = array();
        switch ( trim($params['list']) )
        {
            case 'created':
                if ( !OW::getUser()->isAuthenticated() )
                {
                    throw new Redirect404Exception();
                }

                $this->setPageHeading($language->text('frmcfp', 'event_created_by_me_page_heading'));
//                $this->setPageTitle($language->text('frmcfp', 'event_created_by_me_page_title'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                $events = $this->eventService->findUserEvents(OW::getUser()->getId(), $page, null);
                $eventsCount = $this->eventService->findLatestEventsCount();
                break;

            case 'latest':
                $contentMenu = FRMCFP_BOL_Service::getInstance()->getContentMenu();
                $contentMenu->setItemActive('latest');
                $this->addComponent('contentMenu', $contentMenu);
                $this->setPageHeading($language->text('frmcfp', 'latest_events_page_heading'));
//                $this->setPageTitle($language->text('frmcfp', 'latest_events_page_title'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
                OW::getDocument()->setDescription($language->text('frmcfp', 'latest_events_page_desc'));
                $events = $this->eventService->findPublicEvents($page);
                $eventsCount = $this->eventService->findPublicEventsCount();
                break;

            case 'past':
                $contentMenu = FRMCFP_BOL_Service::getInstance()->getContentMenu();
                $this->addComponent('contentMenu', $contentMenu);
                $this->setPageHeading($language->text('frmcfp', 'past_events_page_heading'));
//                $this->setPageTitle($language->text('frmcfp', 'past_events_page_title'));
                $this->setPageHeadingIconClass('ow_ic_calendar');
//                OW::getDocument()->setDescription($language->text('frmcfp', 'past_events_page_desc'));
                $events = $this->eventService->findPublicEvents($page, null, true);
                $eventsCount = $this->eventService->findPublicEventsCount(true);
                break;

            default:
                $resultsEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::GET_RESULT_FOR_LIST_ITEM_EVENT, array('list' =>trim($params['list']), 'eventController' => $this,'page'=>$page)));
                if(isset($resultsEvent->getData()['events']) && isset($resultsEvent->getData()['eventsCount'])) {
                    $url = OW::getRouter()->urlForRoute('frmcfp.view_event_list', array('list' =>trim($params['list'])));
                    $this->assign('url',$url);
                    $events = $resultsEvent->getData()['events'];
                    $eventsCount = $resultsEvent->getData()['eventsCount'];
                    $page=$resultsEvent->getData()['page'];
                }else {
                    throw new Redirect404Exception();
                }
        }

        $this->addComponent('paging', new BASE_CMP_Paging($page, ceil($eventsCount / $configs[FRMCFP_BOL_Service::CONF_EVENTS_COUNT_ON_PAGE]), 5));
        $addUrl = OW::getRouter()->urlForRoute('frmcfp.add');

        $script = '$("input.add_event_button").click(function() {
                window.location='.json_encode($addUrl).';
            });';

        if ( !OW::getUser()->isAuthorized('frmcfp', 'add_event') && !OW::getUser()->isAuthorized('frmcfp') && !OW::getUser()->isAdmin() )
        {
            $status = BOL_AuthorizationService::getInstance()->getActionStatus('frmcfp', 'add_event');

            if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
            {
                $script = '$("input.add_event_button").click(function() {
                        OW.authorizationLimitedFloatbox('.json_encode($status['msg']).');
                    });';
            }
            else if ( $status['status'] == BOL_AuthorizationService::STATUS_DISABLED )
            {
                $this->assign('noButton', true);
            }
        }

        OW::getDocument()->addOnloadScript($script);

        if ( empty($events) )
        {
            $this->assign('no_events', true);
        }

        $this->assign('listType', trim($params['list']));
        $this->assign('page', $page);
        $this->assign('events', $this->eventService->getListingDataWithToolbar($events, $toolbarList));
        $this->assign('toolbarList', $toolbarList);
        $this->assign('add_new_url', OW::getRouter()->urlForRoute('frmcfp.add'));
        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'frmcfp', 'main_menu_item');
    }

    public function privateEvent( $params )
    {
        $language = OW::getLanguage();

        $this->setPageTitle($language->text('frmcfp', 'private_page_title'));
        $this->setPageHeading($language->text('frmcfp', 'private_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_lock');

        $eventId = $params['eventId'];
        $event = $this->eventService->findEvent((int) $eventId);

        $avatarList = BOL_AvatarService::getInstance()->getDataForUserAvatars(array($event->userId));
        $displayName = BOL_UserService::getInstance()->getDisplayName($event->userId);
        $userUrl = BOL_UserService::getInstance()->getUserUrl($event->userId);

        $this->assign('event', $event);
        $this->assign('avatar', $avatarList[$event->userId]);
        $this->assign('displayName', $displayName);
        $this->assign('userUrl', $userUrl);
        $this->assign('creator', $language->text('frmcfp', 'creator'));
    }

    /**
     * Responder for event attend form
     */
    public function attendFormResponder()
    {
        if ( !OW::getRequest()->isAjax() || !OW::getUser()->isAuthenticated() )
        {
            throw new Redirect404Exception();
        }

        $userId = OW::getUser()->getId();
        $respondArray = array('messageType' => 'error');

        if ( !empty($_POST['attend_status']) && in_array((int) $_POST['attend_status'], array(1, 2, 3)) && !empty($_POST['eventId']) && $this->eventService->canUserView($_POST['eventId'], $userId) )
        {
            $event = $this->eventService->findEvent($_POST['eventId']);

            if ( $event->getEndTimeStamp() < time() )
            {
                throw new Redirect404Exception();
            }

            $eventUser = $this->eventService->findEventUser($_POST['eventId'], $userId);

            if ( $eventUser !== null && (int) $eventUser->getStatus() === (int) $_POST['attend_status'] )
            {
                $respondArray['message'] = OW::getLanguage()->text('frmcfp', 'user_status_not_changed_error');
                exit(json_encode($respondArray));
            }

           /*if ( $event->getUserId() == OW::getUser()->getId() && (int) $_POST['attend_status'] == FRMCFP_BOL_Service::USER_STATUS_NO )
            {
                $respondArray['message'] = OW::getLanguage()->text('frmcfp', 'user_status_author_cant_leave_error');
                exit(json_encode($respondArray));
            }*/

            if ( $eventUser === null )
            {
                $eventUser = new FRMCFP_BOL_EventUser();
                $eventUser->setUserId($userId);
                $eventUser->setEventId((int) $_POST['eventId']);
            }

            $eventUser->setStatus((int) $_POST['attend_status']);
            $eventUser->setTimeStamp(time());
            $this->eventService->saveEventUser($eventUser);

            $e = new OW_Event(FRMCFP_BOL_Service::EVENT_ON_CHANGE_USER_STATUS, array('eventId' => $event->id, 'userId' => $eventUser->userId));
            OW::getEventManager()->trigger($e);

            $respondArray['message'] = OW::getLanguage()->text('frmcfp', 'user_status_updated');
            $respondArray['messageType'] = 'info';
            $respondArray['currentLabel'] = OW::getLanguage()->text('frmcfp', 'user_status_label_' . $eventUser->getStatus());
            $respondArray['eventId'] = (int) $_POST['eventId'];

            if ( $eventUser->getStatus() == FRMCFP_BOL_Service::USER_STATUS_YES && $event->getWhoCanView() == FRMCFP_BOL_Service::CAN_VIEW_ANYBODY )
            {
                $eventTitle = $event->getTitle();
                $eventUrl = FRMCFP_BOL_Service::getInstance()->getEventUrl($event->getId());
                $eventEmbed = '<a href="' . $eventUrl . '">' . $eventTitle . '</a>';

                OW::getEventManager()->trigger(new OW_Event('feed.activity', array(
                        'activityType' => 'event-join',
                        'activityId' => $eventUser->getId(),
                        'entityId' => $event->getId(),
                        'entityType' => 'frmcfp',
                        'userId' => $eventUser->getUserId(),
                        'pluginKey' => 'frmcfp'
                        ), array(
                        'eventId' => $event->getId(),
                        'userId' => $eventUser->getUserId(),
                        'eventUserId' => $eventUser->getId(),
                        'string' =>  OW::getLanguage()->text('frmcfp', 'feed_actiovity_attend_string' ,  array( 'user' => $eventEmbed )),
                        'feature' => array()
                    )));
            }
        }
        else
        {
            $respondArray['message'] = OW::getLanguage()->text('frmcfp', 'user_status_update_error');
        }

        exit(json_encode($respondArray));
    }

    public function approve( $params )
    {
        $entityId = $params["eventId"];
        $entityType = FRMCFP_CLASS_ContentProvider::ENTITY_TYPE;

        $backUrl = OW::getRouter()->urlForRoute("frmcfp.view", array(
            "eventId" => $entityId
        ));

        $event = new OW_Event("moderation.approve", array(
            "entityType" => $entityType,
            "entityId" => $entityId
        ));

        OW::getEventManager()->trigger($event);

        $data = $event->getData();
        if ( empty($data) )
        {
            $this->redirect($backUrl);
        }

        if ( $data["message"] )
        {
            OW::getFeedback()->info($data["message"]);
        }
        else
        {
            OW::getFeedback()->error($data["error"]);
        }

        $this->redirect($backUrl);
    }
}
