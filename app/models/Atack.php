<?php
class Atack extends Zend_Db_Table {
	protected $_name = "atack";
	protected $_primary = array("id");

	const TEMPDIR = "/dev/shm/atacks";
	/**
	* gaunam ar useris tuo laiku uzimtas
	*
	* @param int $uid
	* @param int $time
	* @return boolean
	*/
	public function checkFighter($uid, $time, $started=false) {
		$mysql_time=$time;
		if (is_numeric($time))
			$mysql_time=date(DATE_FORMAT2, $time);

		$select=$this->select()->setIntegrityCheck(false)
							   ->from(array("t1" => $this->_name), "COUNT(*) as num")
							   ->join(array("t2" => "fighters"), "t1.id=t2.aid", array())
							   ->where("t2.uid = ?", $uid)
							   ->where("t1.start < ?", $mysql_time)
							   ->where("t1.end > ?", $mysql_time);

		if ($started)
			$select->where("t1.fight = 'Y'");

		return ($this->fetchRow($select)->num==1);
	}

	public function inAtack($uid) {
		$mysql_time=date(DATE_FORMAT2);

		$select=$this->select()->setIntegrityCheck(false)
							   ->from(array("t1" => $this->_name), "COUNT(*) as num")
							   ->join(array("t2" => "fighters"), "t1.id=t2.aid", array())
							   ->where("t2.uid = ?", $uid)
							   ->where("t1.start < ?", $mysql_time)
							   ->where("t1.fight = 'N'");

		return ($this->fetchRow($select)->num==1);
	}

	public function init() {
		if (rand(1,100)==1)
			$this->flushTrash();
	}

	private function flushTrash() {
		$time=date(DATE_FORMAT2);
		$this->delete("end < '$time'");

		$select=$this->select()->setIntegrityCheck(false)
							   ->from(array("t1" => "fighters"), array("id"))
							   ->joinLeft(array("t2" => $this->_name), "t2.id=t1.aid", array("aid" => "id"))
							   ->where("t2.id IS NULL");
		//DebugBreak();
		$fighters=new Fighters();
		$data=$this->fetchAll($select);
		foreach ($data as $v) {
			$fighters->delete("id = {$v->id}");
		}

		$select=$this->select()->setIntegrityCheck(false)
							   ->from(array("t1" => "objects"), array("id"))
							   ->joinLeft(array("t2" => $this->_name), "t2.id=t1.aid", array("aid" => "id"))
							   ->where("t2.id IS NULL");

		$objects=new Objects();
		$data=$this->fetchAll($select);
		foreach ($data as $v) {
			$objects->delete("id = {$v->id}");
		}
	}

	public function checkFighterByRezis($uid, $start, $end) {
		if ($this->checkFighter($uid, $start))
			return true;

		if ($this->checkFighter($uid, $end))
			return true;

		$select=$this->select()->setIntegrityCheck(false)
							   ->from(array("t1" => $this->_name), "COUNT(*) as num")
							   ->join(array("t2" => "fighters"), "t1.id=t2.aid", array())
							   ->where("t2.uid = ?", $uid)
							   ->where("t1.start > ?", $start)
							   ->where("t1.end < ?", $end);

		return ($this->fetchRow($select)->num==1);
	}

	public function getAllFigterShedule($uid) {
		$mysql_time=date(DATE_FORMAT2);
		$select=$this->select()->setIntegrityCheck(false)
							   ->from(array("t1" => $this->_name), "*")
							   ->join(array("t2" => "fighters"), "t1.id=t2.aid", array("f_auto" => "auto", "f_side" => "side"))
							   ->where("t2.uid = ?", $uid)
							   ->where("t1.end > ?", $mysql_time);

		return $this->fetchAll($select);
	}

	public function isFight($aid) {
		$data=$this->find($aid);
		if ($data->count()==0)
			throw new Exception("Tokia kova neegzistuoja", ERROR);

		return ($data->getRow(0)->fight=='Y');
	}

	public function countCreator($uid) {
		$select=$this->select()->from($this->_name, "COUNT(*) as num")
							   ->where("creator = ?", $uid);

		return $this->fetchRow($select)->num;
	}

	public function getCurrentFight($uid) {
		$date=date(DATE_FORMAT2);
		$select=$this->select()->setIntegrityCheck(false)
							   ->from(array("t1" => $this->_name), "id")
							   ->join(array("t2" => "fighters"), "t1.id=t2.aid", array())
							   ->where("t2.uid = ?", $uid)
							   ->where("t1.start < ?", $date)
							   ->where("t1.end > ?", $date)
							   ->where("t1.fight = 'Y'");

		$data=$this->fetchAll($select);
		if ($data->count()==0)
			return false;

		return $data->getRow(0)->id;
	}

	public function publishTemp() {
		$folder_name = Atack::TEMPDIR . '/'. getsid();

		if (!file_exists($folder_name))
			if (!mkdir($folder_name, 0777, true))
				throw new Exception("Negalima aktyvuoti kešo", ERROR);

		$file_name = "a{$this->id}";

		//padarom duomenu struktura
		$atacks = new Atack();
		$fighters = new Fighters();
		$objects = new Objects();
		$atacklogs = new Atacklogs();

		$fighteriai = $this->fighters->getFighters($this->aid);
		$obejctai = $this->objects->getFieldByMap($this->aid);

		$data = new stdClass();

		$data->fighters = array();
		$data->objects = array();

		foreach ($fighteriai as $v)
			$data->fighters[] = new Afighter($v);

	}
}
?>
