<?php

	$IP = $_POST[ "ip" ];
	if (!isset($IP))
		$Result = null;
	else
	{
		if (substr($IP, 0, 1) == '[')
			$IP = substr($IP, 1, -1);
		$Result = $IP . "<|>" . gethostbyaddr($IP);
	}

	echo($Result);

?>
