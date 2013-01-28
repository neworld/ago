<?php
class Mobs extends Zend_Db_Table {
	protected $_name = 'mobs';
	protected $_primary = array('id');

	public function getmobs($maxlvl=null,$order="lvl ASC") {
		$select=$this->select()->order($order);
		if ($maxlvl)
			$select->where('lvl <= ?',$maxlvl);


		return $this->fetchAll($select);
	}
}
?>