<?php
class ActivateUrl implements Zend_Filter_Interface {
	public function filter($text) {
		return do_clickable($text);
	}
}
?>