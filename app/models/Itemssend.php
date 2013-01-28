<?php
class Itemssend extends Zend_Db_Table {
	protected $_name = 'items_send';
	protected $_primary = array('id');

	/**
	* Gauti visu daikus.
	*
	* @param Int $user
	* @param boolean $order
	* @return Zend_Db_Table_Rowset_Abstract
	*/
	public function getItems($user,$order=false) {
		$select=$this->select()->from($this->_name, array("*", "IF(`to` = $user, 1 , 0) AS tipas"))
							   ->where("`to` = ?",$user)
							   ->orWhere("`from` = ?", $user);

		if ($order)
			$select->order("tipas DESC");

		return $this->fetchAll($select);
	}
	/**
	* @desc gaunam userio kuriam siunciami daiktai duomenis
	* @param Int vartotojo id
	*/
	public function getToItems($user) {
		$select=$this->select()->where("`to` = ?",$user);

		return $this->fetchAll($select);
	}
	/**
	* Gauname siuntejo daiktus
	*
	* @param Int $user vartotojo id
	* @return Zend_Db_Table_Rowset_Abstract
	*/
	public function getFromItems($user) {
		$select=$this->select()->where("`from` = ?", $user);

		return $this->fetchAll($select);
	}
	/**
	* @desc pridedam daikta
	* @param Item daiktas
	* @param Int kaina
	* @param Int kam siunciam
	* @return Int ar pavyko, ir jei pavyko jo id
	*/
	public function addItems($item, $cost, $to) {
		if ($cost<0)
			throw new Exception("Negalima statyti minusinės kainos",ERROR);

		$id=$this->insert(array(
			"item" => $item->id,
			"cost" => $cost,
			"to" => $to,
			"from" => $item->owner
		));

		if ($id) {
			$item->owner=0;
			$item->place='';
			$item->save();
			return $id;
		} else {
			return false;
		}
	}

	/**
	* @desc pasalinam daikta is listo ir sugrazinam siuntejui
	* $param Int id listo
	* @param String priezastis
	*/
	public function remove($id,$reason=null) {
		$data=$this->find($id);
		if ($data->count()==0)
			throw new Exception("Neegzistuojantis id",ERROR);

		$row=$data->getRow(0);

		$item=new Item($row->item);

		$item->owner=$row->from;
		$item->save();

		if ($reason) {
            $users = new Users();
            $user = $users->getuser($row->from, true, true);
			$user->sendevent("Jums buvo sugražintas daiktas {$item->itemLink()} iš privačių mainų, nes $reason", EVENT_OTHER);
			unset($user);
		}

		$row->delete();
	}

	/**
	* @desc pasalinam visus senus iraus
	* @return Int kiek irasu pasalinta
	*/
	public function removeold() {
		$select=$this->select()->from($this->_name,array("id"))
							   ->where("date < ?",date(DATE_FORMAT2,time()-3600*24));

		$data=$this->fetchAll($select);

		foreach ($data as $v) {
			$this->remove($v->id,"adresatas nepasiėmė daikto per 24 val.");
		}

		return $data->count();
	}

	public function buyItem($id) {
		$data=$this->find($id);

		if ($data->count()==0)
			return false;

		$row=$data->getRow(0);

		$item=new Item($row->item);
		$item->owner=$row->to;
		$item->save();

		$row->delete();

		return true;
	}

	public function count($user) {
		$select=$this->select()->from($this->_name,array("num" => "COUNT(*)"))
							   ->where("`from` = ?",$user);

		return $this->fetchRow($select)->num;
	}
}
?>