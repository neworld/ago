<?
class Objects extends Zend_Db_Table {
	protected $_name = "objects";
	protected $_primary = array("id");

	private function init() {
		$this->setRowClass("Objectas");
	}

	/**
	* gauname visus mapo fieldus
	*
	* @param Int $mapid mapo id
	* @return Zend_Db_Table_Rowset_Abstract
	*/
	public function getFieldByMap($aid) {
		if (!is_numeric($aid))
			throw new Exception("Nurodytas ne skaicius");

		$select=$this->select()->where("aid = ?", $aid);

		return $this->fetchAll($select);
	}
	/**
	* Pridedame fielda
	*
	* @param Int $x
	* @param Int $y
	* @param Int $ob objekto tipas
	* @param Int $fid  playerio id
	* @param Int $mapid
	* @return array
	*/
	public function addField($x, $y, $type, $aid) {
		if ($this->getByPosition($x, $y, $aid)!==null)
			return false;

		$fighters=new Fighters();


		$data=array(
			"x" => $x,
			"y" => $y,
			"type" => $type,
			"aid" => $aid
		);

		return $this->insert($data);
	}
	/**
	* istrinam visus fieldus pagal mapa
	*
	* @param Int $mapid
	* @return int
	*/
	public function deleteFields($aid) {
		return $this->delete("aid = ?", $aid);
	}
	/**
	* gaunam objekta pagal pozicija
	*
	* @param int $x
	* @param int $y
	* @param int $aid
	* @return Zend_Db_Table_Row_Abstract|null
	*/
	public function getByPosition($x,$y,$aid) {
		$select=$this->select()->where("x = ?", $x)
							   ->where("y = ?", $y)
							   ->where("aid = ?", $aid);

		return $this->fetchRow($select);
	}
}