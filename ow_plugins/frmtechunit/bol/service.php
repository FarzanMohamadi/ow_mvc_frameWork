<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 7/1/18
 * Time: 1:26 PM
 */

class FRMTECHUNIT_BOL_Service
{

    /**
     * Singleton instance.
     *
     * @var FRMTECHUNIT_BOL_Service
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTECHUNIT_BOL_Service
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    const INTRO = 'intro';
    const FOUNDER = 'founder';
    const PEOPLE = 'people';
    const GOALS = 'goals';
    const ACTIVITIES = 'activities';
    const CONTEXTS = 'contexts';
    const PROJECTS_PRODUCTS = 'projects_products';
    const CO_COMPANIES = 'co_companies';
    const DEPARTMENTS = 'departments';

    public $sections = array(
        array(
            'name' => 'intro',
            'title' => 'section_intro',
            'required' => true,
        ),
        array(
            'name' => 'founder',
            'title' => 'section_founder',
            'required' => false,
        ),
        array(
            'name' => 'people',
            'title' => 'section_people',
            'required' => false,
        ),
        array(
            'name' => 'contexts',
            'title' => 'section_contexts',
            'required' => false,
        ),
        array(
            'name' => 'activities',
            'title' => 'section_activities',
            'required' => false,
        ),
        array(
            'name' => 'goals',
            'title' => 'section_goals',
            'required' => false,
        ),
        array(
            'name' => 'departments',
            'title' => 'section_departments',
            'required' => false,
        ),
        array(
            'name' => 'co_companies',
            'title' => 'section_co_companies',
            'required' => false,
        ),
        array(
            'name' => 'projects_products',
            'title' => 'section_projects_products',
            'required' => false,
        ),
    );

    public function addImage($imagePath)
    {
        $imageId = FRMSecurityProvider::generateUniqueId();
        $storage = OW::getStorage();

        if ($storage->fileExists($this->generateImagePath($imageId))) {
            $storage->removeFile($this->generateImagePath($imageId));
            $storage->removeFile($this->generateImagePath($imageId));
        }

        $pluginfilesDir = OW::getPluginManager()->getPlugin('frmtechunit')->getPluginFilesDir();

        $tmpImgPath = $pluginfilesDir . 'img_' . FRMSecurityProvider::generateUniqueId() . '.jpg';

        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('source' => $imagePath, 'destination' => $tmpImgPath)));
        if (isset($checkAnotherExtensionEvent->getData()['destination'])) {
            $tmpImgPath = $checkAnotherExtensionEvent->getData()['destination'];
        }

        $image = new UTIL_Image($imagePath);
        $image->resizeImage(400, 400, true)->saveImage($tmpImgPath);

        $storage->copyFile($tmpImgPath, $this->generateImagePath($imageId));

        OW::getStorage()->removeFile($imagePath);
        OW::getStorage()->removeFile($tmpImgPath);
        return $imageId;
    }

    public function generateImagePath($imageId)
    {
        $imagesDir = OW::getPluginManager()->getPlugin('frmtechunit')->getUserFilesDir();
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $imagesDir . 'frmtechunit_image_' . $imageId)));
        if (isset($checkAnotherExtensionEvent->getData()['ext'])) {
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        return $imagesDir . 'frmtechunit_image_' . $imageId . $ext;
    }

    public function getImageFileName(FRMTECHUNIT_BOL_Unit $unit, $image)
    {
        if ($unit == null) {
            return null;
        }
        $imagesDir = OW::getPluginManager()->getPlugin('frmtechunit')->getUserFilesDir();
        $ext = '.jpg';
        $checkAnotherExtensionEvent = OW::getEventManager()->trigger(new OW_Event(FRMEventManager::ON_BEFORE_PHOTO_TEMPORARY_PATH_RETURN, array('fullPath' => $imagesDir . 'frmtechunit_image_' . $unit->image)));
        if (isset($checkAnotherExtensionEvent->getData()['ext'])) {
            $ext = $checkAnotherExtensionEvent->getData()['ext'];
        }
        return 'frmtechunit_image_' . $image . $ext;
    }

    public function getImageUrl(FRMTECHUNIT_BOL_Unit $unit, $image, $returnPath = false)
    {
        $noPictureUrl = OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl() . 'no-picture.png';
        if ($unit == null || !isset($image)) {
            return $noPictureUrl;
        }
        $path = $this->getImagePath($unit, $image);

        return empty($path) ? $noPictureUrl : OW::getStorage()->getFileUrl($path, $returnPath);
    }

    public function getImagePath(FRMTECHUNIT_BOL_Unit $unit, $image)
    {
        if ($unit == null) {
            return null;
        }
        $fileName = $this->getImageFileName($unit, $image);

        return empty($fileName) ? null : OW::getPluginManager()->getPlugin('frmtechunit')->getUserFilesDir() . $fileName;
    }

    public function saveUnit($name, $manager, $image, $qr_code, $address, $phone, $email, $website, $sections)
    {
        $unit = new FRMTECHUNIT_BOL_Unit();
        $unit->name = $name;
        $unit->manager = $manager;
        $unit->image = $image;
        $unit->qr_code = $qr_code;
        $unit->address = $address;
        $unit->phone = $phone;
        $unit->email = $email;
        $unit->website = $website;
        $unit->timestamp = time();
        FRMTECHUNIT_BOL_UnitDao::getInstance()->save($unit);
        foreach ($sections as $key => $content) {
            if (empty(trim($content)))
                continue;
            $unitSection = new FRMTECHUNIT_BOL_UnitSection();
            $unitSection->unitId = $unit->id;
            $unitSection->sectionId = $key;
            $unitSection->content = $content;
            FRMTECHUNIT_BOL_UnitSectionDao::getInstance()->save($unitSection);
        }
    }

    public function editUnit($id, $name, $manager, $image,$deleteImage, $qr_code,$deleteQr, $address, $phone, $email, $website, $sections)
    {
        $unit = FRMTECHUNIT_BOL_UnitDao::getInstance()->findById($id);
        $unit->name = $name;
        $unit->manager = $manager;
        if ($deleteImage)
            $unit->image = null;
        if ($deleteQr)
            $unit->qr_code = null;
        $unit->address = $address;
        if (isset($image))
            $unit->image = $image;
        if (isset($qr_code))
            $unit->qr_code = $qr_code;
        $unit->address = $address;
        $unit->phone = $phone;
        $unit->email = $email;
        $unit->website = $website;
        $unit->timestamp = time();
        FRMTECHUNIT_BOL_UnitDao::getInstance()->save($unit);
        FRMTECHUNIT_BOL_UnitSectionDao::getInstance()->deleteUnitSectionsByUnit($unit->id);
        foreach ($sections as $key => $content) {
            if (empty(trim($content)))
                continue;
            $unitSection = new FRMTECHUNIT_BOL_UnitSection();
            $unitSection->unitId = $unit->id;
            $unitSection->sectionId = $key;
            $unitSection->content = $content;
            FRMTECHUNIT_BOL_UnitSectionDao::getInstance()->save($unitSection);
        }
    }

    /***
     * @param $list
     */
    public function savePageOrdered($list){
        OW::getConfig()->saveConfig('frmtechunit', 'orders', json_encode($list));
    }

    /***
     * @param $list
     */
    public function resetPageOrdered(){
        $list = $sections = FRMTECHUNIT_BOL_SectionDao::getInstance()->findIdListByExample(new OW_Example());
        if(!OW::getConfig()->configExists('frmtechunit','orders'))
        {
            OW::getConfig()->saveConfig('frmtechunit', 'orders', json_encode($list));
        }
        else {
            $orderedList = json_decode(OW::getConfig()->getValue('frmtechunit','orders'));
            $finalList = array();
            foreach($list as $item){
                if(!in_array($item,$orderedList)){
                    $finalList[] = $item->id;
                }
            }
            $deleteList = array();
            foreach($orderedList as $key => $item){
                if(!in_array($item,$list)){
                    $deleteList[] = $item;
                }
            }
            $orderedList = array_diff($orderedList,$deleteList);
            $orderedList = array_merge($orderedList,$finalList);
            OW::getConfig()->saveConfig('frmtechunit', 'orders', json_encode($orderedList));
        }
    }

    public function loadUnits($page, $count)
    {
        return FRMTECHUNIT_BOL_UnitDao::getInstance()->findAllOrderByTime($page, $count);
    }

    public function searchUnits($query, $first, $count)
    {
        return FRMTECHUNIT_BOL_UnitDao::getInstance()->search($query,$first, $count);
    }


    public function hasAddAccess()
    {
        return (OW_User::getInstance()->isAuthenticated()) && (OW_User::getInstance()->isAdmin() || OW_User::getInstance()->isAuthorized('frmtechunit', 'add'));
    }

    public function hasViewAccess()
    {
        return (OW_User::getInstance()->isAdmin() || OW_User::getInstance()->isAuthorized('frmtechunit', 'view'));
    }
}