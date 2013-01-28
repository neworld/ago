<?php
class Itemstemplates extends Zend_Db_Table {
	protected $_name = 'items_template';
	protected $_primary = array('id');
	/**
	* @desc gaunam ietma pagal templeita
	* @param String|null daigto tipas
	* @param id|null daigto id
	* @param int|null daigto maksimalus lygis
	* @param int|null daigto minimalus lygis
	* @param boolean true jeigu reikia naudoti retimu formule
	* @param RACE rase
	* @param int bonus
	* @param int bonus2
	* @return Item
	*/
	public function getiitem($type=null,$id=null,$maxlvl=null,$minlvl=null,$withrare=false, $race=RACE_ALL, $bonus=0, $bonus2=0) {
		$select=$this->select();
		if (in_array($race,array(RACE_MAN,RACE_RJAL))) {
			$select->where("race = '".RACE_ALL."' OR race = '".$race."'");
		}
		if ($type) {
			$select->where("`type` = ?",$type);
		}

		//cristamss cheat
		/*
		if ($id == null && rand(0,7)==0) {
			$id=rand(80,84);
			$maxlvl=null;
			$minlvl=null;
			$withrare=null;
			$type=null;
			$race=null;
		}
		*/
		//end cristams cheat
        
/*        if ($id == null && rand(0, 9) == 0) {
            $id = 133;
            $maxlvl=null;
            $minlvl=null;
            $withrare=null;
            $type=null;
            $race=null;
        }   */

		if ($id==null) {
			if ($maxlvl==null) {
				return false;
			}

			$minlvl=($minlvl==null)? floor($maxlvl*0.7) : $minlvl;

			$max=$this->getMaxLvl(10);

			$check_minlvl=($max<$minlvl)? $max : $minlvl;

			$select->where("((lvl <= $maxlvl AND lvl >= $check_minlvl) OR (no_lvl_limit='Y' AND lvl <= $maxlvl))");
			if ($withrare) {
				$rare=rand(1-$bonus-floor($bonus2/20),100);
				$rare=max($rare, 1);
				$select->where("rare >= ?",$rare);
			}
		} else {
			$select->where("id = ?",$id);
		}

		//Zend_Registry::get('logger')->sql((string)$select);

		$data=$this->fetchAll($select);
		$num=$data->count();

		if ($num==0) {
			return false;
		}
		$i=mt_rand(0,$num-1);

		$row=$data->getRow($i);

		$Iid=$row->id;
		$Ilvl=$row->lvl;
		$Iquality=$row->quality;
		$Icost=$row->cost;
		$Imaxlvl=$row->maxlvl;

		$Iquality_per_lvl=$row->quality_per_lvl;
		$Ibonus_per_quality=$row->bonus_per_quality;

		$addminlvl=($minlvl>$Ilvl)? $minlvl-$Ilvl+floor($bonus2/5) : floor($bonus2/5);
		
		//$addminlvl = max($addminlvl, $maxlvl - $Ilvl);

		if ($maxlvl) {
			$diff=$maxlvl-$Ilvl-$addminlvl;
		} else {
			$diff=0;
		}
		$diff=max(0,$diff);

		//$lvl=$Ilvl+rand(0,$diff)+$addminlvl;
		$lvl=$Ilvl+rand(0,$diff)+$addminlvl;
		
		if ($maxlvl && $lvl > $maxlvl)
			$lvl = $maxlvl;

		$maxlvl=($Imaxlvl>0)? $lvl+$Imaxlvl : 0;

		$diffq=floor(($lvl-$Ilvl)*$Iquality_per_lvl);

		Zend_Loader::loadClass('Itemstemplatesplus');
		$Itemstemplatesplus=new Itemstemplatesplus();
		$rawplus=$Itemstemplatesplus->getplus($Iid);

		$plus=array();
		foreach ($rawplus as $key => $value) {
			if (in_array($key,array('SPEED_DURATON','SPEED_RELOAD'))) {
				$plus[$key] = $value;
			} elseif ($key == 'RANDOM') {
				$skills=include("app/data/skills_DB.php");

				$i=rand(0, count($skills)-1);

				$skillas=array_slice($skills, $i, 1, true);
				$keys=array_keys($skillas);

				$max=$skills[$keys[0]]['MAX'];

				$k=0.5+0.2*$max;

				@$plus[$keys[0]] += ceil(pow($lvl * ($value / 100), $k));
			} else {
				@$plus[$key] += $value + round($diffq * $value * $Ibonus_per_quality / 100);
			}
		}
		$quality=$Iquality+$diffq;
		$cost=round($Icost*pow(((100+$Ibonus_per_quality)/100),sqrt($diffq)*1.3));
		$title=$row->title;

		Zend_Loader::loadClass('Item');
		$item=new Item();

		$item->type=$row->type;
		$item->apie=$row->apie;
		$item->title=$row->title;
		$item->cost=$cost;
		$item->lvl=$lvl;
		$item->maxlvl=$maxlvl;
		$item->plus=$plus;
		$item->quality=$quality;
		$item->template=$Iid;
		$item->race=$row->race;
		$item->ammo=$row->ammo;
		$item->guntype=$row->guntype;
		$item->img=$row->img;
		$item->gunplace=$row->gunplace;
		$item->color=$row->color;
		$item->kiekis=0;

		return $item;
	}

	private function getMaxLvl($num) {
		//return 80;
		$select=$this->select()->limit(1,$num-1)
							   ->from($this->_name,"lvl")
							   ->order("lvl DESC");

//mydebug($this->fetchRow($select)->lvl);

		return $this->fetchAll($select)->getRow(0)->lvl;
	}
}
?>
