<?php
class frmdatabackupunitTest extends FRMUnitTestUtilites
{
    /**
     * Test of frmdatabackup plugin
     */
    public function testDataBackup()
    {
        if(!defined('BACKUP_TABLES_USING_TRIGGER') || BACKUP_TABLES_USING_TRIGGER == true) {
            $username = 'frmuser1';
            $accountType = BOL_QuestionService::getInstance()->getDefaultAccountType()->name;
            FRMSecurityProvider::createUser($username, 'frmuser1@test.com', '12345', '1987/3/21', '1', $accountType, 'c0de');
            $user = BOL_UserService::getInstance()->findByUsername($username);
            $userId = $user->getId();
            FRMSecurityProvider::deleteUser($username);
            $table_name = BOL_UserDao::getInstance()->getTableName();
            $table_backup_name = FRMSecurityProvider::getTableBackupName($table_name);
            $query = 'select * from `' . $table_backup_name . '` where id = ' . $userId . ' and username = \'' . $username . '\'';
            $result = OW::getDbo()->queryForRow($query);
            self::assertEquals(true, !empty($result));
        }
        self::assertEquals(true, true);
    }

    public function testTriggerCount(){
        if(defined('BACKUP_TABLES_USING_TRIGGER') && !BACKUP_TABLES_USING_TRIGGER ) {
            return;
        }

        $DB_NAME = OW_DB_NAME;

        $sql = "SELECT COUNT(*)
            FROM information_schema.triggers
            WHERE trigger_schema = '{$DB_NAME}' AND EVENT_MANIPULATION='UPDATE'";
        $u_count = OW::getDbo()->queryForColumn($sql);
        self::assertGreaterThan(130, $u_count);

        $sql = "SELECT COUNT(*)
            FROM information_schema.triggers
            WHERE trigger_schema = '{$DB_NAME}' AND EVENT_MANIPULATION='DELETE'";
        $d_count = OW::getDbo()->queryForColumn($sql);
        self::assertGreaterThan(130, $d_count);

        // SPECIAL CASE: check if update trigger for base_user is created.
        self::assertEquals($u_count, $d_count);
    }
}