<?php
class Poll {
	public $title;
	public $unitl;
	public $on;
	public $date;
	public $items;
	public $id;
	
	public function __construct() {
		$this->items=array();
	}
	
	public function add($ob) {
		if (!($ob instanceof PollItem)) {
			throw new Exception('Poll::add() argument must be a PollItem');
		} 
		$this->items[]=$ob;
	}
}
?>
