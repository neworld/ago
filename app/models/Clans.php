<?php
class Clans extends Zend_Db_Table {
	protected $_name = 'clans';
	protected $_primary = array('id');

	public function init() {
		$this->setRowClass('Clan');
	}
	/**
	* Gaunam klana
	*
	* @param Int $id
	* @return Clan
	*/
	public function getClan($id) {
		return $this->find($id)->getRow(0);
	}
	/**
	* Gaunam klana pagal taga
	*
	* @param String $tag tagas
	* @return Clan
	*/
	public function tag2Clan($tag) {
		$select=$this->select()->where('tag like ?',$tag);

		$data=$this->fetchAll($select);
		if ($data->count()==0) {
			return false;
		} else {
			return $data->getRow(0);
		}
	}

	public function getClanByUser($user) {
		$clanmembers=new ClanMembers();
		return $clanmembers->getClanId($user);
	}

	public function makeClan($name,$tag,$leader) {
		if (strlen($name)<3) {
			throw new Exception('Aljanso pavadinimas per trumpas', ERROR);
		}
		if (strlen($tag)<2) {
			throw new Exception('Aljanso tagas per trumpas', ERROR);
		}
		$select=$this->select()->where('title like ?',$name);
		if ($this->fetchAll($select)->count()>0) {
			throw new Exception('Toks pavadinimas užimtas', ERROR);
		}
		$select=$this->select()->where('tag like ?',$tag);
		if ($this->fetchAll($select)->count()>0) {
			throw new Exception('Toks tagas užimtas', ERROR);
		}
		if ($this->getClanByUser($leader)) {
			throw new Exception('Jūs jau esate klane', ERROR);
		}
		$cid=$this->insert(array(
			'title' => $name,
			'tag' => $tag,
			'leader' => $leader
		));
		if (!$cid) {
			throw new Exception('Aljanso sukurti nepavyko', ERROR);
		}
		$clanmembers=new ClanMembers();

		if ($clanmembers->add($leader,$cid)) {
			return $cid;
		} else {
			throw new Exception('Nepavyko jūsų priskirti į aljansą', ERROR);
		}
	}

	public function clanTop() {
		$select=$this->select()->from(array("t3" => $this->_name),array("id", "title", "tag", "info_private", "info_public", "money", "nano", "lvl"))
							   ->setIntegrityCheck(false)
							   ->joinInner(array("t4" =>
									$this->select()->from(array("t1" => 'clan_members'),array("cid","num" => "COUNT(*)"))
												   ->setIntegrityCheck(false)
												   ->join(array("t2" => "users"),"t2.id=t1.user",array("suma" => "SUM(t2.exp)"))
												   ->group("t1.cid")
												   ->where("t2.visible = ?", 1)
									),"t4.cid = t3.id", array("num","suma"))
							   ->join(array("t5" => "users"), "t5.id=t3.leader", array("lordname" => "name", "race"))
							   ->order("t4.suma DESC");

		return $this->fetchAll($select);
	}

}
?>
