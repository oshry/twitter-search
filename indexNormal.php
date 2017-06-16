<?php
///**** get token ****/
//define('CONSUMER_KEY', CONSUMER_KEY);
//define('CONSUMER_SECRET', CONSUMER_SECRET);
//
//function getBearerToken()
//{
//    $bearer = CONSUMER_KEY . ':' . CONSUMER_SECRET;
//    $bearer = base64_encode($bearer);
//
//    $ci = curl_init();
//    curl_setopt($ci, CURLOPT_USERAGENT, "My User Agent 1.0");
//    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
//    curl_setopt($ci, CURLOPT_TIMEOUT, 30);
//    curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
//    curl_setopt($ci, CURLOPT_HTTPHEADER, array('Authorization: Basic ' . $bearer));
//    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
//    curl_setopt($ci, CURLOPT_HEADER, FALSE);
//
//    $postfields = array("grant_type"=>"client_credentials");
//    curl_setopt($ci, CURLOPT_POST, TRUE);
//    if (!empty($postfields)) {
//        curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
//    }
//
//    curl_setopt($ci, CURLOPT_URL, "https://api.twitter.com/oauth2/token");
//
//    $response = curl_exec($ci);
//    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
//    $http_info = curl_getinfo($ci);
//    curl_close ($ci);
//
//
//    return json_decode($response);
//}
//
//$response = getBearerToken();
//var_dump($response->access_token);
//die;

require_once('./env.php');
ini_set('max_execution_time', 0);
ini_set('memory_limit','-1');


function objectsIntoArray($arrObjData, $arrSkipIndices = array())
{
    $arrData = array();

    if (is_object($arrObjData)) {
        $arrObjData = get_object_vars($arrObjData);
    }

    if (is_array($arrObjData)) {
        foreach ($arrObjData as $index => $value) {
            if (is_object($value) || is_array($value)) {

                $value = objectsIntoArray($value, $arrSkipIndices); // recursive call
            }
            if (in_array($index, $arrSkipIndices)) {
                continue;
            }
            $arrData[$index] = (count($value)==0)? '': $value;
        }
    }
    return $arrData;
}

function parseUrlParams($url)
{
    $params = array();
    $parts = parse_url($url);
    parse_str($parts['query'],$params);

    if(isset($params['max_id']))
    {
        return $params['max_id'];
    }
    else
    {
        return '-1';
    }
}


function prepareArr($arr)
{
    $arrResult = array();
    for($i=0;$i<count($arr);$i++)
    {
        for($j=0;$j<count($arr[$i]);$j++)
        {
            $arrResult[] = $arr[$i][$j];
        }
    }

    return $arrResult;
}



function executeCurl($url)
{

    $ci = curl_init();
    curl_setopt($ci, CURLOPT_USERAGENT, "User Agent 1.0");
    curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ci, CURLOPT_TIMEOUT, 30);
    curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ci, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . BEARER_TOKEN));
    curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ci, CURLOPT_HEADER, FALSE);
    curl_setopt($ci, CURLOPT_POST, FALSE);
    curl_setopt($ci, CURLOPT_URL, $url);

    $json = curl_exec($ci);

    $http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
    $http_info = curl_getinfo($ci);
    curl_close($ci);

    $response = json_decode($json);
    $response = objectsIntoArray($response);
    return $response;
}


function getTweets($query)
{

    $twitterurl = "https://api.twitter.com/1.1/search/tweets.json";

    $tweetsToShow = 100; // tweets count to be fetched from twitter api
    $resultType = 'recent'; // Default value 'recent'

    $result = array();
    $response = array();
    $trends = array();

    $responseStatusCount = 0;
    $cursor = TRUE;            //do-while looping
    $max_id = '-1';         // for twitter api pagination



    if($query != '')
    {
        do
        {

            if($max_id == '-1')
            {
                $pageParams = '?q=' . urlencode($query) . '&result_type=' . $resultType . '&count=' . $tweetsToShow;
                $url = $twitterurl . $pageParams;
            }
            else
            {
                $pageParams = '?q=' . urlencode($query) . '&max_id=' . $max_id . '&result_type=' . $resultType . '&count=' . $tweetsToShow;
                $url = $twitterurl . $pageParams;
            }
            $response = executeCurl($url);
            if(isset($response['errors'][0]['code']) == 88)
            {
                $cursor = TRUE;
                sleep(305);
            }
            else
            {
                if((isset($response['search_metadata'])) && (isset($response['statuses'])) && (!empty($response['statuses'])))
                {
                    $trends[] = $response['statuses'];
                    $nextPageParams = (isset($response['search_metadata']['next_results']) ? $response['search_metadata']['next_results'] : '-1');
                    $responseStatusCount = count($response['statuses']);
                    if($responseStatusCount  == 100)
                    {
                        $cursor = TRUE;      // do-while
                        if($nextPageParams != '-1')
                        {
                            $max_id = parseUrlParams($nextPageParams); // pagination
                        }
                        else
                        {
                            $max_id = $nextPageParams;
                        }
                    }
                    else
                    {
                        $cursor = FALSE;              // break do-while
                        $max_id = '-1';          // reset
                    }
                }
                else
                {
                    $cursor = FALSE;    // break do-while
                }
            }
        }while($cursor);

        $result = prepareArr($trends);
        return $result;
    }
    else
    {
        return -1;
    }

}

//die('here');
$tweets = getTweets('xml');
echo 'Total: '.count($tweets).'<br>';
foreach ( $tweets as $t)
    echo $t['text'].'<br>';
echo "<pre>";
die('Oshry');

