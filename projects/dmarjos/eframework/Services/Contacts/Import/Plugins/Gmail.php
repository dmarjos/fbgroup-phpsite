<?php
class Services_Contacts_Import_Plugins_Gmail extends Services_Contacts_Import_Base {

	public $service_type="email";
	public $debug_array=array(
	  'login_post'=>'Auth=',
	  'contact_xml'=>'xml'
	);
    public $fromName='';
    public $fromAddr='';

    function login($username,$password) {
		$this->service='gmail';
		$this->service_user=$username;
		$this->service_password=$password;
		if (!$this->init()) return false;

//		$post_elements=array('accountType'=>'HOSTED_OR_GOOGLE','Email'=>$username,'Passwd'=>$password/*,'service'=>'cp'*/,'source'=>'EmanonFramework-'.$this->version);
		$post_elements=array('accountType'=>'HOSTED_OR_GOOGLE','Email'=>$username,'Passwd'=>$password,'service'=>'cp','source'=>'EmanonFramework-'.$this->version);

//	    $res=$this->post("https://www.google.com/accounts/ServiceLoginAuth?service=mail",$post_elements,true);
	    $res=$this->post("https://www.google.com/accounts/ClientLogin",$post_elements,true);
	    if ($this->checkResponse("login_post",$res))
			$this->updateDebugBuffer('login_post',"https://www.google.com/accounts/ClientLogin",'POST',true,$post_elements);
		else {
			$this->updateDebugBuffer('login_post',"https://www.google.com/accounts/ClientLogin",'POST',false,$post_elements);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
		}

		$auth=substr($res,strpos($res,'Auth=')+strlen('Auth='));
		
		$this->login_ok=$auth;
		return true;

		dump_var($res);
		dump_var($this);
	}

	public function getMyContacts() {
		if ($this->login_ok===false) {
			$this->debugRequest();
			$this->stopPlugin();
			return false;
		} else $auth=$this->login_ok; 
		$res=$this->get("http://www.google.com/m8/feeds/contacts/default/full?max-results=10000",true,false,true,false,array("Authorization"=>"GoogleLogin auth={$auth}"));
		if ($this->checkResponse("contact_xml",$res))
			$this->updateDebugBuffer('contact_xml','http://www.google.com/m8/feeds/contacts/default/full?max-results=10000','GET');
		else {
			$this->updateDebugBuffer('contact_xml','http://www.google.com/m8/feeds/contacts/default/full?max-results=10000','GET',false);
			$this->debugRequest();
			$this->stopPlugin();
			return false;
		}
		
		$contacts=array();
		$doc=new DOMDocument();
		libxml_use_internal_errors(true);
		if (!empty($res)) $doc->loadHTML($res);
		libxml_use_internal_errors(false);
		$xpath=new DOMXPath($doc);
		$query="//entry";
		$data=$xpath->query($query);
		foreach ($data as $node) {
			$entry_nodes=$node->childNodes;
			$tempArray=array();	
			foreach($entry_nodes as $child) { 
				$domNodesName=$child->nodeName;
				switch($domNodesName) {
					case 'title' : { $tempArray['first_name']=$child->nodeValue; } break;
					case 'organization': { $tempArray['organization']=$child->nodeValue; } break;
					case 'email' : 
						{ 
						if (strpos($child->getAttribute('rel'),'home')!==false)
							$tempArray['email_1']=$child->getAttribute('address');
						elseif(strpos($child->getAttribute('rel'),'work')!=false)  	
							$tempArray['email_2']=$child->getAttribute('address');
						elseif(strpos($child->getAttribute('rel'),'other')!==false)  	
							$tempArray['email_3']=$child->getAttribute('address');
						} break;
					case 'phonenumber' :
						{
						if (strpos($child->getAttribute('rel'),'mobile')!==false)
							$tempArray['phone_mobile']=$child->nodeValue;
						elseif(strpos($child->getAttribute('rel'),'home')!==false)  	
							$tempArray['phone_home']=$child->nodeValue;	
						elseif(strpos($child->getAttribute('rel'),'work_fax')!==false)  	
							$tempArray['fax_work']=$child->nodeValue;
						elseif(strpos($child->getAttribute('rel'),'pager')!=false)  	
							$tempArray['pager']=$child->nodeValue;
						} break;
					case 'postaladdress' :
						{
						if (strpos($child->getAttribute('rel'),'home')!==false)
							$tempArray['address_home']=$child->nodeValue;
						elseif(strpos($child->getAttribute('rel'),'work')!==false)  	
							$tempArray['address_work']=$child->nodeValue;
						} break;	
					}
				}
			if (!empty($tempArray['email_1']))$contacts[$tempArray['email_1']]=$tempArray;
			if(!empty($tempArray['email_2'])) $contacts[$tempArray['email_2']]=$tempArray;
			if(!empty($tempArray['email_3'])) $contacts[$tempArray['email_3']]=$tempArray;
			}
		foreach ($contacts as $email=>$name) if (!$this->isEmail($email)) unset($contacts[$email]);
		return $this->returnContacts($contacts);
	}

	function getContactID($id,$ct) {
		if ($ct[$id])
			return $id;
		else
			return false;
	}

    function sendMessage($message,$contacts){
        $xFrom="";
        if (!empty($this->fromAddr)) {
            $xFrom=$this->fromAddr;
            if (!empty($this->fromName))
                $xFrom="{$this->fromName} <$xFrom>";

            $xFrom="From: $xFrom";
        }
        foreach($contacts as $email=>$name) {
            mail($email,$message["subject"],$message["body"],$xFrom);
        }
    }
	
}