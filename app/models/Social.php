<?php
class Social extends Zend_Db_Table_Abstract {
	protected $_name = 'socials';
	protected $_primary = array("id");

	const FRIEND = 1;
	const IGNORE = 2;

	function add($uid, $what, $type) {
		return $this->insert(array(
			"uid" => $uid,
			"what" => $what,
			"type" => $type
		));
	}

	function get($uid, $type = null) {
		$select = $this->select()->setIntegrityCheck(false)
								 ->from(array("t1" => $this->_name), '*')
								 ->join(array("t2" => 'users'), 't2.id = t1.what', 'name')
								 ->where('uid = ?', $uid)
								 ->order("id ASC");

		if (in_array($type, array(Social::FRIEND, Social::IGNORE)))
			$select->where("type = ?", $type);

		return $this->fetchAll($select);
	}

	function getStatus($uid, $what) {
		$select = $this->select()->where('uid = ?', $uid)
								 ->where('what = ?', $what);

		$data = $this->fetchAll($select);

		if ($data->count() == 0) {
			return false;
		} else {
			return $data->getRow(0)->type;
		}
	}

	function remove($uid, $what) {
		return $this->delete("uid = $uid and what = $what");
	}
}
?>
