<?php
class Shop extends Zend_Db_Table {
	protected $_name='shop';
	protected $_primary = array('id');

	public function deleteold() {
		return $this->delete('available < \''.date(DATE_FORMAT2).'\'');
	}

	public function additem($item,$user=0,$nano=0, $money=0, $date=null) {
		$date=(!$date)? date(DATE_FORMAT2,time()+3600*24) : $date;
		$data=array(
			'user' => $user,
			'item' => $item,
			'available' => $date,
			'nano' => $nano,
			'money' => $money
		);

		return $this->insert($data);
	}

	public function get($user=0,$onlyuser=false) {
		$select=$this->select()->where('available > ?',date(DATE_FORMAT2));

		if ($onlyuser) {
			$select->where('user = ?',$user);
		} else {
			$select->where('user = ? OR user = 0',$user);
		}

		return $this->fetchAll($select);
	}

	public function buy($id) {
		$data=$this->find($id);
		if ($data->count()==0)
			return false;

		return $data->getRow(0)->delete();
	}
}
?>