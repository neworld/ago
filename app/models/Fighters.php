<?
class Fighters extends Zend_Db_Table {
	protected $_name = "fighters";
	protected $_primary = array("id");

	public function init() {
		$this->setRowClass("Fighter");
	}
	/**
	* pridedam fighteri
	*
	* @param Int $uid
	* @param int $side
	* @param int $shot_left
	* @param int $aid
	* @param int $x
	* @param int $y
	* @return array
	*/
	public function addFighter($uid, $sid, $shot_left, $aid, $x, $y) {
		if (!in_array($side, array(ATACK_SIDE_LEFT, ATACK_SIDE_RIGHT)))
			throw new Exception("Blogas side");

		$data=array(
			"uid" => $uid,
			"side" => $side,
			"shot_left" => $shot_left,
			"aid" => $aid,
			"x" => $x,
			"y" => $y
		);

		return $this->insert($data);
	}
	/**
	* gaunam fighteri pagal pozicija
	*
	* @param int $x
	* @param int $y
	* @param inr $aid
	* @return Fighter|null
	*/
	public function getByPosition($x, $y, $aid) {
		$select=$this->select()->where("x = ?", $x)
							   ->where("y = ?", $y)
							   ->where("aid = ?", $aid);

		$data=$this->fetchAll($select);

		if ($data->count()==0) {
			return false;
		}

		return $data->getRow(0);
	}
	/**
	* gaunam visus kovotojus
	*
	* @param int $aid
	* @return Zend_Db_Table_Rowset_Abstract
	*/
	public function getFighters($aid) {
		$select=$this->select()->where("aid = ?", $aid)
							   ->order("id ASC");
		return $this->fetchAll($select);
	}
	/**
	* suskaiciuojam visas zaidejo kovas
	*
	* @param int $uid
	* @return int
	*/
	public function countFightShedules($uid) {
		$select=$this->select()->from($this->_name, "COUNT(*) as num")
							   ->where("uid = ?", $uid);

		return $this->fetchRow($select)->num;
	}

	public function getFighter($aid, $uid) {
		$select=$this->select()->where("uid = ?", $uid)
							   ->where("aid = ?", $aid);

		return $this->fetchRow($select);
	}
}