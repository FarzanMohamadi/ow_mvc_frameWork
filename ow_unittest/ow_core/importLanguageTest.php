<?php
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ImportLanguageTest extends FRMUnitTestUtilites
{
    protected $fixturesDir;
    protected $langId;

    public function tearDown()
    {
        if(isset($this->langId)){
            $langService = BOL_LanguageService::getInstance();
            $langEn = $langService->findByTag('en');
            $language = $langService->findById($this->langId);
            $langEn->setOrder($language->getOrder());
            $langService->save($langEn);
            $langService->generateCache($langEn->getId());
            $language->setOrder(1);
            $langService->save($language);
            $langService->generateCache($language->getId());
            $langService->setCurrentLanguage($language);
        }
    }


    public  function setLangsOrdersforTest($langService)
    {
        $langEn = $langService->findByTag('en');
        if($langEn!=null) {
            if ($langEn->order != 1) {
                $langs = $langService->findAll();
                $enOrder = $langEn->getOrder();
                $langEn->setOrder(1);
                $langService->save($langEn);
                foreach($langs as $language){
                    if($language->getOrder()==1){
                        $this->langId = $language->getId();
                        $language->setOrder($enOrder);
                        $langService->save($language);
                    }
                }
                $langService->setCurrentLanguage($langEn);
            }
        }
    }
    protected function deleteLangs(BOL_LanguageService $langService)
    {
        $this->setLangsOrdersforTest($langService);
        $this->fixturesDir = OW_DIR_ROOT.'ow_unittest'.DS.'ow_core'.DS.'fixtures'.DS.'importLanguage'.DS;
        $this->deleteLang( 'test_prefix', 'test_key_1' );
        $this->deleteLang( 'test_prefix', 'test_key_2' );
        $this->deleteLang( 'test_prefix', 'test_key_3' );

        $this->deletePrefix( 'test_prefix', true );
    }

    protected function deletePrefix($prefix)
    {
        $prefix = BOL_LanguageService::getInstance()->findPrefix($prefix);

        if ( !empty($prefix) )
        {
            BOL_LanguageService::getInstance()->deletePrefix($prefix->id);
        }
    }

    protected function deleteLang($prefix, $key)
    {
        $langKey = BOL_LanguageService::getInstance()->findKey($prefix, $key);

        if ( !empty($langKey) )
        {
            BOL_LanguageService::getInstance()->deleteKey($langKey->id, false);
        }
    }

    public function testNewImportFormDir()
    {
        $langService = BOL_LanguageService::getInstance();
        $this->deleteLangs($langService);
        $langService->importPrefixFromDir($this->fixturesDir.'new'.DS.'langs'.DS, 'test_prefix', true);
        $this->isValidLangs();
    }

    public function testOldImportFormDir()
    {
        $langService = BOL_LanguageService::getInstance();
        $this->deleteLangs($langService);
        $langService->importPrefixFromDir($this->fixturesDir.'old'.DS, 'test_prefix', true);
        $this->isValidLangs();
    }

    public function testNewImportFormZip()
    {
        $langService = BOL_LanguageService::getInstance();
        $this->deleteLangs($langService);
        $langService->importPrefixFromZip($this->fixturesDir.'new.zip', 'test_prefix', true);
        $this->isValidLangs();
    }

    public function testOldImportFormZip()
    {
        $langService = BOL_LanguageService::getInstance();
        $this->deleteLangs($langService);
        $langService->importPrefixFromZip($this->fixturesDir.'old.zip', 'test_prefix', true);
        $this->isValidLangs();
    }

    public function testExportData()
    {
        $langService = BOL_LanguageService::getInstance();
        $this->deleteLangs($langService);

        $langService->importPrefixFromZip($this->fixturesDir.'export.zip', 'test_prefix', true);
        $this->isValidLangs();

        $this->deleteLangs($langService);

        $langService->importPrefixFromDir($this->fixturesDir.'export'.DS.'langs'.DS, 'test_prefix', true);
        $this->isValidLangs();
    }

    protected function isValidLangs()
    {
        $langService = BOL_LanguageService::getInstance();
        $prefix = BOL_LanguageService::getInstance()->findPrefix('test_prefix');

        self::assertTrue( (boolean)(!empty($prefix) && $prefix instanceof BOL_LanguagePrefix) );
        self::assertEquals('Test prefix', $prefix->label );
        $lang = BOL_LanguageService::getInstance()->findByTag('en');
        if($lang!=null){
            self::assertEquals('test1', $langService->getText($lang->id, 'test_prefix', 'test_key_1'));
            self::assertEquals('test2', $langService->getText($lang->id, 'test_prefix', 'test_key_2'));
            self::assertEquals('test3', $langService->getText($lang->id, 'test_prefix', 'test_key_3'));
        }
    }
}