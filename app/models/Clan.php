<?php
class Clan extends Zend_Db_Table_Row {
	public function max_member() {
		return 4+$this->lvl;
	}
	/**
	* gaunam logus
	*
	* @param Int $num Irasu skaicius
	* @return Zend_Db_Table_Rowset_Abstract
	*/
	public function get_logs($num=10) {
		$clanlogs=new Clanlogs();
		return $clanlogs->getlast($this->id,$num);
	}
	/**
	* pridedam logo irasa
	*
	* @param string $text zinute
	* @return array|Int
	*/
	public function add_log($text) {
		$clanlogs=new Clanlogs();
		return $clanlogs->add($this->id,$text);
	}

	public function is_user($userid) {
		$clanmembers=new ClanMembers();
		return $clanmembers->check_user($userid, $this->id);
	}

	public function remove() {
		$clanMembers = new ClanMembers();
		$clanInvites = new ClanInvites();

		$clanMembers->removeAll($this->id);
		$clanInvites->removeAll($this->id);

		return $this->delete();
	}
}
?>