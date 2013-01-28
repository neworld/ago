<?php
class Smilein implements Zend_Filter_Interface {
	public function filter($text) {
		$a=include('app/data/smile_DB.php');
		return str_replace($a['out'],$a['in'],$text);
	}
}
?>
