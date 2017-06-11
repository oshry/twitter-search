<?php
require "env.php";
require "vendor/autoload.php";

use Abraham\TwitterOAuth\TwitterOAuth;

class Twitter {

    public function __construct( $key, $secret ){
        $this->connection = new TwitterOAuth( $key, $secret);
    }
    public function search(){
        $_SESSION['last_id'] = ( isset( $_SESSION['last_id'] ) ) ? $_SESSION['last_id'] : 0;
        return $this->connection->get("search/tweets", ["q" => "bear", "count" => 100, 'max_id' => $_SESSION['last_id'] ]);
    }

}


$twitter  = new Twitter( CONSUMER_KEY, CONSUMER_SECRET);
$statuses = $twitter->search();
$last_id = end($statuses->statuses);
if(isset($last_id->id_str)){
    $_SESSION['last_id'] = $last_id->id_str;
}
echo "<pre>";
print_r($statuses);
