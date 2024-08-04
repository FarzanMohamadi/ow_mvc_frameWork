<?php
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 * Date: 7/12/2017
 * Time: 11:00 AM
 */
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__ . '/../ow_updates/classes/language_service.php');

class languageAddOrUpdateTest extends FRMUnitTestUtilites
{
    private static $PREFIX = "prefix";
    private static $KEY = "key";
    /**
     * @var UPDATE_LanguageService
     */
    private $updateLanguageService;
    /**
     * @var BOL_LanguageService
     */
    private $languageService;
    private $enId;
    private $faId;

    protected function setUp()
    {
        parent::setUp();
        $this->updateLanguageService = UPDATE_LanguageService::getInstance();
        $this->languageService = BOL_LanguageService::getInstance();
        $languages = $this->languageService->getLanguages();
        $languageId = null;
        foreach ($languages as $lang) {
            if ($lang->tag == 'en') {
                $this->enId = $lang->id;
            }
            if ($lang->tag == 'fa-IR') {
                $this->faId = $lang->id;
            }
        }
    }

    public function test()
    {
        //add some values
        $this->languageService->addPrefix(self::$PREFIX, '');
        $this->updateLanguageService->addOrUpdateValueByLanguageTag('fa-IR', self::$PREFIX, self::$KEY, 'fa-v1');
        $newValue = $this->getDBValue($this->faId, self::$PREFIX, self::$KEY);
        //the translation change must be applied in db
        self::assertEquals($newValue, 'fa-v1');

        $this->updateLanguageService->addOrUpdateValueByLanguageTag('en', self::$PREFIX, self::$KEY, 'en-v1');
        $newValue = $this->getDBValue($this->enId, self::$PREFIX, self::$KEY);
        //the translation change must be applied in db
        self::assertEquals($newValue, 'en-v1');

        //update the values
        $this->updateLanguageService->addOrUpdateValueByLanguageTag('fa-IR', self::$PREFIX, self::$KEY, 'fa-v2');
        $newValue = $this->getDBValue($this->faId, self::$PREFIX, self::$KEY);
        //the translation change must be applied in db
        self::assertEquals($newValue, 'fa-v2');

        $this->updateLanguageService->addOrUpdateValueByLanguageTag('en', self::$PREFIX, self::$KEY, 'en-v2');
        $newValue = $this->getDBValue($this->enId, self::$PREFIX, self::$KEY);
        //the translation change must be applied in db
        self::assertEquals($newValue, 'en-v2');
    }

    private function getDBValue($languageId, $prefix, $key)
    {
        $value = $this->languageService->getValue($languageId, $prefix, $key);
        if (isset($value))
            return $value->value;
        return null;
    }

    protected function tearDown()
    {
        parent::tearDown();
        $prefixId = $this->languageService->findPrefixId(self::$PREFIX);
        $this->languageService->deletePrefix($prefixId);
    }


}