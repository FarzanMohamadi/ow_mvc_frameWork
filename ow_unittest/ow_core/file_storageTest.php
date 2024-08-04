<?php
class file_storageTest extends FRMUnitTestUtilites
{
    /*
     * $des1 for copyFile test with direct parameter equal true
     * $des1 for copyFile test with direct parameter equal false
     * */
    private static $des1=OW_DIR_ROOT . 'ow_unittest' . DS .'ow_core'. DS . 'file_storageTestCopy.php';
    private static $des2=OW_DIR_ROOT . 'ow_unittest' . DS .'ow_core'. DS . 'file_storageTestCopy2.php';
    protected function setUp()
    {
        parent::setUp();
    }


    public function test()
    {
        /**
         * $notExistFile for fake file and we expect that the result of fileExists method to be false
         * $existFile for actual file and we expect that the result of fileExists method to be true
         */
        $notExistFile=OW_DIR_ROOT . 'ow_unittest' . DS .'ow_core'. DS . 'Test_fake.php';
        $existFile=OW_DIR_ROOT . 'ow_unittest' . DS .'ow_core'. DS . 'file_storageTest.php';
        self::assertEquals(false,OW::getStorage()->fileExists($notExistFile));
        self::assertEquals(true, OW::getStorage()->fileExists($existFile));

        /**
         * $notFile for fake file and we expect that the result of isFile method to be false
         * $file for acctual file and we expect that the result of isFile method to be true
         */

        $file=OW::getStorage()->isFile(OW_DIR_ROOT . 'ow_unittest' . DS .'ow_core'. DS . 'file_storageTest.php');
        $notFile=OW::getStorage()->isFile(OW_DIR_ROOT . 'ow_unittest');
        self::assertEquals(true, $file);
        self::assertEquals(false, $notFile);

        /**
         * $notExistFile for fake file and we expect that the result of copyFile method to be false because source path(file) not exist
         * $existFile for actual file and we expect that the result of copyFile method to be true
         */

        self::assertEquals(true,  OW::getStorage()->copyFile($existFile,self::$des1,true));
        self::assertEquals(true, OW::getStorage()->copyFile($existFile,self::$des2));
        self::assertEquals(false,  OW::getStorage()->copyFile($notExistFile,self::$des1,true));
        self::assertEquals(false, OW::getStorage()->copyFile($notExistFile,self::$des2));

        /**
         * $existFile for actual file and we expect that the result of checkUrlFilePathExist method to be false because it is not a url
         */

        self::assertEquals(false, OW::getStorage()->checkUrlFilePathExist($existFile));
        //self::assertEquals(true, OW::getStorage()->checkUrlFilePathExist(OW_URL_HOME));


    }

    public function tearDown()
    {
        /**
         * remove destination files
         */
        OW::getStorage()->removeFile(self::$des1);
        OW::getStorage()->removeFile(self::$des2);
    }
}