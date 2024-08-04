<?php
/**
 * User: Hamed Tahmooresi
 * Date: 5/10/2016
 * Time: 1:46 PM
 */

require_once "init.php";

class PerformanceDataGenerator
{
    public static $conn;
    private $ignoreUsernameList;

    public function __construct($numberOfUsers,$usernamePrefix){
        $this->numberOfUsers = $numberOfUsers;
        $this->usernamePrefix = $usernamePrefix;
    }

    public function createUsers()
    {
        $sql = 'TRUNCATE TABLE `ow_base_user`';
        OW::getDbo()->query($sql);
        $sql = 'TRUNCATE TABLE `ow_base_question_data`';
        OW::getDbo()->query($sql);

        echo "Creating ".$this->numberOfUsers." users \n";

        $output = fopen('output.csv', 'w');
        fputcsv($output, array('username', 'password'));

        $counter = 0;
        for ($i = 1; $i <= $this->numberOfUsers; $i++) {
            $userId = $i;
            $username = $this->usernamePrefix."".strval($i);
            $email = $username."@gmail.com";
            $sql = "INSERT INTO `ow_base_user` (`id`, `email`, `username`, `password`, `joinStamp`, `activityStamp`, `accountType`, `emailVerify`, `joinIp`) VALUES
                (".$userId.", '".$email."', '".$username."', '3cfaa0a3c9aa979dfd729ab77037dc87a5a685e0cfbf138d83ad03dd25d9fd5b', 1449404242, 1449408781, '290365aadde35a97f11207ca7e4279cc', 1, 0);";
            OW::getDbo()->query($sql);
            $sql = "INSERT INTO `ow_base_question_data` (`questionName`, `userId`, `textValue`, `intValue`, `dateValue`) VALUES ('realname', ".$userId.", '".$username."', 0, NULL);";
            OW::getDbo()->query($sql);
            $sql = "INSERT INTO `ow_base_question_data` (`questionName`, `userId`, `textValue`, `intValue`, `dateValue`) VALUES ('sex',".$userId.", '', 2, NULL);";
            OW::getDbo()->query($sql);
            $sql = "INSERT INTO `ow_base_question_data` (`questionName`, `userId`, `textValue`, `intValue`, `dateValue`) VALUES ('birthdate', ".$userId.", '', 0, '1976-01-16 00:00:00');";
            OW::getDbo()->query($sql);
            $counter ++;

            fputcsv($output, array($username, "hamed1369"));

            if ($counter % 1000 == 0){
                echo "I've created ".$counter." users \n";
            }

        }
        fclose($output);
        echo "done creating users! \n";
    }
    public function makeFriends($minNumberOfFriends,$maxNumberOfFriends){

        $sql = 'TRUNCATE table `ow_newsfeed_follow`';
        OW::getDbo()->query($sql);
        $sql = 'TRUNCATE table `ow_friends_friendship`';
        OW::getDbo()->query($sql);

        $counter = 0;
        for ($j = 1;$j<=$this->numberOfUsers;$j++){

            $friendsNum = rand($minNumberOfFriends,$maxNumberOfFriends);

            for ($i = 1;$i<=$friendsNum;$i++){
                $friendUserId= ($j+$i) % $this->numberOfUsers;
                if ($friendUserId == 0){
                    $friendUserId = $this->numberOfUsers;
                }

                try{
                    $sql = "INSERT INTO `ow_newsfeed_follow` (`feedId`, `feedType`, `userId`, `permission`, `followTime`) VALUES (".$j.", 'user', ".$friendUserId.", 'friends_only', 1455698082);";
                    OW::getDbo()->query($sql);

                }catch(PDOException $e){

                }
                try{
                    $sql = "INSERT INTO `ow_newsfeed_follow` (`feedId`, `feedType`, `userId`, `permission`, `followTime`) VALUES (".$friendUserId.", 'user', ".$j.", 'friends_only', 1455698082);";
                    OW::getDbo()->query($sql);
                }catch(PDOException $e){

                }
                $sql = "INSERT INTO `ow_friends_friendship` (`userId`, `friendId`, `status`, `timeStamp`, `viewed`,`active`,`notificationSent`) VALUES (".$j.",".$friendUserId.", 'active',1462878594,1,1,0);";
                OW::getDbo()->query($sql);



            }
            $counter ++;
            if ($counter % 50 == 0){
                echo "I've created friendship relation for ".$counter." users \n";
            }
        }
        echo "Done creating friends! \n";
    }
    public function createPosts(){
        echo "creating posts \n";
        $sql = 'TRUNCATE table `ow_newsfeed_action`';
        OW::getDbo()->query($sql);
        $sql = 'TRUNCATE table `ow_newsfeed_activity`';
        OW::getDbo()->query($sql);
        $sql = 'TRUNCATE table `ow_newsfeed_status`';
        OW::getDbo()->query($sql);
        $sql = 'TRUNCATE table `ow_newsfeed_action_feed`';
        OW::getDbo()->query($sql);


        $activityId = 0;
        for ($j = 1;$j<=$this->numberOfUsers;$j++) {
            $userId = $j;
            $statusId = $j;
            $actionId = $j;
            $actionData = '\'{"content":{"format":"text","vars":{"status":"sghl"}},"attachmentId":null,"statusId":'.$statusId.',
                "status":"status message","contentImage":null,"view":{"iconClass":"ow_ic_comment"},
                "data":{"userId":'.$userId.',"status":"status message"},"actionDto":null}\'';
            $sql = "INSERT INTO `ow_newsfeed_action` (`id`,`entityId`,`entityType`,`pluginKey`,`data`,`format`) VALUES (".$actionId.",".$statusId.",'user-status','newsfeed',".$actionData.",'text')";
            OW::getDbo()->query($sql);


            $sql = "INSERT INTO `ow_newsfeed_status` (`id`,`feedType`,`feedId`,`timeStamp`,`status`) VALUES (".$statusId.",'user',".$userId.",1455698082,'status message');";

                OW::getDbo()->query($sql);


            $activityId ++;
            $now = time();
            $sql = "INSERT INTO `ow_newsfeed_activity` (`id`,`activityType`,`activityId`,`userId`,`data`,`actionId`,`timeStamp`,`privacy`,`visibility`,`status`) VALUES
            (".$activityId.",'create',".$userId.",".$userId.",'[]',".$actionId.",".$now.",'friends_only',15,'active');";
            OW::getDbo()->query($sql);

            $sql = "INSERT INTO `ow_newsfeed_action_feed` (`feedType`,`feedId`,`activityId`) VALUES ('user',".$userId.",".$activityId.");";
            OW::getDbo()->query($sql);

            $activityId ++;
            $sql = "INSERT INTO `ow_newsfeed_activity` (`id`,`activityType`,`activityId`,`userId`,`data`,`actionId`,`timeStamp`,`privacy`,`visibility`,`status`) VALUES
            (".$activityId.",'subscribe',".$userId.",".$userId.",'[]',".$actionId.",".$now.",'friends_only',15,'active');";
            OW::getDbo()->query($sql);



        }
        echo "posts created! \n";
    }
}
$gen = new PerformanceDataGenerator(1000,"hamed");
OW::getConfig()->saveConfig('base','guests_can_view',2);
$gen->createUsers();
$gen->makeFriends(5,50);
$gen->createPosts();
//echo print_r(PerformanceDataGenerator::getAllUsers());