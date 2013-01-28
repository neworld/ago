<?php
class Polls extends Zend_Db_Table {
	protected $_name = 'polls';
	protected $_primary = array('id');
	
	public function getpoll($id) {
		$data=$this->find($id);
		if ($data->count()==0) {
			return  false;
		}
		
		$row=$data->getRow(0);
		
		$ob=new Poll();
		$ob->date=$row->date;	 	
		$ob->on=$row->on;	 	
		$ob->until=$row->until;	 	
		$ob->title=$row->title;	 	
		$ob->id=$row->id;
		
		$pollitems=new PollItems();
		
		$select=$pollitems->select()->where('poll = ?', $row->id);
		
		$data=$pollitems->fetchAll($select);
			
		$vote=new Vote();
		
		Zend_Loader::loadClass('PollItem');
		
		foreach ($data as $v) {
			$num=$vote->count($row->id,$v->id);
			$ob->add(new PollItem($v->id, $row->id, $v->title, $num));
		}
		
		return $ob;
	} 
	
	public function getlast($user) {
		$select=$this->select()->from(array("t1" => $this->_name), "id")
			                   ->joinLeft(array("t2" => 'vote'),"t2.pid=t1.id AND t2.user=$user", array())
			                   ->where("t2.id IS NULL")
			                   ->where("t1.on='ON'")
			                   ->limit(1)
			                   ->order("t1.id DESC")
			                   ->setIntegrityCheck(false);
		//echo $select;
	
		$data=$this->fetchAll($select);
		
		if ($data->count()==1) {
			return  $this->getpoll($data->getRow(0)->id);
		} else {
			return false;
		}
	}	
}  
?>