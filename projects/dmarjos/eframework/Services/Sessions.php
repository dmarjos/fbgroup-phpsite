<?php
abstract class Services_Sessions {
	protected $session_id;
	public $settings=array();

	protected function startSession($session_id=false) {
		if ($session_id) {
			$path=$this->getCookiePath($session_id);
			if (!file_exists($path)) {
				$this->internalError="Invalid session ID";
				return false;
			}
			$this->session_id=$session_id;
		} else
			$this->session_id=$this->getSessionID();
		return true;
	}

	public function getSessionID() {
		return (empty($this->session_id)?time().'.'.rand(1,10000):$this->session_id);
	}

	protected function endSession() {
		if ($this->checkSession()) {
			$path=$this->getCookiePath($this->session_id);
			if (file_exists($path)) 
				unlink($path);
			$path=$this->getLogoutPath($this->session_id);
			if (file_exists($path)) 
				unlink($path);
			unset($this->session_id);
		}
	}

	protected function getLogoutPath($session_id=false) {
		if ($session_id) $path=$this->settings['cookie_path'].DIRECTORY_SEPARATOR.'sci.'.$session_id.'.logout';
		else $path=$this->settings['cookie_path'].DIRECTORY_SEPARATOR.'sci.'.$this->getSessionID().'.logout';
		return $path;
	}

	/**
	 * Get the cookies file path
	 * 
	 * Gets the path to the file storing all
	 * the cookie for the current session
	 * 
	 * @return string The path to the cookies file.
	 */
	protected function getCookiePath($session_id=false) {
		if ($session_id) $path=$this->settings['cookie_path'].DIRECTORY_SEPARATOR.'sci.'.$session_id.'.cookie';
		else $path=$this->settings['cookie_path'].DIRECTORY_SEPARATOR.'sci.'.$this->getSessionID().'.cookie';
		return $path;
	}

	protected function checkSession() {
		return (empty($this->session_id)?FALSE:TRUE);
	}

}