<?php
require "vendor/autoload.php";
require "env.php";
use Elliotchance\Iterator\AbstractPagedIterator;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterSearcher extends AbstractPagedIterator
{
    protected $totalSize = 0;
    protected $searchTerm;
    protected $key = CONSUMER_KEY;
    protected $secret = CONSUMER_SECRET;
    protected $connection;
    protected $cursor = TRUE;
    protected $max_id = '-1';
    public $tweets;

    public function __construct($searchTerm)
    {
        if($searchTerm != '') {
            $this->connection = new TwitterOAuth($this->key, $this->secret);
            $this->searchTerm = $searchTerm;
            do {
                $this->getPage(0);
            }while($this->cursor);
        } else {
            return -1;
        }
    }

    public function getTotalSize()
    {
        return $this->totalSize;
    }

    public function getPageSize()
    {
        return 1000;
    }

    function parseUrlParams($url)
    {
        $params = array();
        $parts = parse_url($url);
        parse_str($parts['query'], $params);

        if (isset($params['max_id'])) {
            return $params['max_id'];
        } else {
            return '-1';
        }
    }

    public function getPage($pageNumber)
    {
        $this->tweets = $this->connection->get("search/tweets", ["q" => $this->searchTerm, "count" => $this->getPageSize(), 'max_id' => $this->max_id]);
        $nextPage = (isset($this->tweets->next_results) ? $this->tweets->next_results : '-1');
        if( isset($this->tweets->statuses)){
            $this->totalSize = count($this->tweets->statuses);
            if($this->totalSize == 100){
                $this->cursor = TRUE;
                if ($nextPage != '-1') {
                    $this->max_id = $this->parseUrlParams($nextPage); // pagination
                } else {
                    $this->max_id = $nextPage;
                }
                echo $this->max_id."<br>";
            }else{
                $this->cursor = FALSE;              // break do-while
                $this->max_id = '-1';          // reset
            }
        }else{
            $this->cursor = false;
        }

        return $this->tweets->statuses;
    }
}

$tweets = new TwitterSearcher('bear');
echo "Found " . count($tweets) . " results:\n";
foreach ($tweets as $tweet) {
    var_dump($tweet);
}




















