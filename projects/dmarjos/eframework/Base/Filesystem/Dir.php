<?
if (!defined('BASEPATH')) exit('No direct script access allowed');
class Base_FileSystem_Dir {

    public $Folders=array();
    
    public function getFolderContent($parent) {
		if (!is_dir($parent)) return false;
		$dir=dir($parent);
		while($entry=$dir->read()) {
			if (substr($entry,0,1)!=".") {
				$this->Folders[]=array("name"=>$entry);
			}
		}
    
    }

}