<?php
class frmoghatTest extends FRMUnitTestUtilites
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Test of frmoghat plugin
     */
    public function testOghat()
    {
        $service = FRMOGHAT_BOL_Service::getInstance();
        $cityName = 'city_test_1';
        $service->addCity($cityName, 10, 10);
        $existCity = $service->existCity($cityName, 10, 10);
        self::assertEquals(true, $existCity);

        $existCity = $service->deleteCity($cityName);
        self::assertEquals(false, $existCity);
    }
}