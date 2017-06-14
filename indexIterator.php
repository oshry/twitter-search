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

    public function __construct($searchTerm)
    {
        $this->connection = new TwitterOAuth($this->key, $this->secret);
        $this->searchTerm = $searchTerm;
        $this->getPage(0);
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
        $nextPage = (isset($this->tweets->next_results) ? $this->tweets->next_results : '-1');
        if ($nextPage != '-1') {
            $max_id = $this->parseUrlParams($nextPage); // pagination
        } else {
            $max_id = $nextPage;
        }
        $this->tweets = $this->connection->get("search/tweets", ["q" => $this->searchTerm, "count" => $this->getPageSize(), 'max_id' => $max_id]);
        $this->totalSize = count($this->tweets->statuses);
        return $this->tweets->statuses;
    }
}

$tweets = new TwitterSearcher('bear');
echo "Found " . count($tweets) . " results:\n";
foreach ($tweets as $tweet) {
    var_dump($tweet);
}




















