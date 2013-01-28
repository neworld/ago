<?php
class Item {
	public $id;
	public $owner;
	public $place='';
	public $type;
	public $apie;
	public $cost;
	public $template;
	public $title;
	public $race;
	public $ammo;
	public $guntype;
	public $gunplace;
	public $img='';
	public $maxlvl=0;
    public $set;
	/**
	* @desc cia saugomi ginlo pliusai
	* @var array Itemsplus $plus itemo
	*/
	public $plus;
	public $lvl;
	public $quality;
	public $kiekis;
	public $color;

	public function __construct($id=null) {
		if ($id) {

			$data = Zend_Registry::get('db')->fetchRow("
                SELECT t1.*, t2.set 
                FROM `items` AS t1
                RIGHT JOIN `items_template` as t2
                    ON t1.template = t2.id  
                WHERE t1.`id` = $id
            ");

			if (!$data['id']) {
				return 0;
			}

			$this->id = $id;

			$this->owner = $data['owner'];
			$this->place = $data['place'];
			$this->apie = $data['apie'];
			$this->cost = $data['cost'];
			$this->template = $data['template'];
			$this->title = $data['title'];
			$this->type = $data['type'];
			$this->lvl = $data['lvl'];
			$this->quality = $data['quality'];
			$this->ammo = $data['ammo'];
			$this->guntype = $data['guntype'];
			$this->gunplace = $data['gunplace'];
			$this->img = $data['img'];
			$this->race = $data['race'];
			$this->kiekis = $data['kiekis'];
			$this->maxlvl = $data['maxlvl'];
            $this->color = $data['color'];
			$this->set = $data['set'];

			Zend_loader::loadClass('Itemsplus');
			$itemsplus=new Itemsplus();

			$this->plus=$itemsplus->getplus($id);

			if ($this->type=="EMBLEM" && count($this->plus)==0) {
				$skills=include("app/data/skills_DB.php");

				$i=rand(0, count($skills)-1);

				$skillas=array_slice($skills, $i, 1, true);
				$keys=array_keys($skillas);

				$max=$skills[$keys[0]]['MAX'];

				$k=0.5+0.2*$max;

				$itemsplus->setplus($id, array($keys[0] => ceil(pow($this->lvl, $k))));
				$this->plus=$itemsplus->getplus($id);
			}

			if (empty($this->img))
				$this->getimg();
		}
	}

	private function getimg() {
		$itemstemplate=new Itemstemplates();
		$img=$itemstemplate->find($this->template)->getRow(0)->img;
		$this->img=$img;
		$this->save();
	}

	public function replace() {
		$data=Zend_Registry::get('db')->fetchRow("SELECT `place` FROM `items` WHERE `id` = {$this->id}");
		$this->place=$data['place'];
	}
	
	public function img() {
		$src = $this->img;
		if (!$src) {
			$src="/img/item/no-item.png";
		} else {
			$src="/img/item/$src.png";
		}
		
		return $src;
	}
	
	public function generateDiv($id = "item", $class = "", $over = true, $lvl = null, $race = null) {
		$id = "$id-{$this->id}";
			
		if ($over) {
			require_once('app/views/helpers/Run.php');
			require_once('app/views/helpers/ItemOverDiv.php');
			
			$run = new Main_Run();
			$over = new Main_ItemOverDiv();
			
			$run->run("new Overlib('".$over->itemOverDiv($this,$this->lvl,$this->race)."',P('$id'));");
		}
		
		return "<div id='$id' class='item $class' style='background-image:url({$this->img()});'></div>";
	}

	public function setowner($owner) {
		$this->owner=$owner;
		$this->place=null;
		return $this->save();
	}

	public function setplace($place) {
		if ($this->owner) {
			list($type,$num)=explode("_",$place."_");
			if (($place==$this->place)) {
				//return false;
			}
			if (($this->guntype==GUNPLACE_MAIN) && ($place!='GUN_MAIN') && ($this->type!='GUN')) {
				return false;
			}
			if (($this->guntype==GUNPLACE_TWO) && ($place!='GUN_MAIN') && ($this->type!='GUN')) {
				return false;
			}
			if (($type!=$this->type) && ($type!='BANK') && ($type!='JOINAS')) {
				return false;
			}
			$data=Zend_Registry::get('db')->fetchRow("SELECT * FROM `items` WHERE `owner`= {$this->owner} and `place` = ?", $place);
			$id=$data['id'];
			if ($id) {
				$data=array('place' => $this->place);
				Zend_Registry::get('db')->update('items', $data, "`id` = {$id}");
			}
			$this->place=$place;
			return $this->save();
		}
		return false;
	}

	public function consume($num) {
		$this->kiekis-=$num;
		if ($this->kiekis<0) {
			$this->destruct();
			return false;
		} else {
			return true;
		}
	}

	public function save() {
		if ($this->id) {
			$data=array(
				'owner' => $this->owner,
				'kiekis' => $this->kiekis,
				'img' => $this->img,
				'place' => $this->place,
				'maxlvl' => $this->maxlvl
			);

			Zend_Registry::get('db')->update('items', $data, "`id` = {$this->id}");
		} else {
			$data=array(
				'owner' => $this->owner,
				'place' => $this->place,
				'type' => $this->type,
				'apie' => $this->apie,
				'cost' => $this->cost,
				'template' => $this->template,
				'title' => $this->title,
				'lvl' => $this->lvl,
				'race' => $this->race,
				'ammo' => $this->ammo,
				'guntype' => $this->guntype,
				'gunplace' => $this->gunplace,
				'img' => $this->img,
				'kiekis' => $this->kiekis,
				'quality' => $this->quality,
				'color' => $this->color,
				'maxlvl' => $this->maxlvl
			);

			Zend_Registry::get('db')->insert('items',$data);
			$this->id=Zend_Registry::get('db')->lastInsertId();

			Zend_Loader::loadClass('Itemsplus');
			$itemsplus=new Itemsplus();

			$itemsplus->setplus($this->id,$this->plus);
		}
		return true;
	}

	public function destruct() {
		if ($this->id) {
			Zend_Registry::get('db')->delete('items',"id = {$this->id}");
			$this->id=null;
			$this->place=null;
			$this->owner=null;
		}
	}
	public function getDMG() {
		$min=floor($this->plus['DMG_MIN']);
		$max=floor($this->plus['DMG_MAX']);
		$min=max($min,1);
		$max=max($max,1);
		return new Minmax($min,$max);
	}
	public function getDMGpersecond() {
		return round($this->getDMG()->average()/$this->plus['SPEED_DURATON'],2);
	}
	/**
	* @desc gaunam ginklito tipa
	* @return String ginklo tipas
	*/
	public function get_gun_type() {
		if ($this->type=="GUN") {
			$text='';
			switch ($this->guntype) {
				case GUNTYPE_LIGHT : $text="Lengvas šaunamasis"; break;
				case GUNTYPE_MELLE : $text="Šaltasis"; break;
				case GUNTYPE_HEAVY : $text="Sunkus šaunamasis"; break;
			}
			return $text;
		}
	}
	/**
	* @desc Gaunam ginklo vieta
	* @retun String ginklo vieta
	*/
	public function get_gun_place() {
		if ($this->type=="GUN") {
			$text='';
			switch ($this->gunplace) {
				case GUNPLACE_MAIN : $text="Pagrindine ranka"; break;
				case GUNPLACE_ONE : $text="Viena ranka"; break;
				case GUNPLACE_TWO : $text="Abiejomis rankomis"; break;
			}
			return $text;
		}
	}
	public function get_ammo() {
		if ($this->type=="GUN") {
			$text='';
			switch ($this->ammo) {
				case AMMO_BULLET : $text="Kulkos"; break;
				case AMMO_GRANATE : $text="Granatos"; break;
				case AMMO_LASER : $text="Lazeris"; break;
				case AMMO_ROCKET : $text="Raketos"; break;
			}
			return $text;
		}
	}
	public function __toString() {
		return $this->title;
	}

	public function userFriendlyLink() {
		return "[ITEM:{$this->id}:{$this->title}:]";
	}

	public function itemLink() {
		require_once('app/views/helpers/ItemLinks.php');
		$item_links=new Main_ItemLinks();

		return $item_links->itemLinks(clone $this);
	}
	
	public static function countJoinEmblemCost(Item $i1, Item $i2) {
		if ($i1->type != "EMBLEM" || $i2->type != "EMBLEM")
			throw new Exception("Tai ne emblema", ERROR);
			
		$lvl = max($i1->lvl, $i2->lvl); 
		     
		return round(getexp(pow($lvl, 1.09), 20)); 
	}
	
	public static function joinEmblem(Item $i1, Item $i2) {
		if ($i1->type != "EMBLEM" || $i2->type != "EMBLEM")
			throw new Exception("Tai ne emblema", ERROR);
			
		$lvl = max($i1->lvl, $i2->lvl);
			
		$it = new Itemstemplates();
		$i = $it->getiitem(null, 131, $lvl, $lvl);
		
		$i->title = "Emblema II";
		$i->apie = "Tai pas Bugenhageną sujungta emblema. Jeigu jūs ją jungsite kitais būdais, sugadinsite.";
		
		$keys1 = array_keys($i1->plus);
		$key1 = reset($keys1);
		$v1 = $i1->plus[$key1];
		
		$keys2 = array_keys($i2->plus);
		$key2= reset($keys2);
		$v2 = $i2->plus[$key2];
		
		if ($key1 == $key2)
			throw new Exception("Negali atributai būti vienodi", ERROR);
		
		@$i->plus[$key1] += $v1;
		if (rand(0, 10) > 3)
			@$i->plus[$key2] += $v2;
		
		$i->owner = $i1->owner;	
		$i->save();
		$i1->destruct();
		$i2->destruct();
		
		return $i;
	}
	
	public static function countEmblemChangeCost(Item $i) {
		return round(getexp(pow($i->lvl, 1.055), 10));
	}
	
	public static function changeEmblem(Item $i) {
		$lvl = $i->lvl;
		
		$it = new Itemstemplates();
		$new = $it->getiitem(null, 131, $lvl, $lvl);
		
		$new->owner = $i->owner;
		$new->save();
		$i->destruct();
		return new Item($new->id);
	}
}
?>