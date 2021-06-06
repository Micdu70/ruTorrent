<?php

	eval( getPluginConf( 'fileshare' ) );

	$theSettings->registerPlugin($plugin["name"],$pInfo["perms"]);
