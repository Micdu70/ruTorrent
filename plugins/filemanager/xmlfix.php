<?php

class rxmlrpcfix extends rXMLRPCRequest {
	public function run($trusted = true)
	{
	        $ret = false;
		$this->i8s = array();
		$this->strings = array();
		$this->val = array();
		$this->commandOffset = 0;
		rTorrentSettings::get()->patchDeprecatedRequest($this->commands);
		while($this->makeNextCall())
		{
			$answer = self::send($this->content,$trusted);
			if(!empty($answer))
			{
				if($this->parseByTypes)
				{
					$ret = preg_match_map("|<value><string>(.*)</string></value>|Us", $answer, function ($match) {
						$this->strings[] = html_entity_decode(
							str_replace( array("\\","\""), array("\\\\","\\\""), $match[1][0] ),
							ENT_COMPAT,"UTF-8");
					});
					$ret = $ret && preg_match_map("|<value><i.>(.*)</i.></value>|Us", $answer, function ($match)
					{
						$this->i8s[] = $match[1][0];
					});
				} else {
					$ret = preg_match_map("/<value>(<string>|<i.>)(.*)(<\/string>|<\/i.>)<\/value>/Us", $answer, function ($match)
					{
						$this->val[] = html_entity_decode(
							str_replace( array("\\","\""), array("\\\\","\\\""), $match[2][0] ),
							ENT_COMPAT,"UTF-8");
					});
				}
				if($ret) {
					if(strstr($answer,"faultCode")!==false)
					{
						$this->fault = true;
						if(LOG_RPC_FAULTS && $this->important)
						{
							toLog($this->content);
							toLog($answer);
						}
						break;
					}
				} else break;
			} else break;
		}
		$this->content = "";
		$this->commands = array();
		return($ret);
	}
}

?>
