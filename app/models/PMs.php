<?php
class PMs extends Zend_Db_Table {
	protected $_name = 'PMs';
	protected $_primary = array('id');
	/**
	* @desc pm siuntimas
	* @param Int userio id kuriam siunciam
	* @param Int useiro id is kurio isiunciam
	* @param String temos pavadinimas
	* @param String tekstas
	* @param Int|null zinutes id, i kuria atsakom
	*/
	public function sendpm($to,$from,$title,$text,$fromid=null) {
		Zend_Loader::loadclass('Users');
		$users=new Users();

		if ($users->exsist(null,$to)) {
			if ($fromid) {
				$text.="\n\n-----------------\n\n".($this->getpm($fromid)->text);
			}
			$title=($title)? $title : '(Be pavadinimo)';
			$data=array(
				'to' => $to,
				'from' => $from,
				'text' => $text,
				'title' => $title,
				'date' => mydate());

			return $this->insert($data);
		}
		return false;
	}

	public function getpms($user,$num=null,$start=null) {
		$select=$this->select()->from(array('t1' => 'PMs'))
							   ->join(array('t2' => 'users'),'t2.id=t1.`from`',array('fromname' => 'name'))
							   ->where('t1.`to` = ?',$user)
							   ->order('t1.id DESC');

		if ($num) {
			$select->limit($num,$start);
		}
		$select->setIntegrityCheck(false);

		return $this->fetchAll($select);
	}

	public function count_pm($user) {
		$select=$this->select()->from($this->_name,'COUNT(id) AS num')
							   ->where('`to` = ?',$user);
		$row=$this->fetchRow($select);
		return $row->num;
	}

	public function deletepm($user,$pid) {
		return $this->delete("`to` = $user AND id = $pid");
	}

	public function deleteall($user) {
		return $this->delete("`to` = $user");
	}

	public function getpm($id) {
		$select=$this->select()->from(array('t1' => 'PMs'))
							   ->join(array('t2' => 'users'),'t2.id=t1.`from`',array('fromname' => 'name'))
							   ->where('t1.id = ?',$id);

		$select->setIntegrityCheck(false);

		$row=$this->fetchRow($select);

		$this->makeread($id);

		return $row;
	}

	public function count_unread($user) {
		$select=$this->select()->from($this->_name,'COUNT(id) AS num')
							   ->where('`to` = ?',$user)
							   ->where('`read` = \'?\'', 0);
		$row=$this->fetchRow($select);
		return $row->num;
	}

	public function makeread($id) {
		$where = $this->getAdapter()->quoteInto('id = ?', $id);
		$data=array("read" => 1);
		return ($this->update($data,$where)==1)? true : false;
	}

}
?>