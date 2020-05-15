<?php
require_once( dirname(__FILE__)."/../../php/settings.php" );
require_once( dirname(__FILE__)."/../../php/Snoopy.class.inc" );

$port = rTorrentSettings::get()->port;
$bind = rTorrentSettings::get()->bind;

function get_ip()
{
	$client = new Snoopy();
	$client->proxy_host = "";

	@$client->fetch("https://portchecker.co/");

	if($client->status==200)
	{
		if(strpos($client->results,"data-ip=")!==false)
		{
			preg_match('/data-ip="(?P<ip>.*)"/', $client->results, $match);
			return $match["ip"];
		}
	}
}

function check_port($ip,$port)
{
	$ret = 0;

	$client = new Snoopy();
	$client->proxy_host = "";

	@$client->fetch("https://portchecker.co/", "POST", "application/x-www-form-urlencoded", "target_ip=".$ip."&port=".$port);

	if($client->status==200)
	{
		if(strpos($client->results,">closed<")!==false)
			$ret = 1;
		else
		if(strpos($client->results,">open<")!==false)
			$ret = 2;
	}

	cachedEcho('{ "port": '.$port.', "status": '.$ret.' }',"application/json");
}

if(!empty($bind) && $bind != '0.0.0.0')
	check_port($bind,$port);
else
{
	session_start();
	if(isset($_REQUEST['init']))
		unset($_SESSION['ip']);
	if(isset($_SESSION['ip']))
		check_port($_SESSION['ip'],$port);
	else
	{
		$_SESSION['ip'] = get_ip();
		if(isset($_SESSION['ip']))
			check_port($_SESSION['ip'],$port);
		else
			cachedEcho('{ "port": '.$port.', "status": 0 }',"application/json");
	}
}
