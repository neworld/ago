<?php
class AdminController extends Zend_Controller_Action {
	public function init() {
		$this->view->addHelperPath(MY_HELPER_PATH,'Main');
	}
	
	public function voteAction() {
		$poll=array();
		
		$polls=new Polls();
		
		$select=$polls->select()->from('polls','id');
		
		$data=$polls->fetchAll($select);
		
		foreach ($data as $v) {
			$poll[]=$polls->getpoll($v->id);
		}
		
		$this->view->data=$poll;
	}
}
?>
