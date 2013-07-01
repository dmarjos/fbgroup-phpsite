<?
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Server_SVN {

    private $repositoryPath="";
    private $repositoryURL="";
    private $revision=-1;
    
    function __construct($user,$pass) {
	svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_USERNAME,$user);
	svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_PASSWORD,$pass);
    }

    function setRepositoryPath($repoPath) {
	if (!is_dir($repoPath))
	    return false;
	    
	$this->repositoryPath=$repoPath;
    }
    
    function setRepositoryURL($repoURL) {
	$this->repositoryURL=$repoURL;
    }
    
    function setRevision($revision) {
	$this->revision=$revision;
    }
    
    function getProjects() {
    
	$retVal=array();
	$d=dir($this->repositoryPath);
	while ($e=$d->read()) {
	    if (($e!=".") && ($e!="..") && ($e!=".deleted") && is_dir($this->repositoryPath."/".$e))
		$retVal[]=$e;
	}
	asort($retVal);
	return $retVal;
	    
    }
    
    function browseProject($project,$path = "/") {
    
	if (!is_dir($this->repositoryPath."/".$project))
	    throw new Server_SVN_RepositoryNotFoundException("SVN Repository wasn't created yet. Please wait a few minutes and try again");
    
	$output=@svn_ls($this->repositoryURL."/".$project.$path,$this->revision);
	if ($output===false) {
	    throw new Server_SVN_ForbiddenException("Access is forbidden for $project$path in {$this->repositoryURL}");
	}
	$_dirs=$_files=array();
	foreach($output as $fname => $info) {
	    if ($info["type"]=="dir")
		$_dirs[$fname]=$info;
	    else
		$_files[$fname]=$info;
	}
	
	ksort($_dirs);	
	ksort($_files);
	$output=array();
	foreach($_dirs	 as $fname => $info) 
	    $output[$fname]=$info;
	foreach($_files	 as $fname => $info) 
	    $output[$fname]=$info;
	    
	return $output;
	    
    }
    
    function logs($project,$path = "/") {
	$output=@svn_log($this->repositoryURL."/".$project.$path,$this->revision);
	if (!$output) {
	    return false;
	}
	$_output=array();
	foreach($output as $idx => $info) {
	    foreach($info["paths"] as $pathIdx=>$pathInfo) {
		if ($pathInfo["path"]==$path) {
		    $info["action"]=$pathInfo["action"];
		    break;
		}
	    }	    
	    unset($info["paths"]);
	    $output[$idx]=$info;
	}
	return $output;
    }
    
    function cat($project,$path = "/") {
	$output=@svn_cat($this->repositoryURL."/".$project.$path,$this->revision);
	if (!$output) {
	    return false;
	}
	return $output;
    }
    
    function pathInfo($project,$path="/",$recursive=false) {
    
	if (empty($this->revision))
	    $this->revision=-1;
	if ($this->revision!=-1) 
	    $rev="@".$this->revision;
	else
	    $rev="";
	$info=@svn_info($this->repositoryURL."/".$project.$path.$rev,$recursive);
	return $info;
    }
}