<?php
class Names extends Zend_Db_Table {
	protected $_name = "names";
	protected $_primaty = array("id");

	public function generate($sex = NOSEX) {
		//gaunam pirma varda
		$first = $this->get(1, $sex);
		$second = $this->get(2, $sex);

		return "$first $second";
	}

	public function get($type, $sex = NOSEX) {
		//gaunam pirma varda
		$num = $this->count(1, $sex);

		$i = rand(0, $num - 1);

		$select = $this->select()->from($this->_name, "value")
								 ->where("type = ?", 1)
								 ->limit(2, $i);

		if ($sex != NOSEX)
			$select->where('sex = ?', $sex);

		return $this->fetchAll($select)->getRow(0)->value;
	}

	public function count($type, $sex = NOSEX) {
		$select = $this->select()->from($this->_name, 'COUNT(*) as num')
								 ->where("type = ?", $type);

		if ($sex != NOSEX)
			$select->where("sex = ?", $sex);

		return $this->fetchRow($select)->num;
	}


}
?>
