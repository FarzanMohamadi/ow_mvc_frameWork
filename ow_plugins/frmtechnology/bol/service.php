<?php
class FRMTECHNOLOGY_BOL_Service
{
    private static $classInstance;
    private $configs = array();
    const FRMTECHNOLOGY_BEFORE_IMAGE_UPDATE = 'frmtechnology_before_image_update';
    const CONF_TECHNOLOGIES_COUNT_ON_PAGE = 'technologies_count_on_page';
    const CONF_ORDERS_COUNT_ON_PAGE = 'orders_count_on_page';
//    const WCIS_MEMBERS = 'members';
//    const WCIS_CREATOR = 'creator';
    const STATUS_ACTIVE = 'active';
    const STATUS_DEACTIVATE = 'deactivate';


    public static function getInstance()
    {
        if (null === self::$classInstance) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $technologyDao;
    private $supporterDao;
    private $orderDao;

    protected function __construct()
    {
        $this->technologyDao = FRMTECHNOLOGY_BOL_TechnologyDao::getInstance();
        $this->supporterDao = FRMTECHNOLOGY_BOL_SupporterDao::getInstance();
        $this->orderDao = FRMTECHNOLOGY_BOL_OrderDao::getInstance();
        $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE] = 9;
        $this->configs[self::CONF_ORDERS_COUNT_ON_PAGE] = 2;
    }

    public function getConfigs()
    {
        return $this->configs;
    }

    public function saveTechnology(FRMTECHNOLOGY_BOL_Technology $technology)
    {
        $this->technologyDao->save($technology);
    }

    public function deleteTechnology($technologyId)
    {

        $this->technologyDao->deleteById($technologyId);
    }

    public function deleteOrder($orderId)
    {

        $this->orderDao->deleteById($orderId);
    }

    public function findTechnologyById($technologyId)
    {
        return $this->technologyDao->findById((int)$technologyId);
    }

    public function findTechnologiesByFiltering($searchTitle, $page)
    {
        if ($page === null) {
            $first = 0;
            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
        } else {
            $page = ($page === null) ? 1 : (int)$page;
            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
            $first = ($page - 1) * $count;
        }
        return $this->technologyDao->findTechnologiesByFiltering($searchTitle, $first, $count);
    }

    public function findTechnologiesByFilteringTag($searchTag, $page)
    {
        $list = array();
        if ($page === null) {
            $first = 0;
            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
        } else {
            $page = ($page === null) ? 1 : (int)$page;
            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
            $first = ($page - 1) * $count;
        }
        $info = $this->technologyDao->getInstance()->findTechnologyListByTag($searchTag, $first, $count);

        foreach ($info as $item) {
            $dtoList[] = $this->findTechnologyById($item);
        }

        if (empty($dtoList)) {
            return;
        }

        function sortByTimestamp($post1, $post2)
        {
            return $post1->timeStamp < $post2->timeStamp;
        }

        usort($dtoList, 'sortByTimestamp');

        foreach ($dtoList as $dto) {
            $list[] = $dto;
        }
        return $list;
    }

    public function findTechnologiesByFilteringCount($searchTag)
    {
        return $this->technologyDao->getInstance()->findTechnologyCountByTag($searchTag);
    }

    public function findTechnologiesByFilteringTagCount($searchTag)
    {
        return $this->technologyDao->getInstance()->findTechnologyCountByTag($searchTag);
    }

    public function isCurrentUserCanEdit(FRMTECHNOLOGY_BOL_Technology $technology)
    {
//        $isManager=false;
//        $eventIisGroupsPlusManager = new OW_Event('frmgroupsplus.check.user.manager.status', array('groupId'=>$group->getId()));
//        OW::getEventManager()->trigger($eventIisGroupsPlusManager);
//        if(isset($eventIisGroupsPlusManager->getData()['isUserManager'])){
//            $isManager=$eventIisGroupsPlusManager->getData()['isUserManager'];
//        }
//        return $group->userId == OW::getUser()->getId() || OW::getUser()->isAuthorized('groups') || $isManager==true;
    }

//    public function isCurrentUserCanAdd()
//    {
//        return OW::getUser()->isAuthorized('frmtechnology', 'add_technology');
//    }

//    public function isCurrentUserCanView()
//    {
//
//        if ( OW::getUser()->isAuthorized('frmtechnology','view_technology') )
//        {
//            return true;
//        }
//
//        return false;
//
//    }


    public function findAllTechnologiesList()
    {
        $technologies = $this->technologyDao->findAll();

        $out = array();

        return $out;
    }

    public function generateImageUrl($imageId, $icon = true)
    {
        return OW::getStorage()->getFileUrl($this->generateImagePath($imageId, $icon));
    }

    public function generateImagePath($imageId, $icon = true)
    {
        $imagesDir = OW::getPluginManager()->getPlugin('frmtechnology')->getUserFilesDir();
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $imagesDir . ($icon ? 'frmtechnology_icon_' : 'frmtechnology_image_') . $imageId)));
        if (isset($checkAnotherExtensionEvent->getData()['ext'])) {
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        return $imagesDir . ($icon ? 'frmtechnology_icon_' : 'frmtechnology_image_') . $imageId . $ext;
    }

    public function generateDefaultImageUrl()
    {
        return OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'no-picture.png';
    }

    public function saveTechnologyImage($tmpPath, $imageId)
    {
        $event = new OW_Event(self::FRMTECHNOLOGY_BEFORE_IMAGE_UPDATE, array(
            "tmpPath" => $tmpPath,
            "eventId" => $imageId
        ), array(
            "tmpPath" => $tmpPath
        ));
        OW::getEventManager()->trigger($event);
        $data = $event->getData();
        $imagePath = $data["tmpPath"];

        $storage = OW::getStorage();

        if ($storage->fileExists($this->generateImagePath($imageId))) {
            $storage->removeFile($this->generateImagePath($imageId));
            $storage->removeFile($this->generateImagePath($imageId, false));
        }

        $pluginfilesDir = OW::getPluginManager()->getPlugin('frmtechnology')->getPluginFilesDir();

        $tmpImgPath = $pluginfilesDir . 'img_' . FRMSecurityProvider::generateUniqueId() . '.jpg';
        $tmpIconPath = $pluginfilesDir . 'icon_' . FRMSecurityProvider::generateUniqueId() . '.jpg';

        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('source' => $imagePath, 'destination' => $tmpImgPath)));
        if (isset($checkAnotherExtensionEvent->getData()['destination'])) {
            $tmpImgPath = $checkAnotherExtensionEvent->getData()['destination'];
        }

        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('source' => $imagePath, 'destination' => $tmpIconPath)));
        if (isset($checkAnotherExtensionEvent->getData()['destination'])) {
            $tmpIconPath = $checkAnotherExtensionEvent->getData()['destination'];
        }

        $image = new UTIL_Image($imagePath);
        $image->resizeImage(400, null)->saveImage($tmpImgPath)
            ->resizeImage(100, 100, true)->saveImage($tmpIconPath);

        $storage->copyFile($tmpIconPath, $this->generateImagePath($imageId));
        $storage->copyFile($tmpImgPath, $this->generateImagePath($imageId, false));

//        OW::getEventManager()->trigger(new OW_Event(self::FRMTECH_AFTER_IMAGE_UPDATE, array(
//            "tmpPath" => $tmpPath,
//            "eventId" => $imageId
//        )));

        OW::getStorage()->removeFile($imagePath);
        OW::getStorage()->removeFile($tmpImgPath);
        OW::getStorage()->removeFile($tmpIconPath);
    }

    public function findTechnologies($page, $technologiesCount = null)
    {
        if ($page === null) {
            $first = 0;
            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
        } else {
            $page = ($page === null) ? 1 : (int)$page;
            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
            $first = ($page - 1) * $count;
        }

        return $this->technologyDao->findTechnologies($first, $count);
    }

    public function findAllTechnologies()
    {
        return $this->technologyDao->findAll();
    }

    public function findTechnologiesCount()
    {
        return $this->technologyDao->findTechnologiesCount();
    }

    public function getListingDataTechnology(array $technologies)
    {
        $resultArray = array();

        foreach ($technologies as $technologyItem) {
            //$title = '';
            if ($technologyItem->getImage1()) {
                $imgSrc = $this->generateImageUrl($technologyItem->getImage1(), false);
            } else if ($technologyItem->getImage2()) {
                $imgSrc = $this->generateImageUrl($technologyItem->getImage2(), false);
            } else if ($technologyItem->getImage3()) {
                $imgSrc = $this->generateImageUrl($technologyItem->getImage3(), false);
            } else if ($technologyItem->getImage4()) {
                $imgSrc = $this->generateImageUrl($technologyItem->getImage4(), false);
            } else if ($technologyItem->getImage5()) {
                $imgSrc = $this->generateImageUrl($technologyItem->getImage5(), false);
            } else {
                $imgSrc = $this->generateDefaultImageUrl();
            }
            if ($technologyItem->getStatus() == self::STATUS_ACTIVE) {
                $title = UTIL_HtmlTag::stripTagsAndJs($technologyItem->getTitle());
            } else {
                $title = '';
            }
            $resultArray[$technologyItem->getId()] = array(
                'title' => $title,
                'content' => UTIL_HtmlTag::stripTagsAndJs($technologyItem->getDescription()),
                'url' => OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $technologyItem->getId())),
                'imageSrc' => $imgSrc,
                'imageTitle' => $title
            );
        }

        return $resultArray;
    }

    public function getListingDataDeactivateTechnology(array $technologies)
    {
        $resultArray = array();

        foreach ($technologies as $technologyItem) {
            //$title = '';
            if ($technologyItem->getImage1()) {
                $imgSrc = $this->generateImageUrl($technologyItem->getImage1(), false);
            } else if ($technologyItem->getImage2()) {
                $imgSrc = $this->generateImageUrl($technologyItem->getImage2(), false);
            } else if ($technologyItem->getImage3()) {
                $imgSrc = $this->generateImageUrl($technologyItem->getImage3(), false);
            } else if ($technologyItem->getImage4()) {
                $imgSrc = $this->generateImageUrl($technologyItem->getImage4(), false);
            } else if ($technologyItem->getImage5()) {
                $imgSrc = $this->generateImageUrl($technologyItem->getImage5(), false);
            } else {
                $imgSrc = $this->generateDefaultImageUrl();
            }
            if ($technologyItem->getStatus() == self::STATUS_DEACTIVATE) {
                $title = UTIL_HtmlTag::stripTagsAndJs($technologyItem->getTitle());
            } else {
                $title = '';
            }
            $resultArray[$technologyItem->getId()] = array(
                'title' => $title,
                'content' => UTIL_HtmlTag::stripTagsAndJs($technologyItem->getDescription()),
                'url' => OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $technologyItem->getId())),
                'imageSrc' => $imgSrc,
                'imageTitle' => $title
            );
        }

        return $resultArray;
    }

    public function getListingDataWithToolbarTechnology(array $technologies)
    {
        $resultArray = $this->getListingDataTechnology($technologies);
        foreach ($technologies as $technologyItem) {
            $resultArray[$technologyItem->getId()]['toolbar'][] = array('label' => OW::getLanguage()->text('frmtechnology', 'technology_release_date_list', array('date' => UTIL_DateTime::formatSimpleDate($technologyItem->getTimeStamp(), true))), 'class' => 'ow_ipc_date');
        }
        return $resultArray;
    }

    public function getListingDataWithToolbarDeactivateTechnology(array $technologies)
    {
        $resultArray = $this->getListingDataDeactivateTechnology($technologies);
        foreach ($technologies as $technologyItem) {
            $resultArray[$technologyItem->getId()]['toolbar'][] = array('label' => OW::getLanguage()->text('frmtechnology', 'technology_release_date_list', array('date' => UTIL_DateTime::formatSimpleDate($technologyItem->getTimeStamp(), true))), 'class' => 'ow_ipc_date');
        }
        return $resultArray;
    }

    public function saveOrder(FRMTECHNOLOGY_BOL_Order $order)
    {
        $this->orderDao->save($order);
    }

    public function findOrders($page)
    {
        if ($page === null) {
            $first = 0;
            $count = $this->configs[self::CONF_ORDERS_COUNT_ON_PAGE];
        } else {
            $page = ($page === null) ? 1 : (int)$page;
            $count = $this->configs[self::CONF_ORDERS_COUNT_ON_PAGE];
            $first = ($page - 1) * $count;
        }
        return $this->orderDao->findOrders($first, $count);
    }

    public function findOrdersCount()
    {
        return $this->orderDao->findOrdersCount();

    }

    public function getListingDataOrder(array $orders)
    {
        $resultArray = array();

        foreach ($orders as $orderItem) {
            $name = UTIL_String::truncate(strip_tags($orderItem['name']), 300, "...");
            $description = UTIL_String::truncate(strip_tags($orderItem['description']), 600, "...");
            $resultArray[$orderItem['id']] = array(
                'title' => OW::getLanguage()->text('frmtechnology', 'order_requester', array('name' => $name)),
                'content' => $description,
                'url' => OW::getRouter()->urlForRoute('frmtechnology.orderView', array('orderId' => $orderItem['id'])),
                'imageSrc' => $this->generateDefaultImageUrl(),
                'imageTitle' => ''
            );
        }

        return $resultArray;
    }

    public function getListingDataWithToolbarOrder(array $orders)
    {
        $resultArray = $this->getListingDataOrder($orders);
        $technologies = array();
        $urls = array();

        foreach ($orders as $order) {
            if ($order['technologyStatus'] == FRMTECHNOLOGY_BOL_Service::STATUS_ACTIVE) {
                $title = UTIL_String::truncate(strip_tags($order['technologyTitle']), 300, "...");
            } else {
                $title = OW::getLanguage()->text('frmtechnology', 'deactivate_technology_title', array('title' => UTIL_String::truncate(strip_tags($order['technologyTitle']), 300, "...")));
            }
            $technologies[$order['id']] = $title;
            $urls[$order['id']] = OW::getRouter()->urlForRoute('frmtechnology.view', array('technologyId' => $order['technologyId']));
        }


        $language = OW::getLanguage();
        foreach ($orders as $orderItem) {
            $resultArray[$orderItem['id']]['toolbar'][] = array('label' => $language->text('frmtechnology', 'order_technology', array('technology' => $technologies[$orderItem['id']])), 'href' => $urls[$orderItem['id']], 'class' => 'ow_icon_control ow_ic_user');
            $resultArray[$orderItem['id']]['toolbar'][] = array('label' => $language->text('frmtechnology', 'order_submit_date_list', array('date' => UTIL_DateTime::formatSimpleDate($orderItem['timeStamp'], true))), 'class' => 'ow_ipc_date');
        }
        return $resultArray;
    }

    public function findOrderById($orderId)
    {
        return $this->orderDao->findById((int)$orderId);
    }

    public function findOrdersByTechnologyId($technologyId, $page)
    {
        if ($page === null) {
            $first = 0;
            $count = $this->configs[self::CONF_ORDERS_COUNT_ON_PAGE];
        } else {
            $page = ($page === null) ? 1 : (int)$page;
            $count = $this->configs[self::CONF_ORDERS_COUNT_ON_PAGE];
            $first = ($page - 1) * $count;
        }
        return $this->orderDao->findOrdersByTechnologyId($technologyId, $first, $count);
    }
//    public function findSupporterList($technologyId)
//    {
//        return $this->supporterDao->findByTechnologyId($technologyId);
//    }
//    public function inviteSupporter( $technologyId, $userId )
//    {
//        $supporter = new FRMTECHNOLOGY_BOL_Supporter();
//        $supporter->setTechnologyId($technologyId);
//        $supporter->setUserId($userId);
//        $this->supporterDao->save($supporter);
//    }

//    public function findSupporterListLimited( $technologyId, $first, $count )
//    {
//        $groupUserList = $this->supporterDao->findByTechnologyIdLimited($technologyId, $first, $count);
//        $idList = array();
//        foreach ( $groupUserList as $groupUser )
//        {
//            $idList[] = $groupUser->userId;
//        }
//
//        return BOL_UserService::getInstance()->findUserListByIdList($idList);
//    }
//    public function deleteSupporter( $technologyId)
//    {
//        $this->supporterDao->deleteByUserIdAndTechnologyId($technologyId ,OW::getUser()->getId() );
//    }

//    public function findMyTechnologies( $page, $technologiesCount = null)
//    {
//        if ( $page === null )
//        {
//            $first = 0;
//            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
//        }
//        else
//        {
//            $page = ( $page === null ) ? 1 : (int) $page;
//            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
//            $first = ( $page - 1 ) * $count;
//        }
//
//        return $this->technologyDao->findMyTechnologies($first, $count);
//    }
//    public function findMyTechnologiesCount()
//    {
//        return $this->technologyDao->findMyTechnologiesCount();
//    }
    public function findTechnologiesOrderedList($page, $technologiesCount = null)
    {
        if ($page === null) {
            $first = 0;
            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
        } else {
            $page = ($page === null) ? 1 : (int)$page;
            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
            $first = ($page - 1) * $count;
        }

        return $this->technologyDao->findOrderedList($first, $count);
    }

    public function findDeactivateTechnologiesOrderedList($page, $technologiesCount = null)
    {
        if ($page === null) {
            $first = 0;
            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
        } else {
            $page = ($page === null) ? 1 : (int)$page;
            $count = $this->configs[self::CONF_TECHNOLOGIES_COUNT_ON_PAGE];
            $first = ($page - 1) * $count;
        }

        return $this->technologyDao->findDeactivateOrderedList($first, $count);
    }

    public function findTechnologiesOrderedListCount()
    {
        return $this->technologyDao->findOrderedListCount();
    }

    public function findDeactivateTechnologiesOrderedListCount()
    {
        return $this->technologyDao->findDeactivateOrderedListCount();
    }

    public function deactivateTechnology($technologyId)
    {
        $technology = $this->technologyDao->findById((int)$technologyId);
        $technology->setStatus(self::STATUS_DEACTIVATE);
        $this->technologyDao->save($technology);
    }

    public function updateTechnologyStatus($technologyId, $status)
    {
        $technology = $this->technologyDao->findById((int)$technologyId);
        $technology->setStatus($status);
        $this->technologyDao->save($technology);
    }

    public function findOrderCountByTechnologyId($technologyId)
    {
        return $this->orderDao->findOrderCountByTechnologyId($technologyId);
    }

    public function findUserIdByAuthorizationAction($actionName)
    {
        $query = "
			SELECT `aur`.`userId`
			FROM `" . OW_DB_PREFIX . "base_authorization_user_role` AS `aur`
			INNER JOIN `" . OW_DB_PREFIX . "base_authorization_permission` AS `ap` ON `ap`.`roleId` = `aur`.`roleId`
			INNER JOIN `" . OW_DB_PREFIX . "base_authorization_action` AS `aa` ON `aa`.`id` = `ap`.`actionId`
			WHERE `aa`.`name` = :actionName
    	";
        $idList = OW::getDbo()->queryForList($query, array(':actionName' => $actionName));
        return $idList;
    }
}