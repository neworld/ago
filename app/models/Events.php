<?php
class Events extends Zend_Db_Table {
	protected $_name = 'events';
	protected $_primary = array('id');

	public function sendevent($user,$text, $type = EVENT_OTHER) {
		$users=new Users();

		if ($users->exsist(null,$user)) {
			$data=array(
				'user' => $user,
				'text' => $text,
				'date' => mydate(),
				'type' => $type);
			return $this->insert($data);
		}
		return false;
	}
	/**
	* @desc gaunam ivykiu skaiciu
	* @param Int userio id
	* @return Int ivykiu kiekis
	*/
	public function num_events($user, $type = EVENT_ALL) {
		$select = $this->select()->from($this->_name,'COUNT(id) AS num')
											->where('user = ?',$user);

		if ($type != EVENT_ALL)
			$select->where('type = ?', $type);

		return $this->fetchRow($select)->num;
	}
	/**
	* @desc gaunam ivykius
	* @param Int useiro id
	* @param Int kiek ivykiu isvesti
	* @param Int nuo kurio pradeti
	* @return Zend_Db_Table_Rowset_Abstract
	*/
	public function getevents($user,$num=null,$start=null, $type = EVENT_ALL) {
		$select=$this->select()->where('user = ?',$user)
							   ->order("id DESC");
		if ($num)
			$select->limit($num,$start);

		if ($type != EVENT_ALL)
			$select->where('type = ?', $type);

		return $this->fetchAll($select);
	}

	public function delevents($user, $type = EVENT_ALL) {
		$m = "";
		if ($type != EVENT_ALL)
			$m = " and `type` = '$type'";

		return $this->delete("user = {$user} $m");
	}
}
?>
