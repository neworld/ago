<?php
class Help extends Zend_Db_Table {
	protected $_name = 'help';
	protected $_primary = array('id');
	
	public function num() {
		$row=$this->fetchRow($this->select()->from($this->_name,'COUNT(id) AS num'));
		return $row->num;
	}
	
	public function getevents($user,$num=null,$start=null) {
		$select=$this->select()->where('user = ?',$user)
							   ->order("id DESC");
		if ($num)
			$select->limit($num,$start);
			
		return $this->fetchAll($select);
	}
	
	public function get_one($id) {
		return $this->find($id)->getrow(0);
	}	
}
?>
