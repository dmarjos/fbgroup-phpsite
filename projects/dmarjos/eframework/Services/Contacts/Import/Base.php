<?php
/**
 * The core of the Contacts Import System
 * 
 * Contains methods and properties used by all
 * the Contact Import plugins
 * 
 * Based on OpenInviter Classes by OpenInviter.org
 * 
 * @author Daniel Marjos
 * @version 1.0
 */
 
abstract class Services_Contacts_Import_Base extends Services_Sessions {
	private $curl;
	public $service;
	public $service_user;
	public $service_password;
	public $version="1.0";
	private $has_errors=false;
	private $debug_buffer=array();
	
	
	protected function getElementDOM($string_bulk,$query,$attribute=false) {
		$search_val=array();
		$doc=new DOMDocument();
		libxml_use_internal_errors(true);
		if (!empty($string_bulk)) $doc->loadHTML($string_bulk);
		else return false;
		libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);$data=$xpath->query($query);
		if ($attribute)
			foreach ($data as $node)
				 $search_val[]=$node->getAttribute($attribute);
		else
			foreach ($data as $node)
				 $search_val[]=$node->nodeValue;
		if (empty($search_val))
			return false;  
		return $search_val;	
	}
	
	public function stopPlugin($graceful=false) {
		if ($this->settings['transport']=='curl')
			curl_close($this->curl);
		if (!$graceful) $this->endSession();
	}

	/**
	 * Update the internal debug buffer
	 * 
	 * Updates the internal debug buffer with information
	 * about the request just performed and it's state
	 * 
	 * @param string $step The name of the step being debugged
	 * @param string $url The URL that was being requested
	 * @param string $method The method used to request the URL (GET/POST)
	 * @param bool $response The state of the request
	 * @param mixed $elements An array of elements being sent in the request or FALSE if no elements are sent.
	 */
	protected function updateDebugBuffer($step,$url,$method,$response=true,$elements=false) {
		$this->debug_buffer[$step]=array(
			'url'=>$url,
			'method'=>$method
		);
		if ($elements)
			foreach ($elements as $name=>$value)
				$this->debug_buffer[$step]['elements'][$name]=$value;
		else
			$this->debug_buffer[$step]['elements']=false;
		if ($response)
			$this->debug_buffer[$step]['response']='OK';
		else {
			$this->debug_buffer[$step]['response']='FAILED';
			$this->has_errors=true;
		}
	}

	function init($session_id=false) {
		$this->settings=Base_Registry::Get("EFW_SERVICES_SETTINGS");
		$session_start=$this->startSession($session_id);
		if (!$session_start) return false;
		$file=$this->getCookiePath();
		if (!$session_id) {
			$fop=fopen($file,"wb");
			fclose($fop);
		}

		if ($this->settings['transport']=='curl') {
			$this->curl=curl_init();
			curl_setopt($this->curl, CURLOPT_USERAGENT,(!empty($this->userAgent)?$this->userAgent:"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1"));
			curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, false);
			curl_setopt($this->curl, CURLOPT_COOKIEFILE,$file);
			curl_setopt($this->curl, CURLOPT_HEADER, false);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
			curl_setopt($this->curl, CURLOPT_RETURNTRANSFER,true);
			curl_setopt($this->curl, CURLOPT_COOKIEJAR, $file);
			if (strtoupper (substr(PHP_OS, 0,3))== 'WIN') curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, (isset($this->timeout)?$this->timeout:5)/2);
			else  curl_setopt($this->curl, CURLOPT_TIMEOUT, (isset($this->timeout)?$this->timeout:5));
			curl_setopt($this->curl, CURLOPT_AUTOREFERER, TRUE);
			return true;
		}
	}
	
	protected function get($url,$follow=false,$header=false,$quiet=true,$referer=false,$headers=array()) {
		if ($this->settings['transport']=='curl') {
			curl_setopt($this->curl, CURLOPT_URL, $url);
			curl_setopt($this->curl, CURLOPT_POST,false);
			curl_setopt($this->curl, CURLOPT_HTTPGET ,true);
			if ($headers) {
				$curl_headers=array();
				foreach ($headers as $header_name=>$value)
					$curl_headers[]="{$header_name}: {$value}";
				curl_setopt($this->curl,CURLOPT_HTTPHEADER,$curl_headers);
			}
			if ($header OR $follow) curl_setopt($this->curl, CURLOPT_HEADER, true);
			else curl_setopt($this->curl, CURLOPT_HEADER, false);
			if ($referer) curl_setopt($this->curl, CURLOPT_REFERER, $referer);
			else curl_setopt($this->curl, CURLOPT_REFERER, '');
			$result=curl_exec($this->curl);
			if ($follow) {
		//		echo nl2br($result)."\n";
				$new_url=$this->followLocation($result,$url);
				if (!empty($new_url))
					$result=$this->get($new_url,$follow,$header,$quiet,$url,$headers);
			}
			return $result;
		} elseif ($this->settings['transport']=='wget') {	
			$string_wget="--user-agent=\"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1\"";
			$string_wget.=" --timeout=".(isset($this->timeout)?$this->timeout:5);
			$string_wget.=" --no-check-certificate";
			$string_wget.=" --load-cookies ".$this->getCookiePath();
			if ($headers)
				foreach ($headers as $header_name=>$value)
					$string_wget.=" --header=\"".escapeshellcmd($header_name).": ".escapeshellcmd($value)."\"";
			if ($header) $string_wget.=" --save-headers";
			if ($referer) $string_wget.=" --referer={$referer}";
			$string_wget.=" --save-cookies ".$this->getCookiePath();
			$string_wget.=" --keep-session-cookies";
			$string_wget.=" --output-document=-";
			$url=escapeshellcmd($url);
			if ($quiet)
				$string_wget.=" --quiet";
			else {
				$log_file=$this->getCookiePath().'_log';
				$string_wget.=" --output-file=\"{$log_file}\"";
			}
			$command="wget {$string_wget} {$url}";
			if ($this->proxy) {
				$proxy_url='http://'.(!empty($this->proxy['user'])?$this->proxy['user'].':'.$this->proxy['password']:'').'@'.$this->proxy['host'].':'.$this->proxy['port'];
				$command="export http_proxy={$proxy_url} && ".$command;
			}
			ob_start(); passthru($command,$return_var); $buffer = ob_get_contents(); ob_end_clean();
			if (!$quiet) {
				$buffer=file_get_contents($log_file).$buffer;
				unlink($log_file);
			}
			if((strlen($buffer)==0)or($return_var!=0)) return(false);
			else return $buffer;	
		}
	}
	
	protected function post($url,$post_elements,$follow=false,$header=false,$referer=false,$headers=array(),$raw_data=false,$quiet=true) {
		$flag=false;
		if ($raw_data)
			$elements=$post_elements;
		else {
			$elements='';
			foreach ($post_elements as $name=>$value) {
				if ($flag)
					$elements.='&';
				$elements.="{$name}=".urlencode($value);
				$flag=true;
			}
		}
		if ($this->settings['transport']=='curl') {
			curl_setopt($this->curl, CURLOPT_URL, $url);
			curl_setopt($this->curl, CURLOPT_POST,true);
			if ($headers) {
				$curl_headers=array();
				foreach ($headers as $header_name=>$value)
					$curl_headers[]="{$header_name}: {$value}";
				curl_setopt($this->curl,CURLOPT_HTTPHEADER,$curl_headers);
			}
			if ($referer) curl_setopt($this->curl, CURLOPT_REFERER, $referer);
			else curl_setopt($this->curl, CURLOPT_REFERER, '');
			if ($header OR $follow) curl_setopt($this->curl, CURLOPT_HEADER, true);
			else curl_setopt($this->curl, CURLOPT_HEADER, false);
			curl_setopt($this->curl, CURLOPT_POSTFIELDS, $elements);
			$result=curl_exec($this->curl);
			if ($follow) {
	//			echo nl2br($result)."\n";
				$new_url=$this->followLocation($result,$url);
				if ($new_url)
					$result=$this->get($new_url,$post_elements,$follow,$header,$url,$headers,$raw_data);
			}
			return $result;
		} elseif ($this->settings['transport']=='wget') {
			$string_wget="--user-agent=\"Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.1) Gecko/2008070208 Firefox/3.0.1\"";
			$string_wget.=" --timeout=".(isset($this->timeout)?$this->timeout:5);
			$string_wget.=" --no-check-certificate";
			$string_wget.=" --load-cookies ".$this->getCookiePath();
			if (!empty($headers))
				foreach ($headers as $header_name=>$value)
					$string_wget.=" --header=\"".escapeshellcmd($header_name).": ".escapeshellcmd($value)."\"";
			if ($header) $string_wget.=" --save-headers";
			if ($referer) $string_wget.=" --referer=\"{$referer}\"";
			$string_wget.=" --save-cookies ".$this->getCookiePath();
			$string_wget.=" --keep-session-cookies";
			$url=escapeshellcmd($url);
			$string_wget.=" --post-data=\"{$elements}\"";
			$string_wget.=" --output-document=-";
			if ($quiet)
				$string_wget.=" --quiet";
			else {
				$log_file=$this->getCookiePath().'_log';
				$string_wget.=" --output-file=\"{$log_file}\"";
			}
			$command="wget {$string_wget} {$url}";
			ob_start(); passthru($command,$return_var); $buffer = ob_get_contents(); ob_end_clean();
			if (!$quiet) {
				$buffer=file_get_contents($log_file).$buffer;
				unlink($log_file);
			}
			if((strlen($buffer)==0)or($return_var!=0)) return false;
			else return $buffer;
		}
	}
	
	protected function followLocation($result,$old_url) {
		if ((strpos($result,"HTTP/1.1 3")===false) AND (strpos($result,"HTTP/1.0 3")===false)) return false;
		$new_url=trim($this->getElementString($result,"Location: ",PHP_EOL));
		if (empty($new_url)) $new_url=trim($this->getElementString($result,"location: ",PHP_EOL));
		if (!empty($new_url))
			if (strpos($new_url,'http')===false) {
				$temp=parse_url($old_url);
				$new_url=$temp['scheme'].'://'.$temp['host'].($new_url[0]=='/'?'':'/').$new_url;
			}
		return $new_url;
	}

	protected function checkResponse($step,$server_response) {
		if (empty($server_response)) return false;
		if (strpos($server_response,$this->debug_array[$step])===false) return false;
		return true;
	}

	protected function debugRequest() {
		if ($this->has_errors) {
			$this->localDebug();
			return false;
		} 
		return true;
	}

	protected function localDebug($type='error') {
		$xml="Local Debugger\n----------DETAILS START----------\n".$this->buildDebugHuman()."\n----------DETAILS END----------\n";
		$this->logAction($xml,$type);
	}

	private function buildDebugHuman() {
		$debug_human="TRANSPORT: {$this->settings['transport']}\n";
		$debug_human.="SERVICE: {$this->service}\n";
		$debug_human.="USER: {$this->service_user}\n";
		$debug_human.="PASSWORD: {$this->service_password}\n";
		$debug_human.="STEPS: \n";
		foreach ($this->debug_buffer as $step=>$details) {
			$debug_human.="\t{$step} :\n";
			$debug_human.="\t\tURL: {$details['url']}\n";
			$debug_human.="\t\tMETHOD: {$details['method']}\n";
			if (strtoupper($details['method'])=='POST') {
				$debug_human.="\t\tELEMENTS: ";
				if ($details['elements']) {
					$debug_human.="\n";
					foreach ($details['elements'] as $name=>$value)
						$debug_human.="\t\t\t{$name}={$value}\n";
				} else
					$debug_human.="(no elements sent in this request)\n";
			}
			$debug_human.="\t\tRESPONSE: {$details['response']}\n";
		}
		return $debug_human;
	}

	protected function logAction($message,$type='error') {
//		$log_path=$this->settings['log_path']."/log_{$type}.log";
	//	$log_file=fopen($log_path,'a');
		$final_message='['.date("Y-m-d H:i:s")."] {$message}\n";
		//echo nl2br($final_message)."<br/>";
		//if ($log_file) {
			//fwrite($log_file,$final_message);
			//fclose($log_file);
		//}
	}

	protected function getElementString($string_to_search,$string_start,$string_end) {
		if (strpos($string_to_search,$string_start)===false)
			return false;
		if (strpos($string_to_search,$string_end)===false)
			return false;
		$start=strpos($string_to_search,$string_start)+strlen($string_start);$end=strpos($string_to_search,$string_end,$start);
		$return=substr($string_to_search,$start,$end-$start);
		return $return;	
	}

	protected function returnContacts($contacts) {
		$returnedContacts=array();
		$fullImport=array('first_name','middle_name','last_name','nickname','email_1','email_2','email_3','organization','phone_mobile','phone_home','phone_work','fax','pager','address_home','address_city','address_state','address_country','postcode_home','company_work','address_work','address_work_city','address_work_country','address_work_state','address_work_postcode','fax_work','phone_work','website','isq_messenger','skype_messenger','skype_messenger','msn_messenger','yahoo_messenger','aol_messenger','other_messenger');
		if (empty($this->settings['fImport'])) {
			foreach($contacts as $keyImport=>$arrayImport) {
				$name=trim((!empty($arrayImport['first_name'])?$arrayImport['first_name']:false).' '.(!empty($arrayImport['middle_name'])?$arrayImport['middle_name']:false).' '.(!empty($arrayImport['last_name'])?$arrayImport['last_name']:false).' '.(!empty($arrayImport['nickname'])?$arrayImport['nickname']:false));
				$returnedContacts[$keyImport]=(!empty($name)?utf8_encode($name):$keyImport);
			}		
		} else {
			foreach($contacts as $keyImport=>$arrayImport) 
				foreach($fullImport as $fullValue)
					$returnedContacts[$keyImport][$fullValue]=(!empty($arrayImport[$fullValue])?$arrayImport[$fullValue]:false);
		}
		return $returnedContacts;
	}

	public function isEmail($email) {
		return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email);
	}

	function sendMessage($message,$contacts) {
		foreach($contacts as $id => $name) {
			if ($this->isEmail($id)) {
				mail($id,$message["subject"],$message["body"]);
			}
		}
	}
	 	
}