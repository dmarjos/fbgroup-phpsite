<?
if (!defined('BASEPATH')) exit('No direct script access allowed');
abstract class Cache_Frontend  {

	private static $cache_lifetime=86400;
	public static $cacheObject=NULL;
	
	function initCache($cacheType="Filesystem",$savePath="") {
	
		$cacheHandler = "Cache_$cacheType";
		if (!empty($savePath))
			self::$cacheObject=new $cacheHandler($savePath);
		else
			self::$cacheObject=new $cacheHandler();
	
	}

	function setLifetime($ttl) {
		self::$cache_lifetime=$ttl;
	}

	function saveCache($key,$content) {
	
		$co=self::$cacheObject;
		$co->saveCache($key,$content);
		
	}

	function getCache($key) {
	
		$co=self::$cacheObject;
		return $co->readCache($key,$content);
		
	}

	function checkIsCached($key) {
		$co=self::$cacheObject;
		$retVal=$co->isCached($key);
		if ($retVal) {
			$cachedSince=$co->lifeTime($key);
			if ($cachedSince<time()-self::$cache_lifetime)
				$retVal=false;
		}
		return $retVal;
	}
	
}