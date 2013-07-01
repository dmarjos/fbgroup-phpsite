<?php
class Services_Contacts_Import {

	private $runningPlugin=false;
	public	$settings=array("cookie_path"=>"/tmp","log_path"=>".");
    public $fromName='';
    public $fromAddr='';

	
	function startPlugin($pluginName) {
	
		Base_Registry::Set("EFW_SERVICES_SETTINGS",&$this->settings);
		$pluginClass="Services_Contacts_Import_Plugins_".ucfirst(strtolower($pluginName));
		$this->runningPlugin=new $pluginClass();
	}

	function setTransport($transportType) {
		$this->settings["transport"]=$transportType;
	}

	function login($username,$password) {
		return $this->runningPlugin->login($username,$password);
	}

	public function getMyContacts() {
		$contacts=$this->runningPlugin->getMyContacts();
		return $contacts;
	}	
	
	function getPluginType() {
		return $this->runningPlugin->service_type;
	}
	
	function sendMessage($message,$contacts) {
		$session_id=$this->runningPlugin->getSessionID();
		$this->runningPlugin->sendMessage($session_id,$message,$contacts);
	}
	
    function setFrom($name,$email) {
        $this->runningPlugin->fromName=$name;
        $this->runningPlugin->fromAddr=$email;
    }
	
	function getContactID($id,$ct) {
		return $this->runningPlugin->getContactID($id,$ct);
	}
}
