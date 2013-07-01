<?php
class Html {
	function __ParseParameters($theParamStr) {

		$params=array();
		if (preg_match_all('/([A-Za-z_0-9]*)="([A-Za-z0-9_\s\+\-\*\/\.\(\)\[\]=><!\'&|]*)"/s',$theParamStr,$matches,PREG_SET_ORDER)) {
			foreach($matches as $match) {
				$theParamValue=$match[2];
				if (substr($theParamValue,-1)=="/") $theParamValue=substr($theParamValue,0,-1);
				$params[strtoupper($match[1])]=str_replace('"','',$theParamValue);
			}
		} elseif (preg_match_all('/([A-Za-z_0-9]*)=([A-Za-z0-9_\+\-\*\/\.\(\)\[\]=><!]*)/s',$theParamStr,$matches,PREG_SET_ORDER)) {
			foreach($matches as $match) {
				$theParamValue=$match[2];
				if (substr($theParamValue,-1)=="/") $theParamValue=substr($theParamValue,0,-1);
				$params[strtoupper($match[1])]=str_replace('"','',$theParamValue);
			}
		}
		return $params;
	}

}