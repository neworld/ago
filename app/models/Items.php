<?php
class Items extends Zend_Db_Table {
	protected $_name='items';
	protected $_primary = array('id');
	
	public function create($title,$img,$gunplace=0,$ammo=AMMO_NO,$guntype=GUNTYPE_NP,$owner=0,$lvl=1,$quality=1,$cost=1,$type='OTHER',$apie='',$race=RACE_ALL,$plus=null) {				
		Zend_Loader::loadClass('Item');
		$item=new Item();
	
		$item->type=$type; 
		$item->apie=$apie; 
		$item->title=$title;
		$item->cost=$cost;
		$item->lvl=$lvl; 
		$item->plus=$plus; 
		$item->quality=$quality;
		$item->race=$race;
		$item->owner=$owner;
		$item->ammo=$ammo;
		$item->guntype=$guntype;
		$item->img=$img;
		$item->gunplace=$gunplace;
		
		$item->save();
	}
	
	public function getitems($uid) {
        $select = $this->select()->setIntegrityCheck(false)
                                 ->from(array("t1" => $this->_name), "*")
                                 ->where('owner = ?', $uid)
                                 ->joinRight(array("t2" => "items_template"), "t2.id = t1.template", "set");
		$data=$this->fetchAll($select);
        $sets = array();
		if ($data->count()>0) {
			Zend_Loader::loadClass('Item');
			$rez=array();
			foreach ($data as $row) {
				$rez[]=new Item($row->id);
                if ($row->set && strpos($row->place, "BANK_") !== 0 )
                    @$sets[$row->set]++;
			}
            ItemsSets::setSets($uid, $sets);
			return $rez;
		}
        ItemsSets::setSets($uid, array());
		return null;
	}
	/**
	* @desc sukurti itema is templeito ir ji uzsaugoti
	* @param int $owner itemo savininkas
	* @param string $type itemo vieta
	* @param int $id jeigu norime sukurti is tam tikro templeito
	* @param int $maxlvl didziausias galimas lvl
	* @param int $minlvl maziausias galimas lvl
	* @param boolean $withrare ar isrinkti itemus pagal retuma
	* @param RACE_MAN|RACE_RJAL|RACE_ALL $race itemo rase
	* @return boolean as uzsaugojo
	*/
	public function from_template($owner,$type=null,$id=null,$maxlvl=null,$minlvl=null,$withrare=false, $race=RACE_ALL) {
		Zend_Loader::loadClass('Itemstemplates');
		$Itemstemplates=new Itemstemplates();
		
		$item=$Itemstemplates->getiitem($type,$id,$maxlvl,$minlvl,$withrare,$race);
		$item->setowner($owner);
		return $item->save();
	}	
}
		
				
				
?>
