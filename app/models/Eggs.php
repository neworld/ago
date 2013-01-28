<?php
Class Eggs extends Zend_Db_Table {
	protected $_name = 'eggs';
	protected $_primary = array('uid');

    public function get($uid, $time) {
    	$data = $this->find($uid);
    	
    	if ($data->count() == 0) {
    		$row = $this->createRow();
    		$row->uid = $uid;
    		$row->last = $time;
    		$row->has = 1;
    		$row->save();
		} else {
			$row = $data->getRow(0);
		}
		
		$a = floor($time / 600);
		$b = floor($row->last / 600);
		
//		$row->has += $a - $b;
		$row->last = $time;
		$row->save();
		return $row;
	}		
}
?>
