<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 7/2/18
 * Time: 10:24 AM
 */

class FRMTECHUNIT_CLASS_UrlMapping
{
    /**
     * Singleton instance.
     *
     * @var FRMTECHUNIT_CLASS_UrlMapping
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return FRMTECHUNIT_CLASS_UrlMapping
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function init(){
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.units', 'tech_units', "FRMTECHUNIT_CTRL_Unit", 'index'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.search', 'tech_units/search', "FRMTECHUNIT_CTRL_Unit", 'index'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.add_unit', 'tech_units/add_unit', "FRMTECHUNIT_CTRL_Unit", 'addUnit'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.edit_unit', 'tech_units/edit_unit/:id', "FRMTECHUNIT_CTRL_Unit", 'editUnit'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.delete_unit', 'tech_units/delete_unit/:id', "FRMTECHUNIT_CTRL_Unit", 'deleteUnit'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.unit', 'tech_units/unit/:id', "FRMTECHUNIT_CTRL_Unit", 'unit'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.admin', 'tech_units/admin', "FRMTECHUNIT_CTRL_Admin", 'index'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.admin.edit', 'tech_units/admin/edit/:id', "FRMTECHUNIT_CTRL_Admin", 'edit'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.admin.delete', 'tech_units/admin/delete/:id', "FRMTECHUNIT_CTRL_Admin", 'delete'));
    }

    public function initMobile(){
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.units', 'tech_units', "FRMTECHUNIT_MCTRL_Unit", 'index'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.search', 'tech_units/search', "FRMTECHUNIT_MCTRL_Unit", 'index'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.add_unit', 'tech_units/add_unit', "FRMTECHUNIT_MCTRL_Unit", 'addUnit'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.edit_unit', 'tech_units/edit_unit/:id', "FRMTECHUNIT_MCTRL_Unit", 'editUnit'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.delete_unit', 'tech_units/delete_unit/:id', "FRMTECHUNIT_MCTRL_Unit", 'deleteUnit'));
        OW::getRouter()->addRoute(new OW_Route('frmtechunit.unit', 'tech_units/unit/:id', "FRMTECHUNIT_MCTRL_Unit", 'unit'));
    }
}