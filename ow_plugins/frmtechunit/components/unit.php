<?php
/**
 * Created by PhpStorm.
 * User: Seyed Ismail Mirvakili
 * Date: 7/2/18
 * Time: 10:50 AM
 */

class FRMTECHUNIT_CMP_Unit extends OW_Component
{

    public $unit;

    /**
     * FRMTECHUNIT_CMP_Unit constructor.
     * @param $unit
     */
    public function __construct($unit)
    {
        parent::__construct();
        $this->unit = $unit;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $unitSections = FRMTECHUNIT_BOL_UnitSectionDao::getInstance()->getUnitSections($this->unit->id);
        if (OW::getConfig()->configExists('frmtechunit', 'orders')) {
            $orderedList = json_decode(OW::getConfig()->getValue('frmtechunit', 'orders'));
        }else{
            $orderedList = FRMTECHUNIT_BOL_SectionDao::getInstance()->findIdListByExample(new OW_Example());
        }
        $sections = array();
        foreach ($orderedList as $item) {
            foreach ($unitSections as $unitSection) {
                if($unitSection->sectionId == $item){
                    $section = FRMTECHUNIT_BOL_SectionDao::getInstance()->findById($unitSection->sectionId);
                    $sections[] = array(
                        'title' => $section->title,
                        'content' => $unitSection->content,
                    );
                }
            }
        }
        $websiteUrl = $this->unit->website;
        if (strpos($this->unit->website,'http://') === false || strpos($this->unit->website,'https://') === false){
            $websiteUrl = 'http://'.$this->unit->website;
        }
        $unitObject = array(
            'name' => $this->unit->name,
            'imagePath' => FRMTECHUNIT_BOL_Service::getInstance()->getImageUrl($this->unit,$this->unit->image),
            'manager' => $this->unit->manager,
            'address' => $this->unit->address,
            'phone' => $this->unit->phone,
            'website' => $this->unit->website,
            'websiteUrl' => $websiteUrl,
            'email' => $this->unit->email,
            'sections' => $sections
        );
        if(isset($this->unit->qr_code)){
            $unitObject['qr_code'] = FRMTECHUNIT_BOL_Service::getInstance()->getImageUrl($this->unit,$this->unit->qr_code);
        }
        $this->assign('unit',$unitObject);
        if(FRMTECHUNIT_BOL_Service::getInstance()->hasAddAccess()){
            $this->assign('edit_url',OW::getRouter()->urlForRoute('frmtechunit.edit_unit',array('id'=>$this->unit->id)));
            $this->assign('delete_url',OW::getRouter()->urlForRoute('frmtechunit.delete_unit',array('id'=>$this->unit->id)));
        }
    }
}