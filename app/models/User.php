<?php
class User {
	const STANCE_BATTLE = 0;
	const STANCE_HIDDEN = 1;
	const STANCE_TIME = 900;
	const HIDDEN_TIME = 90;

	public $items;

	public $skills;

	protected $name;

	protected $lvl;
	protected $exp;
	protected $exp_dif=0;
	protected $race;
	protected $money;
	protected $money_dif=0;
	protected $lasthit;
	public $regdate;
	protected $hp;
	protected $hp_dif=0;
	protected $time;
	protected $long;
	protected $job;
	protected $max_hunt;
	protected $max_hunt_dif=0;
	protected $last_event;
	protected $nano;
	protected $hunt_bonus;
	protected $hunt_bonus_dif=0;
	protected $sex;
	protected $vardas;
	protected $about;
	protected $drink;
	public $img;
	public $heal;
	public $demo;
	public $has;
	public $vote;
	public $mail;
	public $stance;
	public $hidden;
	public $sleft;
	public $hleft;
	protected $val;
	public $mission;
	public $untrust = '';

	protected $startas;
	protected $endas;
	protected $visas;

	protected $kill;
	protected $dead;

	/**
	* @var Zend_Db_Adapter_Pdo_Mysql
	*/
	protected $db;

	protected $id;

	protected $skill_plus;

	protected $next_atack;
	protected $next_defence;

	protected $admin;

	public $safe_money;

	private $readonly=false;
	
	public $egg;
	
	public $bonus = 0;

	public function __construct($id, $readonly=false) {
		if (!is_numeric($id))
			throw new Exception("Blogas userio id");

		$this->id=$id;
		$this->readonly=$readonly;

		$this->db=Zend_Registry::get('db');

		if ($readonly) {
			$hold=false;
			$data=$this->db->fetchRow("SELECT * FROM `users` WHERE `id` = ?", $this->id);
		} else {
			$hold=true;
		}

		while ($hold) {
			$data=$this->db->fetchRow("SELECT * FROM `users` WHERE `id` = ?", $this->id);
			if ($data['hold'] < time() ) {
				$num=$this->db->update('users', array('hold' => (time()+5)), "`id` = {$this->id} AND `hold`<'".time()."'");
				if ($num==1) {
					$hold=false;
				}
			} else {
				usleep(100000);
			}
		}


		$this->name=$data['name'];
		$this->exp=$data['exp'];
		$this->hp=$data['hp'];
		$this->job=$data['job'];
		$this->kill=$data['kill'];
		$this->dead=$data['dead'];
		$this->lasthit=$data['lasthit'];
		$this->money=$data['money'];
		$this->race=$data['race'];
		$this->regdate=$data['regdate'];
		$this->time=$data['time'];
		$this->long=$data['long'];
		$this->max_hunt=$data['max_hunt'];
		$this->last_event=$data['last_event'];
		$this->next_atack=$data['next_atack'];
		$this->next_defence=$data['next_defence'];
		$this->admin=$data['admin'];
		$this->heal=$data['heal'];
		$this->img=$data['img'];
		$this->demo=$data['demo'];
		$this->nano=$data['nano'];
		$this->hunt_bonus=$data['hunt_bonus'];
		$this->sex=$data['sex'];
		$this->about=$data['about'];
		$this->vardas=$data['vardas'];
		$this->has=$data['has'];
		$this->vote=$data['vote'];
		$this->drink=$data['drink'];
		$this->safe_money=$data['safe_money'];
		$this->val=$data['val'];
		$this->mail=$data['mail'];
		$this->stance = $data['stance'];
		$this->hidden = $data['hidden'];
		$this->sleft = $data['sleft'];
		$this->hleft = $data['hleft'];
		$this->untrust = $data['untrust'];

		$this->startas=$data['startas'];
		$this->endas=$data['endas'];
		$this->visas=$data['visas'];
		$this->bonus = $data['bonus'];

		if (!$this->img) {
			$this->img='no-foto.png';
		}

		Zend_Registry::set("MONEY_$id",$this->money);

		//kraunam daiktus
		$this->load_items();

		Zend_Loader::loadClass('Skills');
		$skills=new Skills();

		$this->skills=$skills->getskills($this->id, $this->getlvl());

		$this->restoreHP(step($this->lasthit,time(),60));
		$this->restoreWork(step($this->lasthit, time(), config()->time_ratio));

		//baigiam darbus jei reikia
		if (($this->job>0) && ($this->time<time()) && $this->isfree() && !$readonly) {
			switch ($this->job) {
				case JOB_HUNT : $this->hunt_done(); break;
				case JOB_JOB : $this->job_done(); break;
				case JOB_HEAL : $this->heal_done(); break;
			}
			$this->job=0;
		}
		
		$eggs = new Eggs();
		$this->egg = $eggs->get($this->id, $this->get_session_time());
	}
	
	public function restoreWork($steps) {
		$this->addhunt($steps);
	}

	public function changeStance($stance) {
		if ($stance == $this->stance) return false;
		if ($stance == User::STANCE_HIDDEN && $this->sleft > time()) return false;

		$this->stance = $stance;

		$this->sleft = time() + User::STANCE_TIME;

		return true;
	}

	public function isVal() {
		return ($this->val=='');
	}

	public function process_session_timer() {
		if ($this->endas<time()-60) {
			$this->visas+=$this->endas-$this->startas;
			$this->startas=time();
			$this->endas=time();

			$this->db->update(
				'users',
				array(
					'startas' => $this->startas,
					'endas' => $this->endas,
					'visas' => $this->visas
				),
				"`id` = {$this->id}"
			);
		} else {
			$this->endas=time();
			$this->db->update(
				'users',
				array(
					'endas' => $this->endas
				),
				"`id` = {$this->id}"
			);
		}
	}

	public function get_safe_money() {
		return $this->safe_money;
	}

	public function add_safe_money($num) {
		$num=max(0, $num);
		return $this->safe_money+$num;
	}

	public function transfer_to_money($num) {
		if ($num > 0) {
			$num=min($this->safe_money, $num);

			$this->safe_money -= $num;
			$this->addmoney($num);
		} else {
			$num = -1 * $num;
			$num = min($this->getmoney(), $num);
			$this->addmoney(-1 * $num);
			$num = floor($num * 0.98);
			$this->safe_money += $num;
		}

		return $num;
	}

	public function get_session_time() {
		return $this->visas+($this->endas-$this->startas);
	}

	public function getlasthit() {
		return $this->lasthit;
	}
    
    public function getLastSeen() {
        return $this->startas;
    }

	public function getSex() {
		return $this->sex;
	}

	public function getVardas() {
		return $this->vardas;
	}

	public function getAbout() {
		return $this->about;
	}

	public function isonline() {
		$online=new Online();

		return $online->isonline($this->id);
	}

	public function getdrink() {
		return $this->drink;
	}

	public function isdrink() {
		return (time()<$this->drink);
	}

	public function setdrink($time) {
		$this->drink=time()+$time*60;
	}

	public function reload_item_cache() {
		Zend_Registry::get('item_cache')->remove($this->itemCacheName());
		$this->load_items();
		$this->reindexitem();
	}

	private function itemCacheName() {
		return "i".getsid()."_{$this->id}";
	}

	public function load_items() {
		//dar reikia patikrinti del keso
		$item_cache=Zend_Registry::get('item_cache');
		if ($item_cache->test($this->itemCacheName())) {
			$this->items = $item_cache->load($this->itemCacheName());
		} else {
			Zend_Loader::loadClass('Items');
			$items=new Items();

			$this->items=$items->getitems($this->id);
			$item_cache->save($this->items, $this->itemCacheName());
		}
	}

	public function save_item_cache() { 
		$item_cache=Zend_Registry::get('item_cache');
		if ($item_cache->test($this->itemCacheName())) {
			$item_cache->remove($this->itemCacheName());
		} 

		$item_cache->save($this->items, $this->itemCacheName());
	}

	public function get_hunt_bonus() {
		return $this->hunt_bonus;
	}

	public function getnano() {
		return $this->nano;
	}

	public function ckecknano($need) {
		return ($this->nano>=$need);
	}

	public function addnano($nano) {
		$plus=($nano<0)? "-" : "+";
		$this->nano+=$nano;
		$nano=abs($nano);
		return $this->db->query("UPDATE `users` SET `nano`=`nano`$plus'$nano' WHERE id='{$this->id}'");
	}
	public function getclanid() {
		$clan_members=new ClanMembers();
		$select=$clan_members->select()->where("user = ?",$this->id);

		$data=$clan_members->fetchAll($select);

		return ($data->count()==1)? $data->getRow(0)->cid : null;
	}

	public function getfotoHTML() {
		return "<img src=\"/foto/{$this->img}\" width=\"140\" height=\"180\" border=\"0\" />";
	}

	public function isadmin() {
		return ($this->admin==1)? true : false;
	}

	public function get_max_hunt() {
		return $this->max_hunt+$this->max_hunt_dif;
	}

	public function get_job() {
		if ($this->time<time())
			return false;
		return $this->job;
	}

	public function time_until() {
		return max($this->time - time(), 0);
	}

	public function time_until_atack() {
		return max($this->next_atack-time(),0);
	}

	public function time_until_defense() {
		return max($this->next_defense-time(),0);
	}

	//cia reikia aprasyti gydyima
	private function heal_done() {
		$long=$this->long;
		$heal=$this->heal;

		$doctors=require('app/data/doctors.php');

		$doctor=$doctors[$heal];

		$hp=$doctor['hp']*$long;
		$name=$doctor['name'];

		$this->addhp($hp);
		$this->heal=0;
		$this->sendevent("Jus gydė $name $long minučių, per kurias suteikė $hp gyvybių", EVENT_OTHER);
	}

	private function hunt_done() {
		$long=$this->long;
		$lvl=$this->getlvl();
		$hunt=$this->get_skill_sum("HUNT");
		//$dmg=rand(1,floor(($this->maxhp()*(0.03+0.001*$lvl)*$long)/(1.00+0.001*$this->get_skill_plus("ARMOR"))));
		$dmg=rand(1,floor(($this->maxhp()*0.02*$long)));
		$money=rand(1,sqrt($lvl)*0.2*($long+$hunt))+10*$lvl;

		$dif=new Minmax(1+(0.3*$lvl), floor((20+0.1*(sqrt($lvl+$hunt*1.5)))*$long));

		$vid=$dif->average();

		$min=round($vid*0.7);
		$max=round($vid*1.3);

		$exp=mt_rand($min,$max);

		$evtext='';
		if (rand(0,200+$hunt)+$long*15>100) {
			$items_temple=new Itemstemplates();
			$GET_RACE=(rand(0,1)==0)? $this->getrace() : RACE_ALL;
			$min_item=min($lvl,floor(($hunt+$lvl)/9));
			$bonus=floor($hunt/50)*1;

			$item=$items_temple->getiitem(null,null,$this->getlvl(),$min_item,true,$GET_RACE,$bonus,$this->hunt_bonus);
			if ($item) {
				$item->setowner($this->id);
                
                if ($item->template == 133) {
                    $raides = array("A", "G", "O", "N", "I", "J", "A");
                    
                    $R = $raides[rand(0, 6)];
                    
                    $item->title = $R . " " . $item->title;
                    $item->img = $R;
                } elseif ($item->lvl<=$lvl*0.85) {
					$this->hunt_bonus+=10-ceil($item->lvl/$lvl*10);
				} else {
					if ($item->type!="CONSUMABLE") {
						$this->hunt_bonus = round(.5 * $this->hunt_bonus);
					}
				}
				if (($item->maxlvl>0) && ($item->maxlvl<$this->getlvl()+10)) {
					$item->maxlvl=$this->getlvl()+10;
				} else {
				}
				$item->save();
				$evtext="Taip pat gavote <b>{$item->itemLink()}</b>";
			} else {
				$this->hunt_bonus+=10;
			}
		}

		$this->addhp(-1*$dmg);
		$this->addmoney($money);
		$this->addexp($exp);

		$this->sendevent("Jūs ($lvl lygio) medžiojote $long minučių per kurias patyrėte $dmg žalos, gavote $exp patirties, bei jūsų laimikis yra $money agonų. $evtext", EVENT_DO);
	}

	private function job_done() {
		$long=floor($this->long);
		$lvl=$this->getlvl();
		$max=round(sqrt($lvl)*(30+$this->get_skill_sum("WORK"))*$long);
		$min=round($max*0.7);
		$money=rand($min,$max);

		$this->addmoney($money);

		$this->sendevent("Jūs uždirbote $money agonų", EVENT_DO);
	}

	public function getItemsByType($type, $onlyBank = true) {
		$rez = array();
		foreach ($this->items as $key => $v)
			if ($type == $v->type && (!$onlyBank || strpos($v->place, "BANK_") === 0 || $v->place == ''))
				$rez[] = $v;
				
		return $rez;
	}

	public function get_skill_plus($skill) {
		if (!is_array($this->skill_plus)) {
            $save = array();
            $itemssets = new ItemsSets();
            $setsNum = ItemsSets::getSets($this->getid());
			$this->skill_plus=array();
			if (is_array($this->items)) {
				foreach ($this->items as $v) {
					if ((count($v->plus)>0) && (strpos($v->place,"BANK")!==0) && $v->place && (strpos($v->place,"JOINAS")!==0)) {
						$k = ($v->lvl > $this->getlvl())? 0.5 : 1;
                        foreach($v->plus as $key => $plus) {
							if (isset($this->skill_plus[$key])) {
								$this->skill_plus[$key]+=round($k *$plus);
							} else {
								$this->skill_plus[$key]=round($k *$plus);
							}
						}
                        if ($v->set) {
                            if (!isset($save[$v->set]) && isset($setsNum[$v->set]))   
                                $save[$v->set] = $itemssets->get($v->set, $setsNum[$v->set]);
                        }
					}
				}
			}
            foreach ($save as $set)
                if ($set->count() > 0)
                    foreach ($set as $v) {
                        if (isset($this->skill_plus[$v->type])) {
                            $this->skill_plus[$v->type] *= 1 + ($v->value / 100);
                            $this->skill_plus[$v->type] = round($this->skill_plus[$v->type]);
                        }
                    }
		}
		return (isset($this->skill_plus[$skill]))? $this->skill_plus[$skill] : 0;
	}

	public function getlvl() {
		if (!$this->lvl)
			$this->lvl=lvl($this->exp);
		return $this->lvl;
	}

	public function getid() {
		return $this->id;
	}

	public function getname() {
		return $this->name;
	}

	public function getmoney() {
		return $this->money+$this->money_dif;
	}

	public function getrace() {
		return $this->race;
	}

	public function candefence() {
		return ($this->next_defence<=time());
	}

	public function addmoney($money) {
		if (($this->getmoney()+$money)>0) {
			$this->money_dif+=round($money);
		} else {
			$this->money_dif=0-$this->getmoney();
		}
		Zend_Registry::set("MONEY_{$this->id}",$this->getmoney());
	}

	public function setjob($job,$time) {
		if (
				$this->isfree() &&
				(
					($this->get_max_hunt()>=$time) ||
					($job==JOB_HEAL)
				) &&
				(
					($this->hp>=$this->maxhp()*0.2) ||
					($job!=JOB_HUNT)
				)
		) {
			$this->time=time()+$time*60;
			$this->job=$job;
			$this->long=$time;
			if ($job!=JOB_HEAL)
				$this->max_hunt_dif-=$time;

			$this->changeStance(User::STANCE_BATTLE);
			return true;
		} else {
			return false;
		}
	}

	public function hunt($time) {
		if ($this->gethp()>$this->maxhp()*0.2) {
			return $this->setjob(JOB_HUNT,$time);
		} else {
			return false;
		}
	}

	public function job($time) {
		return $this->setjob(JOB_JOB,$time);
	}

	public function isfree() {
		return ($this->time<=time());
	}

	public function addexp($num) {
		if ($this->getexp()+$num>1) {
			$this->exp_dif+=$num;
			return true;
		} else {
			return false;
		}
	}

	public function getexp() {
		return $this->exp+$this->exp_dif;
	}

	public function needexp() {
		return getexp($this->getlvl(),Zend_Registry::get('config')->ratio)-getexp($this->getlvl()-1,Zend_Registry::get('config')->ratio);
	}
	public function haveexp() {
		return $this->getexp()-getexp($this->getlvl()-1,Zend_Registry::get('config')->ratio);
	}

	public function learnskill($id) {
		$needmoney=$this->skills[$id]->needmoney();
		if ($needmoney>=$this->money) {
			$this->skills[$id]->learn();
			$this->addmoney(-1*$needmoney);
			return true;
		} else {
			return false;
		}
	}

	protected $skills_index;
	/**
	* @desc gauname userio skila pagal tipa
	* @param string skilo tipas
	* @return Skill
	*/
	public function getskillbytype($type) {
		if (!is_array($this->skills_index)) {
			$this->skills_index=array();
			if (is_array($this->skills)) {
				foreach ($this->skills as $v) {
					$this->skills_index[$v->type]=$v;
				}
			}
		}
		return (isset($this->skills_index[$type])) ? $this->skills_index[$type] : 0;
	}

	protected $items_index;

/**
* @desc gaunam daikta pagal jo vieta
* @param string $place vieta
* @return Item daikta
* */
	public function getitembyplace($place) {
		if (!is_array($this->items_index)) {
			$this->items_index=array();
			if (is_array($this->items)) {
				foreach ($this->items as $v) {
					if ($v->place && ($v->owner==$this->id)) {
						$this->items_index[$v->place]=$v;
					}
				}
			}
		}
		return (array_key_exists($place,$this->items_index))? $this->items_index[$place] : null;
	}

	protected $free_items_index;
	/**
	* @desc gaunam daigta kuris nera uzsetintas prie itemu
	* @return Item
	*/
	public function getunsetitem() {
		if (!is_array($this->free_items_index)) {
			$this->free_items_index=array();
			if (is_array($this->items)) {
				foreach ($this->items as $v) {
					if (!$v->place) {
						$this->free_items_index[]=$v;
					}
				}
			}
			return reset($this->free_items_index);
		}
		return next($this->free_items_index);
	}
	public function reindexitem() {
		$this->free_items_index=null;
		$this->items_index=null;
		$this->skill_plus=null;
	}
	public function gethp() {
		$hp=round($this->hp+$this->hp_dif);
		return $hp;
	}

	public function maxhp() {
		$k=($this->lvl<15)? 0 : $this->lvl-15;
		return  getexp($this->lvl,5)-
				getexp($k,5)+
				$this->get_skill_sum("STAMINA")*15+
				$this->get_skill_plus('ADD_HP')+
				20;
	}

	public function addhp($num) {
		if (($this->gethp()+$num)<=0) {
			$this->hp_dif-=$this->gethp();
			return false;
		} elseif ($this->gethp()+$num>$this->maxhp()) {
			$this->hp_dif=$this->maxhp()-$this->gethp();
		} else {
			$this->hp_dif+=$num;
		}
		return true;
	}

	public function sendpm($from,$title,$text) {
		Zend_loader::loadClass('PMs');
		$pms=new PMs();
		return $psm->sendpm($this->id,$from,$title,$text);
	}

	public function sendevent($text, $type = EVENT_OTHER) {
		Zend_loader::loadClass('Events');
		$events=new Events();
		return $events->sendevent($this->id,$text, $type);
	}

	public function addkill() {
		$this->kill++;
	}

	public function adddead() {
		$this->dead++;
	}

	public function save() {
		if ($this->readonly)
			return false;

		$data = array(
			'job' => $this->job,
			'kill' => $this->kill,
			'dead' => $this->dead,
			'lasthit' => time(),
			'long' => $this->long,
			'last_event' => $this->last_event,
			'next_atack' => $this->next_atack,
			'next_defence' => $this->next_defence,
			'heal' => $this->heal,
			'time' => $this->time,
			'has' => $this->has,
			'vote' => $this->vote,
			'drink' => $this->drink,
			'hunt_bonus' => $this->hunt_bonus,
			'safe_money' => $this->safe_money,
			'stance' => $this->stance,
			'sleft' => $this->sleft,
			'bonus' => $this->bonus
		);

		if (!isset($this->hleft) || $this->hleft < time()) {
			$k = ($this->stance == User::STANCE_HIDDEN)? 1.6 : 1;

			$this->hidden = rand(0, round($k * $this->get_skill_sum('HIDING')));

			$data['hleft'] = $this->hleft = time() + User::HIDDEN_TIME;
			$data['hidden'] = $this->hidden;
		}

		$this->db->update('users', $data, "`id` = {$this->id}");
		$this->db->query("
			UPDATE
				users
			SET
				money=money+'{$this->money_dif}',
				exp=exp+'{$this->exp_dif}',
				hp=hp+'{$this->hp_dif}',
				max_hunt=max_hunt+'{$this->max_hunt_dif}'
			WHERE
				`id` = {$this->id}
		");

		$this->hp+=$this->hp_dif;
		$this->exp+=$this->exp_dif;
		$this->money+=$this->money_dif;
		$this->max_hunt+=$this->max_hunt_dif;

		$this->hp_dif=0;
		$this->exp_dif=0;
		$this->money_dif=0;
		$this->max_hunt_dif=0;
	}

	public function needtime() {
		return ($this->time>time())? $this->time-time() : 0;
	}

	public function getregdate() {
		return $this->regdate;
	}

	public function getkill() {
		return $this->kill;
	}

	public function getdead() {
		return $this->dead;
	}

	public function HPperstep() {
		$addHP=2;
		$addHP+=$this->get_skill_sum("STAMINA")*0.1;
		$addHP+=$this->get_skill_plus('ADD_HP_RATE');
		if ($this->race==RACE_RJAL)
			$addHP+=$addHP*0.1;
		return floor($addHP);
	}

	private function restoreHP($step) {
		$addHP=$this->HPperstep();
		$this->addhp($addHP*$step);
	}
	private function unlock() {
		$this->db->update('users',array('hold' => 0),"`id` = {$this->id}");
	}

	public function __destruct() {
		if ($this->readonly)
			return false;

		$this->save();
		$this->unlock();
	}
	public function getskillsum() {
		return Zend_Registry::get('all_skill_'.$this->id);
	}
	/**
	* @desc gaunam daroma zala
	* @param 0|1|2 ginklo tipas
	* @param Def ginyba
	* @return Minmax
	*/
	public function getDMG($type=0,$def=null) {
		$min=0;
		$max=0;
		if ($type==0) {
			$gun1=$this->getitembyplace("GUN_MAIN");
			$gun2=$this->getitembyplace("GUN_SECOND");
		} elseif ($type==1) {
			$gun1=$this->getitembyplace("GUN_MAIN");
			$gun2=null;
		} elseif ($type==2) {
			$gun1=null;
			$gun2=$this->getitembyplace("GUN_SECOND");
		}

		$xx=array($gun1,$gun2);

		foreach ($xx as $gun) {

			if ($gun) {
				$min+=$gun->getDMG()->min;
				$max+=$gun->getDMG()->max;

				$DEF=0;
				if ($gun->guntype==1) {
					$TYPE="MEELE_GUN";
					if ($def!=null)
						$DEF=$def->melle;
				} elseif ($gun->guntype==2) {
					$TYPE="LIGHT_GUN";
					if ($def!=null)
						$DEF=$def->ranged;
				} elseif ($gun->guntype==3) {
					$TYPE="HEAVY_GUN";
					if ($def!=null)
						$DEF=$def->ranged;
				}
				if (($this->getrace()==RACE_MAN) && ($def)) {
					$DEF+=2;
					$DEF=1.3*$DEF;
				}

				if ($gun->ammo==1) {
					$TYPE2="BULLET";
				} elseif ($gun->ammo==2) {
					$TYPE2="LASER";
				} elseif ($gun->ammo==3) {
					$TYPE2="ROCKET";
				} elseif ($gun->ammo==4) {
					$TYPE2="GRANATE";
				}

				$add=$this->get_skill_sum("ADD_DMG");
				$add+=(
						$this->getskillbytype($TYPE)->lvl+
						$this->get_skill_plus($TYPE)
					 )*0.3;

				$add-=$DEF*2.5*0.3;

				if (isset($TYPE2)) {
					$add+=($this->getskillbytype($TYPE2)->lvl+
						   $this->get_skill_plus($TYPE2)
						   )*0.4;
				}
				if ($gun->guntype==1) {
					$add+=$this->get_skill_sum("STRENGHT")*0.25;
				} else {
					$add+=$this->get_skill_sum("RANGED")*0.25;
				}
				if (($gun->guntype==1) && ($this->getrace()==RACE_RJAL))
					$add=$add*1.4+2;

				$add=round($add);

				$min+=$add;
				$max+=$add;

				if ($gun->gunplace==GUNPLACE_TWO) {
					$min=round($min*1.2);
					$max=round($max*1.2);
				}
			}
		}

		if ($type==2) {
			$min=round($min*0.7);
			$max=round($max*0.7);
		}

		$min=max($min,1);
		$max=max($max,1);

		if ($this->isdrink()) {
			$min=round($min*1.1);
			$max=round($max*1.1);
		}

		return new Minmax($min,$max);



/*		$reload1=$gun1->plus['SPEED_RELOAD'];
		$reload2=$gun2->plus['SPEED_RELOAD'];

		$duration1=$gun1->plus['SPEED_DURATON'];
		$duration2=$gun2->plus['SPEED_DURATON'];*/

	}
	public function gunDuration($type, $def=null) {
		if (!in_array($type,array(1,2))) {
			return false;
		}
		if ($type==1) {
			$TYPE="GUN_MAIN";
		} else {
			$TYPE="GUN_SECOND";
		}

		$gun=$this->getitembyplace($TYPE);
		if ($gun) {
			switch ($gun->guntype) {
				case GUNTYPE_MELLE : $GUN_TYPE="MEELE_SPEED"; break;
				case GUNTYPE_LIGHT : $GUN_TYPE="RANGET_SPEED"; break;
				case GUNTYPE_HEAVY : $GUN_TYPE="RANGET_SPEED"; break;
			}
			if (isset($gun->plus['SPEED_DURATON'])) {
				$speed_duration=$gun->plus['SPEED_DURATON'];
			} else {
				$speed_duration=10;
			}
			$pliusas=($this->getlvl()>100)? min($this->getlvl()-100,400)*0.00003 : 0;
			$k=(($this->getrace()==RACE_RJAL) && ($gun->guntype==GUNTYPE_MELLE)) ? 0.068+$pliusas/3 : 0.057+$pliusas;
			$k+=0.0008*$speed_duration;
			$speed_duration=$speed_duration/(1+$k*$this->get_skill_sum($GUN_TYPE));
			$speed_duration=max(0.01,$speed_duration);
			return $speed_duration;
		} else {
			return 0;
		}
	}
	/**
	* @desc gauname ginklo daromos žalos per sekunde
	* @param 1|2 ginklo ranka
	* @param Def ginyba
	* @return int zala
	*/
	public function getDMGpersecond($type,$def=null) {
		if (!in_array($type,array(1,2))) {
			return false;
		}
		if ($type==1) {
			$TYPE="GUN_MAIN";
		} else {
			$TYPE="GUN_SECOND";
		}

		$gun=$this->getitembyplace($TYPE);
		if ($gun) {
			$dmg=$this->getDMG($type,$def)->average();
			return $dmg/$this->gunDuration($type, $def);
		} else {
			return 0;
		}
	}
	public function getDMGperminute($type,$def=null) {
		$time=60;
		if (!in_array($type,array(1,2))) {
			return false;
		}
		if ($type==1) {
			$TYPE="GUN_MAIN";
		} else {
			$TYPE="GUN_SECOND";
		}

		$gun=$this->getitembyplace($TYPE);
		if ($gun) {
			$dmg=$this->getDMG($type,$def)->average();
			$speed_duration=$gun->plus['SPEED_DURATON'];
			$speed_duration-=0.01*$this->get_skill_plus("RANGET_SPEED");
			$speed_duration-=0.01*$this->getskillbytype("RANGET_SPEED")->lvl;
			$speed_duration=max(0.01,$speed_duration);

			//nebaigta

		} else {
			return false;
		}
	}
	public function get_bank_place() {
		return 54+$this->get_skill_plus("BANK");
	}
	public function get_last_event() {
		return $this->last_event;
	}
	/**
	* @desc gaunam ar perksaityttas paskutinis ivykis
	* @return boolean
	*/
	public function read_last_event() {
		$event=new Events();
		$last=$event->getevents($this->getid(),1,0);
		if ($last->count()==1) {
			return ($last->getRow(0)->date>date(DATE_FORMAT,$this->last_event))? true : false;
		}
		return false;
	}
	public function renew_last_event() {
		$this->last_event=time();
	}
	public function get_skill_sum($skill) {
		$add=$this->get_skill_plus($skill);
		$skill=$this->getskillbytype($skill);
		if ($skill)
			$add+=$skill->lvl;

		return $add;
	}
	/**
	* @desc kariaujanm
	* @param User vartojo objektas
	* @return int event id
	*/
	public function atack($user,$full=false) {
		$this->changeStance(User::STANCE_BATTLE);
		//paskaiciuojam kiek laiko turi vykti kova
		$time=($full)? 60000 : 60+min($this->getlvl(),$user->getlvl())*2;

		//gaunam ginyba
		$DEF_A_RANGED=$this->get_skill_sum("DEXTERITY");
		$DEF_A_MELLE=$this->get_skill_sum("MEELE_DEFENCE");

		$DEF_D_RANGED=$user->get_skill_sum("DEXTERITY");
		$DEF_D_MELLE=$user->get_skill_sum("MEELE_DEFENCE");

		if ($this->isdrink()) {
			$DEF_A_RANGED=round(0.9*$DEF_A_RANGED);
			$DEF_A_MELLE=round(0.9*$DEF_A_MELLE);
		}

		if ($user->isdrink()) {
			$DEF_D_RANGED=round(0.9*$DEF_D_RANGED);
			$DEF_D_MELLE=round(0.9*$DEF_D_MELLE);
		}

		$DEF_A=new Def($DEF_A_RANGED, $DEF_A_MELLE);
		$DEF_D=new Def($DEF_D_RANGED, $DEF_D_MELLE);

		//gauname zala per sekunde

		$k=$this->getlvl()-$user->getlvl();

		$k=round($k/2);

		if ($k>75) {
			$k=75;
		} elseif ($k<-75) {
			$k=-75;
		}

		$DMG_A=$this->getDMGpersecond(1,$DEF_D)+
				$this->getDMGpersecond(2,$DEF_D);
		$DMG_D=$user->getDMGpersecond(1,$DEF_A)+
				$user->getDMGpersecond(2,$DEF_A);

		$DMG_A=$DMG_A*(1+0.01*$k);
		$DMG_D=$DMG_D*(1-0.01*$k);

		//tikrinam greicio bonusa

		//skaiciuojam zingsniais
		$event=new stdClass();
		$event->time=time();
		$event->date=mydate();
		//$event->before_atacker=clone $this;
		//$event->befire_defender=clone $user;
		$event->step=array();

		$event->DMG_A=$DMG_A;
		$event->DMG_D=$DMG_D;

		$HIT_A=0;
		$HIT_D=0;

		for ($x=$time; ($x>=0 && (!isset($lose_D)) && (!isset($lose_A))) ;$x-=5) {
			$left=min($x,5);

			$MAKE_DMG_A=round($DMG_A*$left*(rand(80,100)/100)/(1+0.045*$user->get_skill_plus("ARMOR")),2);
			$MAKE_DMG_D=round($DMG_D*$left*(rand(80,100)/100)/(1+0.045*$this->get_skill_plus("ARMOR")),2);

			$HIT_A+=$MAKE_DMG_A;
			$HIT_D+=$MAKE_DMG_D;

			$ob=new stdClass();

			//skaicuojam defenderio likuti
			$ob->def=new stdClass();

			$ob->def->hit=$MAKE_DMG_A;
			$ob->def->wasHP=$user->gethp();
			if (!$user->addhp(-1*$MAKE_DMG_A)) {
				$lose_D=true;
				$ob->def->lose="TAIP";
			}
			$ob->def->isHP=$user->gethp();

			//skaiciuojam atakerio likuti
			$ob->atk=new stdClass();

			$ob->atk->hit=$MAKE_DMG_D;
			$ob->atk->wasHP=$this->gethp();
			if (!$this->addhp(-1*$MAKE_DMG_D)) {
				$lose_A=true;
				$ob->atk->lose="TAIP";
			}
			$ob->atk->isHP=$this->gethp();
			$event->step[]=$ob;
			unset($ob);
		}

		//$event->after_atacker=clone $this;
		//$event->after_defender=clone $user;

		$HIT_A=round($HIT_A);
		$HIT_D=round($HIT_D);

		$event->HIT_A=$HIT_A;
		$event->HIT_D=$HIT_D;

		if ((!isset($lose_D)) && (!isset($lose_A))) {
			if ($HIT_A>$HIT_D) {
				$lose_D=true;
			} elseif ($HIT_A<$HIT_D) {
				$lose_A=true;
			} else {
				$lygiosios=true;
			}
		}

		if (isset($lose_A)) {
			if ($user->get_type()!=USERTYPE_MOB) {
				$this->adddead();
				$user->addkill();
				$money=rand(0,floor($this->getmoney()*0.03));
				$this->addmoney(-1*$money);
				$user->addmoney($money);
			} else {
				$money=0;
			}

			$event->winner="D";

			$exp=3+floor(sqrt($user->getlvl()))-($user->getlvl()-$this->getlvl());
			$exp=max(1,$exp);
			$exp=min(floor(10+2*sqrt($user->getlvl())),$exp);

			$user->addexp($exp);

			$EVENT_A="Deja jūsų puolimas buvo nesėkmingas prieš {$user->getname()} (lygis: {$user->getlvl()}). Padarėte {$HIT_A} žalos, tačiau patyrėte {$HIT_D} žalos, bei praradote $money agonų. Priešas gavo $exp patirties";
			$EVENT_D="Jus užpuolė {$this->getname()} (lygis: {$this->getlvl()}) ir jūs laimėjote. Praradote {$HIT_A} gyvybių, bet priešas patyrė {$HIT_D} žalos. Beto laimėjote $money agonų ir gavote $exp patirties";
		} elseif (isset($lose_D)) {
			if ($user->get_type()!=USERTYPE_MOB) {
				$user->adddead();
				$this->addkill();
				$money=rand(0,floor($user->getmoney()*0.03)); 
			} else {
				$money = $user->getmoney()*0.45;
				$money=rand($money*0.5,floor($money));
			} 
			$user->addmoney(-1*$money);
			$this->addmoney($money);
			$event->winner="A";

			if ($user->getid()==748) {
				$money=min(rand(1,200),$money);
			}

			$exp=3+floor(sqrt($this->getlvl()))-($this->getlvl()-$user->getlvl());
			if ($exp<0) {
				$exp=ceil($exp/5);
				if ($exp>-5) {
					$exp=0;
				} else {
					$exp+=5;
				}
				$user->addexp(-1*$exp);
			}
			$exp=min(floor(10+2*sqrt($this->getlvl())),$exp);

			if (($exp<1) && ($user->get_type()==USERTYPE_MOB))
				$exp=1;

			$this->addexp($exp);

			$EVENT_A="Jūsų ataka buvo sėkminga prieš {$user->getname()} (lygis: {$user->getlvl()}). Padarėte {$HIT_A} žalos, tačiau patyrėte {$HIT_D} žalos, tačiau gavote $money agonų ir $exp patirties";
			$EVENT_D="Jus užpuolė {$this->getname()} (lygis: {$this->getlvl()}) ir jūs pralaimėjote. Praradote {$HIT_A} gyvybių, bei $money agonų, tačiau padarėte {$HIT_D} žalos. Užpuolėjas gavo $exp patirties";
		} else {
			$event->winner="L";
			$EVENT_A="Užpuolėte {$user->getname()} (lygis: {$user->getlvl()}) ir buvo lygiosios. Praradote po {$HIT_D} gyvybių, tačiau gavote po 1 patirties";
			$EVENT_D="Jūs buvote užpultas {$this->getname()} (lygis: {$this->getlvl()}) ir buvo lygiosios. Praradote {$HIT_A} gyvybių, bet priešas patyrė {$HIT_D} žalos. Priešas gavo 1 patirties";
			$this->addexp(1);
			$user->addexp(1);
			$money=0;
			$exp=0;
		}

		$event->money=$money;
		$event->exp=$exp;

		$event->event_d=$EVENT_D;
		$event->event_a=$EVENT_A;

		$this->sendevent($EVENT_A, EVENT_ATACK);
		$user->sendevent($EVENT_D, EVENT_DEFENCE);

		$wait=($user->get_type()==USERTYPE_MOB)? 10 : 15;

		$this->next_atack=time()+$wait*60;
		$user->next_defence=time()+3600;

		$this->save();
		$user->save();

		//Zend_Registry::get('logger')->info("Kariavimas\n\n".print_r($event,true));

		return $event;
	}
	public function enemy() {
		return ($this->race==RACE_MAN)? RACE_RJAL : RACE_MAN;
	}
	public function addhunt($v) {
		$this->max_hunt_dif+=$v;
		if ($this->get_max_hunt()<0) {
			$this->max_hunt_dif+=0-$this->get_max_hunt();
		}
		if ($this->get_max_hunt() > config()->max_time) {
			$this->max_hunt_dif -= $this->get_max_hunt() - config()->max_time;
		}
	}
	public function get_type() {
		return USERTYPE_USER;
	}

	public function __toString() {
		return $this->name;
	}

	public function getCurrentFight() {
		$atack=new Atack();

		return $atack->getCurrentFight($this->id);
	}

	public function getOrientacija() {
		if (!($r = cacheGet("{$this->id}_ORIENTACIJA"))) {
			$o = $this->get_skill_sum("ORIENTACIJA");

			$min = round($o * 0.8);
			$max = round($o * 0.9);

			$r = rand($min, $max);

			cacheAdd("{$this->id}_ORIENTACIJA", $r, 20);
		}
		return $r;
	}
	public function getMission() {
        if (!$this->mission) {
            $missions = new Missions();
            $this->mission = $missions->getByUid($this->getid());
        }
		return $this->mission;
	}
    
    public function isAgonija() {
        return ($this->gethp() / $this->maxhp() < 0.2);
    }
    
    function uzimtumas($action) {
        if ($action == 'atack' && $this->time_until_atack()) {
            $job = JOB_ILSISI;
            $left = $this->time_until_atack();
        } elseif (in_array($action, array('atack', 'mission')) && ($this->isAgonija())) {
            $job = JOB_AGONIJA;
            $left = -1;
        } elseif (($job = $this->get_job()) && ($left = $this->time_until())) {
            if ($job == JOB_HEAL && $action == 'hospital') 
                $job = 200 + $this->heal;
        } elseif ($mission = $this->getMission()) {
            $job = JOB_MISSION;
            $left = $mission->left();    
        } else {
            $job = null;
            $left = 0;
        }
        //die("gdsgdsg_$job $left");
        return array($job, $left);
    }
}
?>
