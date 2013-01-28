<?php
class Vote extends Zend_Db_Table {
	protected $_name = 'vote';
	protected $_primary = array('id');
	
	public function add($user,$pid,$iid) {
		if ($this->check($user,$pid)) {
			return false;
		}
		return $this->insert(array(
			'user' => $user,
			'pid' => $pid,
			'iid' => $iid
		));
	}
	
	public function count($pid,$iid=null) {
		$select=$this->select()->from($this->_name,'COUNT(*) AS num')
			                   ->where('pid = ?',$pid);
			                   
		if ($iid)
			$select->where('iid = ?', $iid); 
			                   
		return $this->fetchRow($select)->num;
	}
	
	public function check($user,$pid) {
		$select=$this->select()->where('user = ?',$user)
		                       ->where('pid = ?',$pid);
		                       
		return ($this->fetchAll($select)->count()==1);
	}
		
}  
?>