<?php
class LoginHistory extends Zend_Db_Table {
	protected $_name = 'login_history';
	protected $_primaty = array('id');

	public function add($uid, $ip, $browser, $os) {
		return $this->insert(array(
			'uid' => $uid,
			'ip'  => $ip,
			'browser' => $browser,
			'os' => $os
			));
	}

	public function load($uid, $num = 10) {
		$select = $this->select()->where('uid = ?', $uid)
				->order('id DESC')
				->limit($num);

		return $this->fetchAll($select);
	}
}
?>
