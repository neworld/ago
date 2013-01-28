<?php
class Main_Run {
	public function run($command) {
		if (!Zend_Registry::isRegistered('SEND_RUN')) {
			Zend_Registry::set('SEND_RUN',array("$command;"));
		} else {
			$a=Zend_Registry::get('SEND_RUN');
			$a[]="$command;";
			Zend_Registry::set('SEND_RUN',$a);
		} 
	}
}
		
?>
