<?php
class ClanMembers extends Zend_Db_Table {
	protected $_name = 'clan_members';
	protected $_primary = array('id');

	public function check_user($user,$clan) {
		$select=$this->select()->where("user = ?", $user)
							   ->where("cid = ?", $clan)
							   ->from($this->_name,'COUNT(*) AS num');

		return ($this->fetchRow($select)->num==1);
	}



	public function count_member($clan) {
		$select=$this->select()->from($this->_name,'COUNT(*) AS num')
							   ->where('cid = ?',$clan);

		return $this->fetchRow($select)->num;
	}

	public function getClanId($user) {
		$select=$this->select()->where('user = ?',$user)
							   ->from($this->_name,'cid');

		return @$this->fetchRow($select)->cid;
	}

	public function add($user,$clan) {
		return $this->insert(array('cid' => $clan, 'user' => $user));
	}

	public function getmembers($clan) {
		$select=$this->select()->setIntegrityCheck(false)
							   ->from(array("t1" => $this->_name),array("cmid" => "id", "title"))
							   ->join(array("t2" => 'users'),'t2.id = t1.user AND t2.visible = 1')
							   ->joinLeft(array("t4" => "online"), "t4.user=t2.id", array("isonline" => "id"))
							   ->where('cid = ?',$clan);

		return $this->fetchAll($select);
	}

	public function removeAll($cid) {
		return $this->delete("cid = $cid");
	}
}
?>
