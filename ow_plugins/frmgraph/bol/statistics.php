<?php
final class FRMGRAPH_BOL_Statistics
{

    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
    }


    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /***
     * @param $edgeList
     * @param $allUsers
     * @return array|bool|string
     */
    public function getServerInformation($edgeList, $allUsers){
        $result = " ";

        //edge list format
        //a b,c d,x a,d t,...
        $nodes = array();
        foreach ($edgeList as $friendship){
            $result = $result . $friendship['userId'] . ' ' .$friendship['feedId'] . ',';
            $nodes[$friendship['userId']] = $friendship['userId'];
            $nodes[$friendship['feedId']] = $friendship['feedId'];
        }
        $result = substr($result, 0, strlen($result) - 1);
        $result = trim($result);

        $isolatedNodes = array_diff( $allUsers, $nodes );
        $resultIsolated = " ";
        foreach ($isolatedNodes as $node){
            $resultIsolated = $resultIsolated . $node . ',';
        }
        $resultIsolated = substr($resultIsolated, 0,-1);
        $resultIsolated = trim($resultIsolated);

        //remove last ","

        $urlServer = OW::getConfig()->getValue('frmgraph', 'server');
        $params = new UTIL_HttpClientParams();
        $params->setHeader('Content-Type' ,'application/x-www-form-urlencoded');
        $params->setBody('data='.$result.'&isolated_nodes='.$resultIsolated);
        $result = array("degreeCentrality" => array(), "betweennessCentrality" => array(), "closenessCentrality" => array(), "eccentricityCentrality" => array());
        try {
            $response = UTIL_HttpClient::post($urlServer, $params);
            if(empty($response)){
                return $result;
            }
            $jsonResponse = json_decode($response->getBody());
            if(empty($jsonResponse)){
                return $result;
            }

            /***
             * json to array start
             */
            if (isset($jsonResponse->degreeCentrality)) {
                $degreeCentrality = $jsonResponse->degreeCentrality;
                foreach ($degreeCentrality as $key => $val) {
                    $result["degreeCentrality"][$key] = $val;
                }
            }

            if (isset($jsonResponse->betweennessCentrality)) {
                $betweennessCentrality = $jsonResponse->betweennessCentrality;
                foreach ($betweennessCentrality as $key => $val) {
                    $result["betweennessCentrality"][$key] = $val;
                }
            }

            if (isset($jsonResponse->closenessCentrality)) {
                $closenessCentrality = $jsonResponse->closenessCentrality;
                foreach ($closenessCentrality as $key => $val) {
                    $result["closenessCentrality"][$key] = $val;
                }
            }

            if (isset($jsonResponse->eccentricityCentrality)) {
                $eccentricityCentrality = $jsonResponse->eccentricityCentrality;
                foreach ($eccentricityCentrality as $key => $val) {
                    $result["eccentricityCentrality"][$key] = $val;
                }
            }

            if (isset($jsonResponse->pageRank)) {
                $pageRank = $jsonResponse->pageRank;
                foreach ($pageRank as $key => $val) {
                    $result["pageRank"][$key] = $val;
                }
            }

            if (isset($jsonResponse->hubAndAuth)) {
                $hubAndAuth = $jsonResponse->hubAndAuth;
                foreach ($hubAndAuth as $key => $val) {
                    $result["hub"][$key] = $val->hub;
                    $result["authority"][$key] = $val->authority;
                }
            }

            $connectedComponent = null;
            if (isset($jsonResponse->connectedComponent))
                $connectedComponent = $jsonResponse->connectedComponent;
            $result["connectedComponent"] = $this->calculateComponentDistributionFromConnectedComponents($connectedComponent);

            $degreeDistributions = null;
            if (isset($jsonResponse->degreeDistributions))
                $degreeDistributions = $jsonResponse->degreeDistributions;
            $result["degreeDistributions"] = $this->normalizeDegreeDistribution($degreeDistributions);

            $result["diameter"] = isset($jsonResponse->diameter) ? $jsonResponse->diameter : null;
            $result["sumDegree"] = isset($jsonResponse->sumDegree) ? $jsonResponse->sumDegree : null;
            $result["avgDistance"] = isset($jsonResponse->avgDistance) ? $jsonResponse->avgDistance : null;
            $result["edgesCount"] = isset($jsonResponse->edgesCount) ? $jsonResponse->edgesCount : null;

            $distanceMatrix = null;
            if (isset($jsonResponse->distanceMatrix))
                $distanceMatrix = $jsonResponse->distanceMatrix;
            $result["distanceMatrix"] = $this->calculateDistanceDistributionFromMatrix($distanceMatrix);

            /***
             * json to array end
             */
        } catch (Exception $e) {

        }
        return $result;
    }

    public function normalizeDegreeDistribution($degreeDistributions){
        $result = array();
        if (isset($degreeDistributions)) {
            foreach ($degreeDistributions as $key => $value) {
                if ($value != null) {
                    $result[$key] = $value;
                }
            }
        }
        return $result;
    }

    public function calculateComponentDistributionFromConnectedComponents($connectedComponent){
        $result = array();
        if (isset($connectedComponent)) {
            foreach ($connectedComponent as $row) {
                $result[] = sizeof($row);
            }
        }
        return $result;
    }

    public function calculateDistanceDistributionFromMatrix($distanceMatrix){
        $result = array();
        if (isset($distanceMatrix)) {
            foreach ($distanceMatrix as $row) {
                if ($row == null) {
                    continue;
                }
                foreach ($row as $column) {
                    if ($column == null) {
                        continue;
                    }
                    if ($column == sizeof($distanceMatrix) - 1) {
                        if (!isset($result[-1])) {
                            $result[-1] = 0;
                        }
                        $result[-1]++;
                    } else {
                        if (!isset($result[$column])) {
                            $result[$column] = 0;
                        }
                        $result[$column]++;
                    }
                }
            }
        }
        return $result;
    }

    public function calculateClusterCoefficientOfAllNodes($edgeList, $allUsers){
        $friendship = array();
        $nodes = array();
        foreach ($edgeList as $edge){
            if(!in_array($edge['userId'], $nodes)) {
                $nodes[] = $edge['userId'];
                $friendship[$edge['userId']] = array();
            }
            if(!in_array($edge['feedId'], $nodes)) {
                $nodes[] = $edge['feedId'];
                $friendship[$edge['feedId']] = array();
            }
            if(!isset($friendship[$edge['userId']]) || !in_array($edge['feedId'], $friendship[$edge['userId']])){
                $friendship[$edge['userId']][] = $edge['feedId'];
            }
        }

        //cluster coefficients
        $CCs = array();

        foreach ($allUsers as $id){
            //find isolate nodes
            if(!in_array($id, $nodes)){
                $CCs[$id] = 0;
            }
        }

        foreach ($friendship as $key => $node){
            $nodeFriendsCount = sizeof($node);

            //cc of node with one link = 1
            if($nodeFriendsCount > 1){
                $friendsConnectCount = 0;
                foreach ($node as $nodeFriends) {
                    foreach ($node as $nodeFriends2) {
                        if (in_array($nodeFriends2, $friendship[$nodeFriends])) {
                            $friendsConnectCount++;
                        }
                    }
                }
                $friendsConnectCount = ($friendsConnectCount / ($nodeFriendsCount * ($nodeFriendsCount - 1)));
            }else{
                $friendsConnectCount = 0;
            }
            $CCs[$key] = $friendsConnectCount;
        }
        $networkCC = 0;
        foreach ($CCs as $CC){
            $networkCC += $CC;
        }
        $networkCC = (sizeof($CCs) == 0) ? 1 : $networkCC/sizeof($CCs);
        $result = array('cc' => $CCs, 'average' => $networkCC);
        return $result;
    }
}