<?php
class AtackController extends Zend_Controller_Action {
	private $user;

	public function init() {
		//isjungiam automatini renderinima:
		$this->_helper->viewRenderer->setNoRender(true);
	}

	private function get_user() {
		Zend_Loader::loadClass('Session');
		$session=new Session();
		if ($session->get()) {
			Zend_Loader::loadClass('Users');
			$users=new Users();
			return $users->getuser($session->get());
		} else {
			return false;
		}
	}

	public function preDispach() {
		if (!$this->user=$this->get_user())
			throw new Exception("Turite būtinai prisijungti");
	}

	public function indexAction() {



	}
}