<?php
require_once( 'settings.php' );

$sg = new speedGraphSettings();
$sg->set();
CachedEcho::send($sg->get(),"application/javascript");
