<?php
require "vendor/autoload.php";

use Elliotchance\Iterator\AbstractPagedIterator;

class GithubSearcher extends AbstractPagedIterator
{
    protected $totalSize = 0;
    protected $searchTerm;

    public function __construct($searchTerm)
    {
        $this->searchTerm = $searchTerm;

        // This will make sure totalSize is set before we try and access the data.
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

    public function getPage($pageNumber)
    {
        echo '<br>page number: '.$pageNumber.'<br>';
        $url = "https://api.github.com/search/repositories?" . http_build_query([
                'q' => $this->searchTerm,
                'page' => $pageNumber + 1,
            ]);

        // Create a stream
        $opts = array(
            'http'=>array(
                'method'=>"GET",
                'header'=> [
                    "Accept: application/vnd.github.v3+json",
                    "Content-Type: text/plain",
                    "User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/47.0.2526.111 YaBrowser/16.3.0.7146 Yowser/2.5 Safari/537.36"
                ]
            )
        );
        $context = stream_context_create($opts);
        $result = json_decode(file_get_contents($url, false, $context), true);
        $this->totalSize = $result['total_count'];
        return $result['items'];
    }
}
$repositories = new GithubSearcher('fridge');
echo "Found " . count($repositories) . " results:\n";
foreach ($repositories as $repo) {
    echo $repo['full_name'];
}
die();