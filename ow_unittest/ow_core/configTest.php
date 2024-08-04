<?php
class configTest extends FRMUnitTestUtilites
{
    private $configDao;
    private $configService;

    const KEY = "testConfigKey";
    const KEY2 = "testConfigKey2";
    const NAME = "testConfigName";
    const VALUE = "testConfigValue";

    protected function setUp()
    {
        parent::setUp();
        $this->configService = BOL_ConfigService::getInstance();
        $this->configDao = BOL_ConfigDao::getInstance();
    }

    /**
     * addConfig
     */
    public function testAddConfig()
    {
        $config = $this->configDao->findConfig($this::KEY, $this::NAME);
        self::assertNull($config);
        $this->configService->addConfig($this::KEY, $this::NAME, $this::VALUE);
        $config = $this->configDao->findConfig($this::KEY, $this::NAME);
        self::assertEquals($config->getValue(), $this::VALUE);
        self::assertNull($config->getDescription());
        $this->configService->addConfig($this::KEY, $this::NAME . "1", $this::VALUE, "description");
        $config = $this->configDao->findConfig($this::KEY, $this::NAME . "1");
        self::assertEquals($config->getValue(), $this::VALUE);
        self::assertEquals($config->getDescription(), "description");
    }

    /**
     * saveConfig
     */
    public function testSaveConfig()
    {
        $this->configService->addConfig($this::KEY, $this::NAME, $this::VALUE);
        $config = $this->configDao->findConfig($this::KEY, $this::NAME);
        self::assertEquals($config->getValue(), $this::VALUE);
        $this->configService->saveConfig($this::KEY, $this::NAME, $this::VALUE . "new");
        $configNew = $this->configDao->findConfig($this::KEY, $this::NAME);
        self::assertEquals($configNew->getValue(), $this::VALUE . "new");
    }

    /**
     * findConfigsList
     */
    public function testFindConfigsList()
    {
        $keyConfigs = OW::getConfig()->getValues($this::KEY);
        self::assertEmpty($keyConfigs);
        $this->configService->addConfig($this::KEY, $this::NAME, $this::VALUE);
        $this->configService->addConfig($this::KEY, $this::NAME . "2", $this::VALUE);
        $this->configService->addConfig($this::KEY2, $this::NAME, $this::VALUE);
        $keyConfigs = OW::getConfig()->getValues($this::KEY);
        self::assertEquals(sizeof($keyConfigs), 2);
        $key2Configs = OW::getConfig()->getValues($this::KEY2);
        self::assertEquals(sizeof($key2Configs), 1);
        self::assertEquals($keyConfigs[$this::NAME], $this::VALUE);
        $nonKeyConfigs = OW::getConfig()->getValues($this::KEY . "None");
        self::assertEmpty($nonKeyConfigs);
    }

    /**
     * findConfig
     */
    public function testFindConfig()
    {
        $config = OW::getConfig()->getValue($this::KEY, $this::NAME);
        self::assertNull($config);
        $this->configService->addConfig($this::KEY, $this::NAME, $this::VALUE);
        $config = $this->configDao->findConfig($this::KEY, $this::NAME);
        self::assertEquals($config->getValue(), $this::VALUE);
        self::assertNull($config->getDescription());
        $config = $this->configDao->findConfig($this::KEY2, $this::NAME);
        self::assertNull($config);
    }

    /**
     * findConfigValue
     */
    public function testFindConfigValue()
    {
        $this->configService->addConfig($this::KEY, $this::NAME, $this::VALUE);
        $config = $this->configDao->findConfig($this::KEY, $this::NAME);
        $configValue = OW::getConfig()->getValue($this::KEY, $this::NAME);
        self::assertEquals($config->getValue(), $configValue);

    }

    /**
     * removeConfig
     */
    public function testRemoveConfig()
    {
        $this->configService->addConfig($this::KEY, $this::NAME, $this::VALUE);
        $this->configService->addConfig($this::KEY, $this::NAME . "2", $this::VALUE);
        $this->configService->removeConfig($this::KEY, $this::NAME);
        $config = $this->configDao->findConfig($this::KEY, $this::NAME);
        $config2 = $this->configDao->findConfig($this::KEY, $this::NAME . "2");
        $keyConfigs = $this->configDao->findConfigsList($this::KEY);
        self::assertEquals(sizeof($keyConfigs), 1);
        self::assertNull($config);
        self::assertNotNull($config2);
    }

    /**
     * removePluginConfigs
     */
    public function testRemovePluginConfigs()
    {
        $this->configService->addConfig($this::KEY, $this::NAME, $this::VALUE);
        $this->configService->addConfig($this::KEY, $this::NAME . "2", $this::VALUE);
        $this->configService->addConfig($this::KEY2, $this::NAME, $this::VALUE);
        $this->configService->removePluginConfigs($this::KEY);
        $config1 = $this->configDao->findConfig($this::KEY, $this::NAME);
        $config2 = $this->configDao->findConfig($this::KEY, $this::NAME . "2");
        $config3 = $this->configDao->findConfig($this::KEY2, $this::NAME);
        self::assertNull($config1);
        self::assertNull($config2);
        self::assertNotNull($config3);
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->configService->removePluginConfigs($this::KEY);
        $this->configService->removePluginConfigs($this::KEY2);
    }
}