<?php
class Main_FormatBBcode {
	public function formatBBcode($text) {
		$filterChain = new Zend_Filter();
		$filterChain->addFilter(new BBcode());
		$filterChain->addFilter(new Smilein());
		$filterChain->addFilter(new ActivateUrl());
		$filterChain->addFilter(new ItemLink());

		return $filterChain->filter($text);
	}
}
?>