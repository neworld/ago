<?php
class Shop extends Zend_Db_Table {
	protected $_name='shop';
	protected $_primary = array('id');

	public function deleteold() {
		return $this->delete('available < '.date(DATE_FORMAT2));
	}

	public function additem($item,$user=0,$nano=0, $money=0) {
		$data=array(
			'user' => $user,
			'item' => $item
		);

		return $this->insert($data);
	}

	public function get($user=0,$onlyuser=false) {
		$select=$this->select()->where('available > ?',date(DATE_FORMAT));

		if ($onlyuser) {
			$select->where('user = ?',$user);
		} else {
			$select->where('user = ? OR user = 0',$user);
		}

		return $this->fetchAll($select);
	}
}
?>