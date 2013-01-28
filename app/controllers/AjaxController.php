<?php
class AjaxController extends Zend_Controller_Action {
	public function init() {
		//isjungiam automatini renderinima:
		$this->_helper->viewRenderer->setNoRender(true);
	}
	/**
	* @desc outputinam teksta
	* @param string $text
	*/
	private function output($text) {
		$this->getResponse()->appendBody($text);
	}
	/**
	* @desc gaunam useri
	* @return User
	*/
	private function get_user() {
		Zend_Loader::loadClass('Session');
		$session=new Session();
		if ($session->get()) {
			Zend_Loader::loadClass('Users');
			$users=new Users();
			return $users->getuser($session->get());
		} else {
			return false;
		}
	}
	/**
	* @desc gaunam daikto informacija
	*/
	public function itemAction() {
		include("app/views/helpers/ItemOverDiv.php");
		$helper=new Main_ItemOverDiv();

		$itemid=$this->_getParam('itemid');

		$user=$this->get_user();

		if (!$user)
			return 0;

		$item=new Item($itemid);

		if (!$item->id)
			throw new Exception('Deja šitas daiktas nebeegzistuoja',ERROR);

		$this->output($helper->itemOverDiv($item,$user->getlvl(),$user->getrace()));
	}
	/**
	* @desc gauname ar toks useris egzistuoja
	*/
	public function checknameAction() {
		$name=$this->_getParam('name');
		if ($name) {
			Zend_Loader::loadClass('Users');
			$users=new Users();
			$a=($users->exsist($name))? 1 : 0;
			$this->output($a);
		}
	}
	/**
	* @desc updtinam skila
	*/
	public function skillAction() {
		$user=$this->get_user();
		if ($user===false) {
			$this->output("Esate neprisijungęs");
			return 0;
		}
		$type=$this->_getParam('type');
		$down=$this->_getParam('down');
		$skills_db=include('app/data/skills_DB.php');
		if (!isset($skills_db[$type])) {
			$this->output("Neegzistuojantis skilas $type");
			return 0;
		}
		$skillas=$user->getskillbytype($type);
		if ($down) {
			if ($skillas->lvl==1) {
				$this->output("Jūs nebegalite sumažinti šio įgūdžio lygio");
				return;
			} else {
				$cost=$skillas->getlowcost()*0.5;
				if ($skillas->down()) {
					$user->addmoney($cost);
				}
			}
		} else {
			$cost=$skillas->getcost();
			if ($skillas->upg()) {
				$user->addmoney(-1*$cost);
			} else {
				$this->output("Neužtenka agonų");
				return 0;
			}
		}

		$skill=array();
		foreach($user->skills as $key => $v) {
			$skill[$v->type]=$v->getcost();
		}
		$ob=new stdClass();
		$ob->money=$user->getmoney();
		$ob->skill=$skill;
		$ob->skill_type=$type;
		$ob->lvl=$user->getskillbytype($type)->lvl;
		$this->output(json_encode($ob));
	}
	public function getitemplaceAction() {
		$user=$this->get_user();
		if ($user===false) {
			$this->output("Esate neprisijungęs");
			return 0;
		}
		$item_db=include('app/data/item_DB.php');
		$item_db["BANK"]=true;

		$type=explode("_",$this->_getParam('item'));

		if (!in_array($type[0],$item_db)) {
			$this->output("Nera tokio tipo daigtu");
			return 0;
		}

		$item=$user->getitembyplace($this->_getParam('item'));
		if (!$item)
			return 0;
		$item_place=explode('_',$item->place);

		$ob=new stdClass();
		$ob->id=$item->id;
		$ob->title=$item->title;
		$ob->cost=$item->cost;

		//generuojam vietas
		$places=array();

		if ($item_place[0]=="BANK") {
			if (
					($item->lvl<=$user->getlvl()) &&
					(
						($item->race==RACE_ALL) ||
						($item->race==$user->getrace())
					)
			) {
				if ($item->type=="GUN") {
					if ($item->gunplace==GUNPLACE_MAIN) {
						$places["GUN_MAIN"]="{$item_db["GUN_MAIN"]['title']} (".$user->getitembyplace("GUN_MAIN").")";
					} elseif (($item->gunplace==GUNPLACE_TWO) && (!$user->getitembyplace("GUN_SECOND"))) {
						$places["GUN_MAIN"]="{$item_db["GUN_MAIN"]['title']} (".$user->getitembyplace("GUN_MAIN").")";
					} elseif ($item->gunplace==GUNPLACE_ONE) {
						if ($user->getitembyplace("GUN_MAIN"))
							if ($user->getitembyplace("GUN_MAIN")->gunplace!=GUNPLACE_TWO)
								$places["GUN_SECOND"]="{$item_db["GUN_SECOND"]['title']} (".$user->getitembyplace("GUN_SECOND").")";
						$places["GUN_MAIN"]="{$item_db["GUN_MAIN"]['title']} (".$user->getitembyplace("GUN_MAIN").")";
					}
				} else {
					for ($x=1;$x<=$item_db[$item->type]['num'];$x++) {
						$TYPE=($item_db[$item->type]['num']==1)? $item->type : "{$item->type}_{$x}";
						$num=($item_db[$item->type]['num']==1)? '' : $x;
						$places[$TYPE]="{$item_db[$item->type]['title']} $num ({$user->getitembyplace($TYPE)})";
					}
				}
			}
		}
		for ($x=1; $x<=$user->get_bank_place(); $x++) {
			$TYPE="BANK_$x";
			if (($item_place[0]=="BANK") || (!$user->getitembyplace($TYPE))) {
				$places[$TYPE]="Bankas $x ({$user->getitembyplace($TYPE)})";
			}
		}
		$ob->places=$places;

		//gaunam maksimalia vieta turguje
		$market=new Market();
		$ob->cansell=(max_market_slot($user->get_skill_sum("TRADE"))>$market->count_by_user($user->getid()))? 1 : 0;

		$this->output(json_encode($ob));
	}
	public function getpmAction() {
		$user=$this->get_user();
		if ($user===false) {
			$this->output("Esate neprisijungęs");
			return 0;
		}
		$PM=new PMs();
		$pm=$PM->getpm($this->_getParam('pid'));
		if (!$pm) {
			$this->output("Žinutės nuskaityti nepavyko");
			return false;
		}

		$filterChain = new Zend_Filter();
		$filterChain->addFilter(new BBcode());
		$filterChain->addFilter(new ItemLink());
		$filterChain->addFilter(new ActivateUrl());
		$filterChain->addFilter(new Smilein());

		$ob=new stdClass();
		$ob->text=$filterChain->filter($pm->text);
		$ob->fromname=$pm->fromname;
		$ob->id=$pm->id;
		$ob->title=$pm->title;

		$this->output(json_encode($ob));
	}
	public function onlineAction() {
		$online=new Online();
		$session=new Session();

		if ($session->get()) {
			$online->updateuser($session->get());
		}
		$this->output($online->getonline());
	}

	public function voteAction() {
		$pid=$this->_getParam('pid');
		if (!$pid) {
			$this->output('nesiunciate pid');
			return 0;
		}

		$pollitems=new PollItems();
		$data=$pollitems->find($pid);

		if ($data->count()==0) {
			$this->output('nesiunciate pid');
			return 0;
		}

		$poll=$data->getRow(0)->poll;

		$vote=new Vote();
		$session=new Session();

		$vote->add($session->get(),$poll,$pid);
		$this->output(0);
		return 0;
	}

	public function fotoAction() {
		$adapter = new Zend_File_Transfer_Adapter_Http();

		$folder='/tmp/agonija/foto';
		if (!file_exists($folder)) {
			mkdir($folder,0777,true);
		}

		$adapter->setDestination($folder);

		$adapter->addValidator('ImageSize',false,
					  array('minwidth' => 1,
							'maxwidth' => 9999999,
							'minheight' => 1,
							'maxheight' => 9999999)
		);

		//$adapter->addValidator('IsImage', false);

		if ($adapter->isValid('myfile')) {
			if ($adapter->receive()) {
				$msg = "0";
				$title = $adapter->getFileName();
				$dir = $adapter->getDestination();
			} else {
				$msg = implode('\n',$adapter->getMessages());
			}
		} else {
			$msg = 'Tai ne paveikslėlis';
		}

		if ($msg=='0') {
			$k=0;
			$maxwidth=140;
			$maxheight=180;

			$file=$title;

			$a=explode('.',$file);
			$format=strtoupper(end($a));

			list($width,$height)=getimagesize($file);

			switch ($format) {
				case "JPG" :
				case "JPEG" : $img=imagecreatefromjpeg($file); break;
				case "PNG" : $img=imagecreatefrompng($file); break;
				case "GIF" : $img=imagecreatefromgif($file); break;
				case "WBMP" : $img=imagecreatefromwbmp($file); break;
				default: $msg="Nepalaikomas formatas";
			}

			if ($msg=='0') {
				$k=min($width/$maxwidth,$height/$maxheight);

				$cropwidth=round($maxwidth*$k);
				$cropheight=round($maxheight*$k);

				$nx=round(($width-$cropwidth)/2);
				$ny=round(($height-$cropheight)/2);

				//$msg="$cropwidth $cropheight";

				$cropimg=imagecreatetruecolor($maxwidth,$maxheight);
				imagecopyresampled($cropimg,$img,0,0,$nx,$ny,$maxwidth,$maxheight,$cropwidth,$cropheight);

				$DES='/www/agonija/foto';
				$new_name=genkey(12).'.png';

				while (file_exists("$DES/$new_name")) {
					$new_name=genkey(12).'.png';
				}

				imagePNG($cropimg, "$DES/$new_name");

				$user=$this->get_user();
				$uid=$user->getid();
				$users=new Users();
				$data=array("img" => $new_name);
				$users->changedata($uid,$data);
			}
			unlink($file);
		}

		$this->output('
			<script language="javascript" type="text/javascript">
				window.top.window.stopfotoUpload("'.$msg.'");
			</script>
		');
	}
	public function pmarketAction() {
		$itemssend=new Itemssend();
		$user=$this->get_user();
		$users=new Users();

		$data=$itemssend->find($this->_getParam('pm_id'));
		if ($data->count()==0) {
			$this->output('Neegzistuojantis siųlymas');
			return false;
		}

		$row=$data->getRow(0);

		$item=new Item($row->item);

		$ob=new stdClass();
		$ob->cost=$row->cost;
		$ob->link=$item->itemLink();
		$ob->title=$item->title;
		$ob->from=$users->getname($row->from);

		$this->output(json_encode($ob));
	}

	public function changetitleAction() {
		$mid=$this->_getParam('mid');
		if (!$mid)
			throw new Exception('Nenuodytas klaniskis',ERROR);

		$user=$this->get_user();
		$clans=new Clans();

		$cid=$clans->getClanByUser($user->getid());
		$clan=$clans->getClan($cid);

		if ($clan->leader!=$user->getid())
			throw new Exception('Jūs ne lyderis',ERROR);

		$clanmembers=new ClanMembers();
		$select=$clanmembers->select()->where('user = ?',$mid);
		$data=$clanmembers->fetchAll($select);
		if ($data->count()==0)
			throw new Exception('Tai ne jūsų klaniškis',ERROR);

		$row=$data->getRow(0);
		$row->title=$this->_getParam('value');
		$row->save();

		$this->output($this->_getParam('value'));
	}

	public function editclaninfoAction() {
		$type=$this->_getParam("type");
		if (!in_array($type,array("private","public")))
			throw new Exception('Klaida!',ERROR);

		$clans=new Clans();
		$user=$this->get_user();

		$cid=$clans->getClanByUser($user->getid());
		$clan=$clans->getClan($cid);

		$text=$this->_getParam("value");

		if ($type=="public") {
			$clan->info_public=$text;
		} elseif ($type=="private") {
			$clan->info_private=$text;
		}

		$clan->save();

		include("app/views/helpers/FormatBBcode.php");
		$helper=new Main_FormatBBcode();

		$this->output($helper->formatBBcode($text));
	}

	public function getclaninfoAction() {
		$type=$this->_getParam("type");
		if (!in_array($type,array("private","public")))
			throw new Exception('Klaida!',ERROR);

		$clans=new Clans();
		$user=$this->get_user();

		$cid=$clans->getClanByUser($user->getid());
		$clan=$clans->getClan($cid);

		if ($type=="public") {
			$text=$clan->info_public;
		} elseif ($type=="private") {
			$text=$clan->info_private;
		}

		$this->output($text);
	}

	public function getnewAction() {
		$nid=$this->_getParam('nid');
		$news=new News();

		$data=$news->find($nid);
		if ($data->count()==0)
			throw new Exception ("Tokios naujienos nėra", ERROR);

		$filter = new Zend_Filter();
		$filter->addFilter(new BBcode());
		$filter->addFilter(new Smilein());
		$filter->addFilter(new ActivateUrl());

		$this->output($filter->filter($data->getRow(0)->text));
	}

	public function getforumAction() {
		$fid=$this->_getParam('fid');
		$forum=new Forum();

		$data=$forum->find($fid);
		if ($data->count()==0)
			throw new Exception("Deja toks postas neegzistuoja",ERROR);

		$this->output($data->getRow(0)->text);
	}

	public function editforumAction() {
		$text=$this->_getParam('value');

		if (!trim($text))
			throw new Exception("Negalima siųsti tusčio teksto",ERROR);

		$forum=new Forum();

		$data=$forum->find($this->_getParam('fid'));

		if ($data->count()==0)
			throw new Exception("Neegzsistuojantis postas",ERROR);

		$row=$data->getRow(0);

		$row->text=$text;
		$row->save();

		include("app/views/helpers/FormatBBcode.php");
		$helper=new Main_FormatBBcode();

		$this->output($helper->formatBBcode($text));
	}

	public function transferfromsafeAction() {
		$num=$this->_getParam('num');

		if (!$user=$this->get_user())
			throw new Exception("Esate neprisijungęs vartotojas", ERROR);

		$user->transfer_to_money($num);

		$ob = new stdClass();
		$ob->money = $user->getmoney();
		$ob->safe = $user->get_safe_money();

		$this->output(json_encode($ob));
	}

	public function quicksellAction() {
		$type=$this->_getParam('type');
		if (strpos($type,"BANK")!==0)
			throw new Exception("Daiktus galima pardavinėti tik iš inventoriaus",ERROR);

		if (!$user=$this->get_user())
			throw new Exception("Nepavyko įkrauti vartotojo",ERROR);

		if (!$item=$user->getitembyplace($type))
			throw new Exception("Nepavyko įkrauti daikto",ERROR);

		$cost=ceil($item->cost*(0.5+0.002*$user->get_skill_sum("TRADE")));
		$user->addmoney($cost);
		$item->destruct();
		$user->save_item_cache();

		$this->output($user->getmoney());
	}

	public function hiddenAction() {
		$user = $this->get_user();

		if ($user->changeStance(User::STANCE_HIDDEN)) {
			$this->output("Jūs pasislėpėte");
		} else {
			$this->output("Nepavyko pasislėpti");
		}
	}

	public function socialAction() {
		$user = $this->get_user();
		$users = new Users();
		$social = new Social();

		$type = $this->_getParam('type');

		if ($type == 'create') {
			$name = $this->_getParam('name');
			$tipas = $this->_getParam('tipas');

			$what = $users->getid($name);

			if (!$what) {
				$this->output('Šitas vartotojas neegzistuoja');
			} elseif ($itrauktas = $social->getStatus($user->getid(), $what)) {
				$this->output('Jau esate įtraukę šitą vartotoją į '.(($itrauktas==Social::FRIEND)? 'draugų' : 'priešų').' sąrašą');
			} elseif (!in_array($tipas, array(Social::FRIEND, Social::IGNORE))) {
				$this->output('Klaida nustatant sąrašo tipą');
			} elseif ($id = $social->add($user->getid(), $what, $tipas)) {
				$ob = new stdClass();
				$ob->key = $id;
				$ob->value = $name;

				$this->output(json_encode($ob));
			} else {
				$this->output("Klaida įrašant duomenis");
			}
		} elseif ($type == 'remove') {
			$key = $this->_getParam('key');

			$data = $social->find($key);
			if ($data->count()==0) {
				$this->output('1');
			} else {
				$row = $data->getRow(0);
				if ($row->uid == $user->getid()) {
					if ($i = $row->delete()) {
						$this->output('1');
					} else {
						$this->output('Įvyko klaida trinant įrašą');
					}
				} else {
					$this->output('Saugumo pažeidimas');
				}
			}
		}

	}

	public function __call($methodName, $args) {
		$this->output("Tokia užklausa negalima");
	}
	
	public function workshop2Action() {
		$ob1 = $this->_getParam("ob1");
		$ob2 = $this->_getParam("ob2");
		
		if (!is_numeric($ob1) || !is_numeric($ob2) || $ob1 <= 0 || $ob2 <= 0)
			throw new Exception("Blogas formatas", ERROR);
			
		if (!($user = $this->get_user()))
			throw new Exception("Neprisijungęs");
			
		$i1 = new Item($ob1);
		$i2 = new Item($ob2);
		
		if ($i1->owner != $user->getid() || $i2->owner != $user->getid())
			throw new Exception("Daiktas turi būti jūsų");
			
		echo Item::countJoinEmblemCost($i1, $i2);
	}
	
	public function workshop3Action() {
		$ob = $this->_getParam("ob");
		
		if (!is_numeric($ob) || $ob <= 0)
			throw new Exception("Blogas formatas", ERROR);
			
		if (!($user = $this->get_user()))
			throw new Exception("Neprisijunges", ERROR);
			
		$i = new Item($ob);
		
		if ($i->owner != $user->getid())
			throw new Exception("Daiktai turi būti jūsų");
			
		echo Item::countEmblemChangeCost($i);
	}
}
?>
