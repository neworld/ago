<?php
class Market extends Zend_Db_Table {
	protected $_name = 'market';
	protected $_primary = array('id');

	public function getitems($num=50,$start=null,$min_lvl=null,$max_lvl=null,$max_cost=null,$type=null,$user=null,$race=null, $title=null) {
		$select=$this->select()->setIntegrityCheck(false)
							   ->order("t1.id DESC")
							   ->from(array("t1" => $this->_name),array("id","cost","owner","item","UNIX_TIMESTAMP(date) as time"))
							   ->join(array("t2" => "items"),"t2.id=t1.item",array())
							   ->join(array("t3" => "users"),"t3.id=t1.owner",array("ownername" => "name"))
							   ->where('(t2.owner) IS NULL OR (t2.owner = 0)');

		$select->limit($num,$start);

		if ($min_lvl && !$user)
			$select->where("t2.lvl >= ?",$min_lvl);

		if ($max_lvl && !$user)
			$select->where("t2.lvl <= ?",$max_lvl);

		if ($max_cost && !$user)
			$select->where("t1.cost <= ?",$max_cost);

		if ($type && !$user)
			$select->where("t2.`type` = ?", $type);

		if ($user)
			$select->where("t1.owner = ?", $user);

		if (($race!==null) && !$user)
			$select->where("(t2.race = '".RACE_ALL."') OR (t2.race = '$race')");

		if ($title && !$user)
			$select->where("title like '%$title%'");

		$data=$this->fetchAll($select);
		$result=array();

		$users=new Users();

		foreach ($data as $v) {
			$ob=new stdClass();
			$ob->id=$v->id;
			$ob->cost=$v->cost;
			$ob->owner=$v->owner;
			$ob->itemid=$v->item;
			$ob->time=$v->time;
			$ob->ownername=$users->getname($v->owner);
			$ob->item=new Item($v->item);
			$result[]=$ob;
			unset($ob);
		}
		return $result;
	}

	public function count($min_lvl=null,$max_lvl=null,$max_cost=null,$type=null,$user=null,$race=null,$title=null) {
		$select=$this->select()->setIntegrityCheck(false)
							   ->order("t1.id DESC")
							   ->from(array("t1" => $this->_name),"COUNT(*) AS num")
							   ->join(array("t2" => "items"),"t2.id=t1.item")
							   ->where('(t2.owner) IS NULL OR (t2.owner = 0)');

		if ($min_lvl && !$user)
			$select->where("t2.lvl >= ?",$min_lvl);

		if ($max_lvl && !$user)
			$select->where("t2.lvl <= ?",$max_lvl);

		if ($max_cost && !$user)
			$select->where("t1.cost <= ?",$max_cost);

		if ($type && !$user)
			$select->where("t2.`type` = ?", $type);

		if ($user)
			$select->where("t1.owner = ?", $user);

		if (($race!==null) && !$user)
			$select->where("(t2.race = '".RACE_ALL."') OR (t2.race = '$race')");

		if ($title && !$user)
			$select->where("title like '%$title%'");

		//echo $select;
		return $this->fetchRow($select)->num;
	}

	public function additem($id,$owner,$cost) {
		$item=new Item($id);
		if ($item->owner!=$owner)
			return false;

///		if (($cost>$item->cost*20) || ($cost<$item->cost*0.2))
//			return false;

		$item->setowner(0);
		$item->setplace('');
		$item->save();

		$data=array(
			"owner" => $owner,
			"cost" => $cost,
			"item" => $id
		);

		return ($this->insert($data))? true : false;
	}

	public function count_by_user($uid) {
		$select=$this->select()->from($this->_name,array("num" => "COUNT(*)"))
							   ->where('owner = ?', $uid);
		$row=$this->fetchRow($select);

		return @$row->num;
	}

	public function getcost($id) {
		$data=$this->find($id);
		if ($data->count()==1) {
			return $this->find($id)->getRow(0)->cost;
		} else {
			return null;
		}
	}

	public function getowner($id) {
		$data=$this->find($id);
		if ($data->count()==1) {
			return $this->find($id)->getRow(0)->owner;
		} else {
			return null;
		}
	}

	public function buy($id,$owner) {
		$data=$this->find($id);
		if ($data->count()==1) {
			$row=$this->find($id)->getRow(0);
			$item=new Item($row->item);
			$item->setowner($owner);
			$item->save();

			$row->delete();
			return true;
		} else {
			return false;
		}
	}
	/**
	* @desc gaunam daikta pagal marketo id
	* @param Int marketo id
	* @return Item daiktas
	*/
	public function getItem($id) {
		$data=$this->find($id);
		if ($data->count()==0)
			return false;

		return new Item($data->getRow(0)->item);
	}
}
?>
