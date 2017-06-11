<?php
session_start();
ini_set ('display_errors', 'on');
ini_set ('log_errors', 'on');
ini_set ('display_startup_errors', 'on');
ini_set ('error_reporting', E_ALL);

define('CONSUMER_KEY', '[ADD YOURS]');
define('CONSUMER_SECRET', '[ADD YOURS]');

// -- Locale setup ---------------------------------------------

// Set the default locale.
// @link  http://php.net/setlocale
setlocale(LC_ALL, 'en_US.utf-8');

// Set the default time zone.
// @link  http://php.net/timezones
date_default_timezone_set('UTC');

// Set the MB extension encoding to the same character set
// @link  http://www.php.net/manual/function.mb-substitute-character.php
mb_internal_encoding('none');