<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 7/2/18
 * Time: 10:23 AM
 */

class FRMTECHUNIT_CLASS_DbInit
{
    /**
     * Singleton instance.
     *
     * @var FRMTECHUNIT_CLASS_DbInit
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTECHUNIT_CLASS_DbInit
     */
    public static function getInstance()
    {
        if (self::$classInstance === null) {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function init()
    {
        foreach (FRMTECHUNIT_BOL_Service::getInstance()->sections as $sectionArray) {
            $sectionObject = FRMTECHUNIT_BOL_SectionDao::getInstance()->findByName($sectionArray['name']);
            if (isset($sectionObject))
                continue;
            if (!OW_Language::getInstance()->valueExist('frmtechunit', $sectionArray['title']))
                continue;
            $section = new FRMTECHUNIT_BOL_Section();
            $section->required = $sectionArray['required'];
            $section->name = $sectionArray['name'];
            $section->title = OW_Language::getInstance()->text('frmtechunit', $sectionArray['title']);
            FRMTECHUNIT_BOL_SectionDao::getInstance()->save($section);
        }
    }
}