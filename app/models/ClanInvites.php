<?php
class ClanInvites extends Zend_Db_Table {
	protected $_name = 'clan_invites';
	protected $_primary = array('id');

	public function invite($cid,$user,$reason) {
		return $this->insert(array(
			'cid' => $cid,
			'user' => $user,
			'reason' => $reason
		));
	}

	public function getinvites($user) {
		$select=$this->select()->setIntegrityCheck(false)
							   ->from(array("t1" => $this->_name),array('id','reason','date'))
							   ->join(array("t2" => 'clans'), 't2.id=t1.cid', array("clanname" => "title", "clantag" => "tag"))
							   ->where('user = ?',$user);

		return $this->fetchAll($select);
	}

	public function removeAll($cid) {
		return $this->delete("cid = $cid");
	}
}

?>
