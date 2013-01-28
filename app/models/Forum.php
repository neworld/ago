<?php
class Forum extends Zend_Db_Table {
	protected $_name = 'forum';
	protected $_primary = array('id');

	private function setlid($id) {
		$select=$this->select()->where('root = ?',$id)
							   ->orWhere('id = ?',$id)
							   ->order('id DESC')
							   ->limit(1);

		$row=$this->fetchRow($select);

		$this->update(array('lid' => $row->id),"id = $id");
	}

	public function get_forum($subid, $max=20,$start=0, $cid=0) {
		$select=$this->select();

		$select->setIntegrityCheck(false)
			   ->from(array("t1" => $this->_name),array("id","title","date","(SELECT COUNT(*) FROM {$this->_name} WHERE `root`=t1.id GROUP BY `root`) AS num"))
			   ->join(array("t2" => "users"),"t2.id=t1.user",array("name"))
			   ->joinLeft(array("t3" => "forum"),"t3.id=t1.lid",array("lastdate" => "date"))
			   ->joinLeft(array("t4" => "users"),"t4.id=t3.user",array("lastname" => "name"))
			   ->order("t1.lid DESC")
			   ->where("t1.subid = ?",$subid)
			   ->where("t1.root = 0")
			   ->where("t1.cid = ?",$cid)
			   ->limit($max,$start);
			   //echo $select;
		return $this->fetchAll($select);
	}

	public function count($subid=null,$root=null, $cid=0) {
		$select=$this->select()->from($this->_name,array("num" => "COUNT(*)"));

		$select->where('cid = ?',$cid);

		if ($subid)
			$select->where("subid = ?",$subid);

		if ($root!==null)
			$select->where("root = ?", $root);

		return $this->fetchRow($select)->num;
	}

	public function getmsg($id) {
		$select=$this->select()->setIntegrityCheck(false)
							   ->from(array("t1" => $this->_name),array("id","date","title","text","user"))
							   ->join(array("t2" => "users"),"t2.id=t1.user",array("name"))
							   ->where("t1.id = ?",$id)
							   ->orWhere("t1.root = ?",$id)
							   ->order("t1.id ASC");

		return $this->fetchAll($select);
	}

	public function newTopic($title,$text,$user,$subid,$cid=0) {
		$id=$this->insert(array(
			'title' => $title,
			'text' => substr($text,0,5000),
			'user' => $user,
			'subid' => $subid,
			'cid' => $cid
		));
		$this->update(array('lid' => $id),"id=$id");
		return $id;
	}

	public function newPost($text,$user,$subid,$root,$cid=0) {
		$ret=$this->insert(array(
			'text' => substr($text,0,5000),
			'user' => $user,
			'root' => $root,
			'subid' => $subid,
			'cid' => $cid
		));

		$this->setlid($root);

		return $ret;
	}

	public function getLastDate($subid=null,$root=null,$cid=0) {
		$select=$this->select()->from($this->_name,array("date"))
							   ->order("id DESC")
							   ->where("cid = ?",$cid)
							   ->limit(1);

		if ($subid)
			$select->where("subid = ?",$subid);

		if ($root!==null)
			$select->where("root = ?", $root);

		return @$this->fetchRow($select)->date;
	}

	public function getLastName($subid=null,$root=null,$cid=0) {
		$select=$this->select()->from(array("t1" => $this->_name,array()))
							   ->setIntegrityCheck(false)
							   ->order("t1.id DESC")
							   ->where("cid = ?",$cid)
							   ->join(array("t2" => "users"), "t2.id=t1.user", array("name"))
							   ->limit(1);

		if ($subid)
			$select->where("subid = ?",$subid);

		if ($root!==null)
			$select->where("root = ?", $root);

		return @$this->fetchRow($select)->name;
	}
}
?>