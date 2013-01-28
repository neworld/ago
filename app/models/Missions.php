<?php
class Missions extends Zend_Db_Table {
	protected $_name = "missions";
	protected $_primary = array("id");

	/**
	* gaunam naujos misijos objekta
	*
	* @param String $type misijos tipas
	* @return Mission_Abstract
	*/
	public function generate($type) {
		$this->setRowClass("Mission_$type");

		$new = $this->fetchNew();

		$new->make();

		return $new;
	}

	public function random() {
		$rare = rand(1,100);

		$data = include("app/data/missions.php");

		$a = array();

		foreach ($data as $v)
			if ($v['rare'] >= $rare)
				$a[] = $v;

		$key = rand(0, count($a)-1);
		$m = $a[$key];

		$type = $m['name'];

		return $this->generate($type);
	}
	/**
	* gaunam misija pagal id
	*
	* @param int $id
    * @param boolean $free as darbas turi buti laivas
	* @return Mission_Abstract
	*/
	public function getById($id, $free = false) {
		$select = $this->select()->from($this->_name, "type")
								 ->where("id = ?", $id);

		$data = $this->fetchAll($select);

		if ($data->count() == 0)
			return false;
            
        if ($free) 
            $select->where("uid = 0"); 

		$key = $data->getRow(0)->type;

		$data = include("app/data/missions.php");

		$type = $data[$key]['name'];

		$this->setRowClass("Mission_$type");

		return $this->find($id)->getRow(0);
	}

	public function getByUid($uid) {
		$select = $this->select()->from($this->_name, "id")
								 ->where("uid = ?", $uid);

		$data = $this->fetchAll($select);

		if ($data->count() == 0)
			return false;

		$id = $data->getRow(0)->id;
		return $this->getById($id);
	}
    
    public function fetchAbstract($select) {
        $this->setRowClass("Mission_Abstract");
        return $this->fetchAll($select);
    }
}
?>