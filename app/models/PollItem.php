<?php
class PollItem {
	public $id;
	public $pid;
	public $title;
	public $num;
	
	public function __construct($id, $pid, $title, $num) {
		$this->id=$id;
		$this->pid=$pid;
		$this->title=$title;
		$this->num=$num;
	}
	
	public function __toString() {
		return "{$this->title} ({$this->num})";
	}
}
?>
