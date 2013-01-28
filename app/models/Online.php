<?php
class Online extends Zend_Db_Table {
	protected $_name = 'online';
	protected $_primary = array('id');
	protected $timeout=ONLINE_TIMEOUT;

	public function settimeout($timeout) {
		$this->timeout=$timeout;
	}

	private function deleteold() {
		$this->delete("time < ".(time()-$this->timeout));
	}

	public function deletebyuser($user) {
		if ($user) {
			$this->delete("user = $user");
		}
	}

	public function isonline($user) {
		$this->deleteold();
		$select=$this->select()->where("user = ?",$user)
							   ->from($this->_name,"COUNT(*) AS num");

		return ($this->fetchRow($select)->num==1);
	}

	/**
	* @desc get num online
	* @return int num online
	*/
	public function getonline() {
		$this->deleteold();
		$select=$this->select();
		$select->from('online','COUNT(*) AS num');

		$row=$this->fetchRow($select);

		return $row->num;
	}
	/**
	* @desc upadtinam userio online
	* @param integer $user userio id
	* @return boolean ar pavyko
	*/
	public function updateuser($user) {
		$this->deleteold();
		$this->deletebyuser($user);

		$data=array(
			"user" => $user,
			"time" => time()
		);

		return ($this->insert($data))? true : false;
	}

	public function get_online_list($col=null,$order=null, $clan=false) {
		$this->deleteold();

		$col=($col==null)? array("name") : $col;

		$select=$this->select()->setIntegrityCheck(false);
		$select->from(array("t1" =>"online" ), array('id'))
			   ->join(array("t2" => "users"),"t2.id=t1.user",$col);

		if ($clan) {
			$select->joinLeft(array("t3" => "clan_members"),"t3.user=t2.id",array())
				   ->joinLeft(array("t4" => "clans"), "t4.id=t3.cid", array("clan_title" => "title", "clan_tag" => "tag"));
		}

		if ($order)
			$select->order($order);

		return $this->fetchAll($select);
	}
}
?>
