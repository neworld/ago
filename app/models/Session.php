<?php
class Session {
	public function set($id) {
		$_SESSION['userid']=$id;
		$_SESSION['server_name']="AGONIJA";
		$_SESSION['game_name']="agonija";
		Zend_Loader::loadClass('Online');
		$online=new Online();
		$online->updateuser($id);
	}
	public function isloged() {
		if (isset($_SESSION['userid'])) {
			Zend_Loader::loadClass('Online');
			$online=new Online();
			$online->updateuser($_SESSION['userid']);
			return true;
		}
		return false;
	}
	public function get() {
		return ($this->isloged())? $_SESSION['userid'] : false;
	}
	public function unlog() {
		Zend_Loader::loadClass('Online');
		$online=new Online();
		if (isset($_SESSION['userid']))
			$online->deletebyuser($_SESSION['userid']);
		unset($_SESSION['userid']);
		unset($_SESSION['server_name']);
		unset($_SESSION['game_name']);
	}
}
?>