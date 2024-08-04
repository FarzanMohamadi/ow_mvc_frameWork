<?php
ob_start();
/**
 * @author Farzan Mohammadi <farzan.mohamadii@gmail.com>
 */
class UtilsFileTest extends FRMUnitTestUtilites
{
    private static $DIR_PATH = OW_DIR_ROOT.'ow_unittest'.DS.'ow_core'.DS.'testDir';
    private static $COPY_DIR_PATH = OW_DIR_ROOT.'ow_unittest'.DS.'ow_core'.DS.'testDir2';

    protected function setUp()
    {
        parent::setUp();
    }

    public function testCopyDir()
    {
        UTIL_File::copyDir(self::$DIR_PATH, self::$COPY_DIR_PATH);
        self::assertTrue(OW::getStorage()->isDir(self::$COPY_DIR_PATH));
        self::assertTrue(OW::getStorage()->fileExists(self::$COPY_DIR_PATH . DS . 'test.png'));
    }

    public function testFindFiles()
    {
        $result = UTIL_File::findFiles(self::$DIR_PATH, array("png"), 1);
        self::assertTrue(sizeof($result) == 1);
    }

    public function testRemoveDir()
    {
        if (!OW::getStorage()->fileExists(self::$COPY_DIR_PATH)){
            OW::getStorage()->mkdir(self::$COPY_DIR_PATH );
        }
        self::assertTrue(OW::getStorage()->fileExists(self::$COPY_DIR_PATH));
        UTIL_File::removeDir(self::$COPY_DIR_PATH);
        self::assertFalse(OW::getStorage()->fileExists(self::$COPY_DIR_PATH));
    }

    public function testGetFileSize()
    {
        $file_size = UTIL_File::getFileSize(self::$DIR_PATH . DS . 'test.png');
        $size_parts = explode(" ", $file_size);
        $size_value = (int) $size_parts[0];
        self::assertTrue( (25 < $size_value) && ($size_value < 35) && ($size_parts[1] == "kB")); // approximate size assertion
        // self::assertTrue( $file_size == "30.61 kB");  // exact size assertion
    }

    public function testGetExtension()
    {
        UTIL_File::getExtension(self::$DIR_PATH . DS . 'test.png');
        self::assertTrue(UTIL_File::getExtension(self::$DIR_PATH . DS . 'test.png') == "png");
    }

    public function testRemoveLastDS()
    {
        $file_path_with_ds = self::$DIR_PATH . DS . 'test.png' . DS;
        self::assertTrue(substr($file_path_with_ds, -1) === DS);
        $file_path_with_ds = UTIL_File::removeLastDS($file_path_with_ds);
        self::assertFalse(substr($file_path_with_ds, -1) === DS);
    }

    public function testValidate()
    {
        self::assertTrue(UTIL_File::validate('test.png'));
        self::assertFalse(UTIL_File::validate('test.svg'));
        self::assertTrue(UTIL_File::validate('test.svg', array('png', 'svg')));
    }

    public function testSanitizeName()
    {
        $text = ".-_test\rtest?[]/\\=<>\ttest:;,'\&$#*()|~`!{}\ntest.-_";
        $sanitized_text = UTIL_File::sanitizeName($text);
        self::assertTrue($sanitized_text == "test-test-test-test");
    }

    public function testGetFileUrl()
    {
        $base_static_dir_path = OW_URL_HOME . "ow_userfiles/plugins/base/";
        self::assertTrue(OW::getStorage()->getFileUrl(OW::getPluginManager()->getPlugin('base')->getUserFilesDir()) == $base_static_dir_path);
    }

    protected function tearDown()
    {
        parent::tearDown();
        try{
            UTIL_File::removeDir(self::$COPY_DIR_PATH);
        } catch (Exception $e){
        }
    }
}