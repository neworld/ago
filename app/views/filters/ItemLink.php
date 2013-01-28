<?php
class ItemLink implements Zend_Filter_Interface {
	public function filter($text) {
		return preg_replace(
			'/\[ITEM:([0-9]+)(:([-_\(\)a-zA-Z0-9ąĄčČęĘėĖįĮšŠųŲūŪžŽ ]*):)?\]/'
			,'<a style="font-weight:bold;color:#008E03" class="pointer" onmouseover="showitem($1);" onmouseout="closeitem()">[$3]</a>',
			$text
		);
	}
}
?>