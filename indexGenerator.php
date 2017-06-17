<?php
require "vendor/autoload.php";
require "env.php";
use Elliotchance\Iterator\AbstractPagedIterator;
use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterSearcher extends AbstractPagedIterator
{
    protected $totalSize = 0;
    protected $searchTerm;
    protected $connection;
    protected $cursor = TRUE;
    protected $max_id = '0';
    public $tweets = [];

    public function __construct($con, $searchTerm)
    {
        if ($searchTerm != '') {
            $this->connection = $con;
            $this->searchTerm = $searchTerm;
            do {
                foreach ($this->getPage($this->max_id) as $tweet){
                    foreach ($tweet as $single) {
                        echo $single->text."<br>";
                    }
                }
                //for short test
                if(count($this->tweets) > 2000)
                    $this->cursor = false;
            } while ($this->cursor);
        } else {
            return -1;
        }
        return $this->tweets;
    }

    public function getTotalSize()
    {
        return $this->totalSize;
    }

    public function getPageSize()
    {
        return 100;
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
        $response = $this->connection->get("search/tweets", ["q" => $this->searchTerm, "count" => $this->getPageSize(), 'max_id' => $this->max_id]);
        if (isset($response->errors[0]['code']) == 88) {
            $this->cursor = TRUE;
            sleep(305);
        } else {
            $nextPage = (isset($response->search_metadata->next_results) ? $response->search_metadata->next_results : '-1');
            if (isset($response->statuses)) {
                $sum = count($response->statuses);
                if ($sum == 100) {
                    $this->cursor = TRUE;
                    if ($nextPage != '-1') {
                        $this->max_id = $this->parseUrlParams($nextPage); // pagination
                    } else {
                        $this->max_id = $nextPage;
                    }
                } else {
                    $this->cursor = FALSE;              // break do-while
                    $this->max_id = '-1';          // reset
                }
            } else {
                $this->cursor = false;
            }
        }

        yield $response->statuses;
    }
}
$con = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET);
$tweets2 = new TwitterSearcher($con, 'bear');
echo 'please 300 count = 300 count='.count($tweets2->tweets);
die();
