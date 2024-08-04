<?php
class frmPassStrengthMeterTest extends FRMTestUtilites
{
    protected function setUp()
    {
        parent::setUp();
        $this->checkRequiredPlugins(array('frmpasswordstrengthmeter'));
    }

    public function testPassStrengthMeter()
    {
        $this->webDriver->prepare();
        $this->webDriver->maximizeWindow();
        $this->url(OW_URL_HOME.'join');
        try{
            $this->waitUntilElementLoaded('byXPath',"//form[@id='joinForm']//input[@name='password']");
            $this->byXPath("//form[@id='joinForm']//input[@name='password']")->value('aaaa');
            if($this->byXPath("//td[text()='".OW::getLanguage()->text('frmpasswordstrengthmeter','strength_poor_label')."']"))
            {
                self::assertTrue(true);
            }
            else
            {
                self::assertTrue(false);
            }
        }catch (Exception $ex){
            $this->handleException($ex,'',true,false);
        }
    }

    public function tearDown()
    {
        if($this->isSkipped)
            return;
        parent::tearDown();
    }

}