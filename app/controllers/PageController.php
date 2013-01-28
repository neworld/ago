<?php
class PageController extends Zend_Controller_Action {
	protected $run=array();
	protected $unrun;
	protected $screen;
	protected $user;
	protected $time;
	protected $post;
	protected $info;
	protected $loged;
	protected $TRACK_NUM;

	public function init() {
		//isjungiam automatini renderinima:
		$this->_helper->viewRenderer->setNoRender(true);
		$this->view->addHelperPath(MY_HELPER_PATH,'Main');
	}

	private function _getPost($param,$default=null) {
		$a=eval("return isset(\$this->post->$param);");
		if ($a) {
			return eval("return \$this->post->$param;");
		} else {
			return $default;
		}
	}

	protected function addrun($command) {
		$this->run[]=$command;
	}

	protected function sendinfo($text) {
		$this->addrun("change_info('$text')");
	}

	protected function alert($text) {
		$this->addrun("Dialogs.alert('$text');");
	}

	public function preDispatch () {
		Zend_Loader::loadClass('Session');
		$session=new Session();
		if ($session->get()) {
			Zend_Loader::loadClass('Users');
			$users=new Users();

			if (($users->check_ses_key($session->get(),$this->_getParam('key'))) || ($_SESSION['DEMO']==1)) {
				if (!isset($this->user) && $this->loged != true) {
					$this->user = $users->getuser($session->get(), false, true);
					$this->view->user = $this->user;
					//echo "testas";
				}

				if (rand(0, 80)==29) {
					$missions = new Missions();
					$new = $missions->random();
					$new->save();
				}

				$this->user->process_session_timer();

				$this->screen=new stdClass();
				$this->screen->width=$this->_getParam('width');
				$this->screen->height=$this->_getParam('height');

				$this->time=$this->_getParam('time');
				$this->post=json_decode($this->_getParam('post'));
				$this->TRACK_NUM=$this->_getParam('TRACK_NUM');
				$this->loged=true;

				//kitos uzduotys

				$this->pmarket();
                
                $ignored = Array(
                    'mission',
                    'atack',
                    'work',
                    'hospital'
                );
                
                $action = $this->getRequest()->getActionName();
                if (in_array($action, $ignored)) {
                    list($job, $left) = $this->user->uzimtumas($action);
                    if ($job && $left) {
                        $this->getRequest()->setParam('actionas', $action);
                        $this->getRequest()->setActionName('uzimtumas')->setDispatched(false);                
                    }
                }

				if ($this->getRequest()->getActionName() !== 'atackgui') {
					$atack=new Atack();
					if ($atack->checkFighter($this->user->getid(), time(), true))
						$this->getRequest()->setActionName('atackgui')->setDispatched(false);
				}

			} else {
				$this->loged=false;
				if ( $this->getRequest()->getActionName() !== 'nologed' )
					$this->getRequest()->setActionName('nologed')->setDispatched(false);
			}
		} else {
			$this->loged=false;
			if ( $this->getRequest()->getActionName() !== 'nologed' )
				$this->getRequest()->setActionName('nologed')->setDispatched(false);
		}
	}

	public function pmarket() {
		$pmarket_buy=$this->_getPost('pmarket_buy');
		if ($pmarket_buy) {
			$itemssend=new Itemssend();
			$data=$itemssend->find($pmarket_buy);
			if ($data->count()==0) {
				$this->alert("Neegzistuojantis pasiųlymas");
			} else {
				$row=$data->getRow(0);
				if ($this->user->getmoney()<$row->cost) {
					$this->alert("Neturite pakankamai agonų");
				} elseif ($this->user->getid()!=$row->to) {
					$this->alert("Tai ne jums skirtas pasiųlymas");
				} elseif ($this->user->demo) {
					$this->alert('Jūs esate demo vartotojas');
				} else {
					$user=new User($row->from);
					$this->user->addmoney(-1*$row->cost);
					$user->addmoney($row->cost);
					$itemssend->buyItem($pmarket_buy);
					$item=new Item($row->item);
					$this->user->sendevent("Nusipirkote {$item} iš {$user->getname()} už {$row->cost}", EVENT_OTHER);
					$user->sendevent("Iš jūsų nupirko {$this->user->getname()} {$item} už {$row->cost}", EVENT_OTHER);
					$this->alert("Nusipirkote {$item} iš {$user->getname()} už {$row->cost}");
					unset($user);
					$this->user->load_items();
					$this->user->reindexitem();
				}
			}
		}

		$pmarket_cancel=$this->_getPost('pmarket_cancel');
		if ($pmarket_cancel) {
			$itemssend=new Itemssend();
			$data=$itemssend->find($pmarket_cancel);
			if ($data->count()==0) {
				$this->alert("Neegzistuojantis pasiųlymas");
			} else {
				$row=$data->getRow(0);
				if ($this->user->getid()!=$row->to) {
					$this->alert("Tai ne jums skirtas pasiųlymas");
				} else {
					$itemssend->remove($pmarket_cancel,$this->_getPost('reason'));
					$this->alert("Pasiųlymas atšauktas");
				}
			}
		}

		$pmarket_remove=$this->_getPost('pmarket_remove');
		if ($pmarket_remove) {
			$itemssend=new Itemssend();
			$data=$itemssend->find($pmarket_remove);
			if ($data->count()==0) {
				$this->alert("Neegzistuojantis pasiųlymas");
			} else {
				$row=$data->getRow(0);
				if ($this->user->getid()!=$row->from) {
					$this->alert("Tai ne jusų pasiųlymas");
				} else {
					$itemssend->remove($pmarket_remove);
					$user=new User($row->to);
					$item=new Item($row->item);
					$user->sendevent("{$this->user} atšaukė {$item->itemLink()} pasiulymą", EVENT_OTHER);
					$this->alert("Pasiųlymas atšauktas");
				}
			}
		}
	}

	public function missionguiAction() {
		$this->view->mission = $mission = $this->user->getMission();

		$control = $this->_getPost('control', null);
		if ($control)
			$mission->control($control, $this);
            
        $this->getResponse()->appendBody($mission->info());
    }

    public function uzimtumasAction() {
        list($job, $left) = $this->user->uzimtumas($this->_getParam('actionas'));
        
        $this->view->job = $job;
        $this->view->left = $left;
        
        if ($job == JOB_MISSION) {
            $mission = $this->user->getMission();
            if ($this->_getParam('actionas') == 'mission') {
                $control = $this->_getPost('control', $this->_getParam('control'));
                if ($control)
                    $mission->control($control, $this);
            }
            
            if ($info = $mission->info() && $this->_getParam('actionas') == 'mission') {
                $this->view->forceRender = $info;
            } else {
                $this->view->text = "Vykdote misiją: <b>{$mission->title}</b>";
            }
        }
        
        $this->render();        
    }

	public function atackguiAction() {
		$atacks=new Atack();

		$this->view->atacks=$atacks;
		$this->view->aid=$this->user->getCurrentFight();

		$this->view->atack=$atacks->find($this->view->aid)->getRow(0);
		$this->view->screen=$this->screen;

		$this->render();
	}

	public function indexAction() {
		$history = new LoginHistory();
		$this->view->history = $history->load($this->user->getid(), 7);
		$this->render();
	}
	public function skillAction() {
		$skills_db=include('app/data/skills_DB.php');
		$category_db=include('app/data/skill_category_DB.php');
		$data=array();
		foreach ($category_db as $key => $v) {
			$data[$key]['NAME']=$v['NAME'];
			$data[$key]['SKILL']=array();
			foreach ($v['SKILL'] as $type) {
				$skill=$this->user->getskillbytype($type);
				$ob=new stdClass();
				$ob->lvl=$skill->lvl;
				$ob->cost=$skill->getcost();
				$ob->name=$skill->name;
				$ob->apie=$skill->apie;
				$ob->max=$skill->max;
				$ob->plus+=$this->user->get_skill_plus($type);
				$data[$key]['SKILL'][$type]=$ob;
				unset($ob);
			}
		}
		$this->view->data=$data;
		$this->view->screen=$this->screen;
		$this->render();
	}
	public function itemAction() {
		$this->user->reload_item_cache();
		$items_DB=include('app/data/item_DB.php');

		$items=array();

		$deleted=0;

		$action=$this->_getParam('actionas');
		if ($action) {
			$item=$this->user->getitembyplace($this->post->type);
		} else {
			$item=false;
		}
		if ($item && $action) {
			if (($action=='place')) {
				//sukeiciam vietomis jeigu reikia
				if ((strpos($this->_getPost('type'),"BANK")===false) && (strpos($this->_getPost('type'),"JOINAS")===false) && $this->user->getitembyplace($this->post->place)) {
					$item=$this->user->getitembyplace($this->post->place);
					$this->post->place=$this->post->type;
				}
				//tikrinam del tasiu
				$inv=true;
				list($aplace,$anum)=explode_place($this->post->place);
				if (($item->type=="INVENTOR") && (isset($item->plus['BANK'])) && !(strpos($this->_getPost('type'),"INVENTOR")===0 && strpos($this->_getPost('place'),"BANK")===0)) {
					$place_before=$this->user->get_bank_place();
					$dif=0;

					if ($this->user->getitembyplace($this->post->place) || ($aplace=="BANK")) {
						if ($aplace=="BANK") {
							$dif=-1*$item->plus['BANK'];
						} else {
							@$dif=$item->plus['BANK']-$this->user->getitembyplace($this->post->place)->plus['BANK'];
						}
					}
					$place_after=$place_before+$dif;
					if ($dif<0) {
						for ($x=$place_after+1;$x<=$place_before;$x++) {
							$places="BANK_$x";
							if ($this->user->getitembyplace($places)) {
								$inv=false;
								$this->alert("Jums trukdo <b>{$this->user->getitembyplace($places)}</b> esantis $x inventoriaus pozicijoje. Perkelkite jį į žemesnio skaičiaus pozicija, jeigu norite sumažinti inventoriaus talpą");
							}
						}
					}
				}

				//ginklu tikrinimas
				$stop=false;
				//tikrinam dvirankius ginklus
				if ($item->guntype!=GUNTYPE_NO) {
					//tikrinam dvirankius ginklus
					if (
						($item->gunplace==GUNPLACE_TWO) &&
						(
							($this->post->place=="GUN_SECOND") ||
							$this->user->getitembyplace("GUN_SECOND")
						)
					   ) {
						   $stop=true;
					   }

					//tikrinam maina

					if (
						($item->gunplace==GUNPLACE_MAIN) &&
						($this->post->place=="GUN_SECOND")
					   ) {
						   $stop=true;
					   }

					//tikrinam sekonda

					if (
						($item->gunplace==GUNPLACE_ONE) &&
						($this->post->place=="GUN_SECOND") &&
						(@$this->user->getitembyplace("GUN_MAIN")->gunplace==GUNPLACE_TWO)
					   ) {
						   $stop=true;
					   }
				}

				if (
					(
						($item->lvl<=$this->user->getlvl()) &&
						(
							($item->race==RACE_ALL) ||
							($item->race==$this->user->getrace())
						) &&
						($aplace!="BANK") &&
						($inv) &&
						(!$stop)
					) ||
					($aplace=="BANK")
				) {
					$item->setplace($this->post->place);
					if ($this->user->getitembyplace($this->post->place)) {
						$this->user->getitembyplace($this->post->place)->replace();
					}
					$this->user->reindexitem();
				}
			} elseif ($action=='remove') {
				$cost=ceil($item->cost*(0.5+0.002*$this->user->get_skill_sum("TRADE")));
				$this->user->addmoney($cost);
				$item->destruct();
				$this->user->reindexitem();
			} elseif ($action=='market') {
				$market=new Market();
				$cost=$this->post->cost;

				if ($this->user->demo) {
					$this->alert("Demo vartotjui draudžiama pardavinėti daiktus");
				} elseif (max_market_slot($this->user->get_skill_sum("TRADE"))<=$market->count_by_user($this->user->getid())) {
					$this->alert("Nebeturite laisvų vietų turguje. Turite išsiimti senus daiktus arba padidinti prekybos įgūdžius");
				} elseif (max(1,$cost/20)>$this->user->getmoney()) {
					$this->alert("Jūs turite susimokėti komisinį mokestį, kuris lygus 5% jūsų nurodomos kainos");
				} else {
					if ($market->additem($item->id,$this->user->getid(),$cost)) {
						$this->alert("Daiktas įdėtas į turgų");
						$this->user->reindexitem();
						$this->user->addmoney(round(-1*0.05*$cost));
						$item->setowner(0);
						$deleted=$item->id;
					} else {
						$this->alert("Daikto į turgų įdėti nepavyko. Galį būti jog nustatė per didelę ar pernelyg žemą kainą");
					}
				}
			} elseif (($action=='pmarket') && !$this->user->demo) {
				$users=new Users();
				$itemssend=new Itemssend();
				$to=$users->getid($this->_getPost('to'));
				$cost=$this->_getPost('cost');
				$komisinis=ceil($cost*0.1);
				if ($to==$this->user->getid()) {
					$this->alert("Negalima parduoti sau");
				} elseif ($this->user->getmoney()<$komisinis) {
					$this->alert("Neturite $komisinis agonų komisinio mokesčio");
	//			} elseif (($cost<$item->cost/20) || ($cost>$item->cost*20)) {

				} elseif (!$to) {
					$this->alert("Toks žaidėjas neegzistuoja");
				} elseif ($cost<0) {
					$this->alert("Negali suma būti neigiama");
				} elseif (!$item) {
					$this->alert("Neegzistuojantis daiktas");
				} elseif ($itemssend->count($this->user->getid())>=5) {
					$this->alert("Negalite vienu metu turėti daugiau kaip 5 aktyvias užklausas");
				} else {
					if ($pm_id=$itemssend->addItems($item,$cost,$to)) {
						$this->user->addmoney(0-$komisinis);
						$this->user->reload_item_cache();
						$this->user->load_items();
						$this->user->reindexitem();
						$this->alert("Užklausą sėkmingai nusiųstą. Jeigu per 24 val. nenupirks daikto, jis grįš jums.");
						$user=new User($to);
						$user->sendevent("{$this->user->getname()} jums siųlo {$item} už $cost. <a onclick=\"show_pmarket($pm_id);\">Pirkimas</a>.", EVENT_OTHER);
						unset($user);
					} else {
						$this->alert("Klaida vykdant užklausą");
					}
				}
			} elseif ($action=='use') {
				if (($item->maxlvl>0) && ($item->maxlvl<$this->user->getlvl())) {
					$this->alert("Jūsų per aukštas lygis");
				} elseif ($item->lvl>$this->user->getlvl()) {
					$this->alert("Jūsų lygis per žemas");
				} elseif ($item->type!='CONSUMABLE') {
					$this->alert("Tai ne papildas");
				} else {
					$affect=array();
					foreach ($item->plus as $key => $v) {
						if ($key=="RECOVERY_HP") {
							$this->user->addhp($v);
							$affect[]="Atstatyta $v gyvybių";
						} elseif ($key=="RECOVERY_EXP") {
							$this->user->addexp($v);
							$affect[]="Pridėta $v patirties";
						} elseif ($key=="RECOVERY_HUNT") {
							$this->user->addhunt($v);
							$affect[]="Pridėta $v medžioklės";
						} elseif ($key=="DRINK") {
							$this->user->setdrink($v);
							$_SESSION['drink']=$this->user->getdrink();
							$this->addrun("setdrink({$_SESSION['drink']});");
							$affect[]="Išgėrėte alkoholinio gėrimo";
						}
					}
					$item->destruct();
					$this->alert(implode(', ',$affect));
				}
			}
		}

		//joinininmas
		if ($this->_getPost('ITEM_JOIN')==1) {
			$item1=$this->user->getitembyplace('JOINAS_1');
			$item2=$this->user->getitembyplace('JOINAS_2');
			$item3=$this->user->getitembyplace('JOIN');

			$itemstemplates=new Itemstemplates();

			$no_need=array('JOIN', 'EMBLEM', 'CONSUMABLE');

			if ($item1 && $item2) {
				if (in_array($item1->type, $no_need) && in_array($item2->type, $no_need)) {
					$item3=$itemstemplates->getiitem(null,115,$item1->lvl+1,$item1->lvl+1);
				}
			}

			if (!($item1 && $item2 && $item3)) {
				$this->alert('Reikia dviejų daiktų ir jungimo kristalo');
			} elseif ($item3->type!='JOIN') {
				$this->alert('Tai ne jungimo kristalas');
			} elseif ($item1->template!=$item2->template) {
				$this->alert('Daiktai turi būti vienodo pavadinimo');
			} elseif (min($item1->lvl,$item2->lvl)>$item3->lvl) {
				$this->alert('Jungimo kristalas turi būti didesnio lygio negu prastesnio daikto lygis');
			} else {
				$rezis=count_join_lvl($item1->lvl, $item2->lvl, $this->user->getlvl());

				$newitem=$itemstemplates->getiitem(null,$item1->template,$rezis->max,$rezis->min);
				$newitem->owner=$this->user->getid();
				if ($newitem->maxlvl>0 && $newitem->maxlvl<$this->user->getlvl()*0.9)
					$newitem->maxlvl=round($this->user->getlvl()*1.2)+10;

				$newitem->save();

				$msg="Jūs sėkmingai sujungėte {$item1} ({$item1->lvl}) + {$item2} ({$item2->lvl}) + {$item3} ({$item3->lvl}) ir gavote {$newitem->itemLink()}";

				$this->alert($msg);
				$this->user->sendevent($msg, EVENT_OTHER);

				$item1->destruct();
				$item2->destruct();
				$item3->destruct();

				$this->user->load_items();
				$this->user->reindexitem();
			}
		}

		$place=$this->user->get_bank_place();

		$sort=$this->_getPost('sort');

		if ($this->_getPost('remove_leak')==1 || in_array($sort, array(1, 2, 3))) {
			//nustatom maksimalu daiktu skaiciu
			$max=0;
			for ($x=1;$x<=$place;$x++) {
				if ($this->user->getitembyplace("BANK_$x"))
					$max=$x;
			}
			if ($max>0) {
				//ieskom skyliu
				for ($x=1;$x<$max;$x++) {
					if (!$this->user->getitembyplace("BANK_$x")) {
						//daiktas nerastas ieskom dif;
						$dif=0;
						for ($i=$x+1;$i<=$max && $dif==0;$i++)
							if ($this->user->getitembyplace("BANK_$i"))
								$dif=$i-$x;

						//stumodm daiktus per dif
						for ($i=$x+$dif;$i<=$max;$i++) {
							if ($this->user->getitembyplace("BANK_$i")) {
								$this->user->getitembyplace("BANK_$i")->setplace("BANK_".($i-$dif));
								$this->user->reindexitem();
							}
						}
					}
				}
			} else {
				$this->alert("Pas jus tusčias inventorius");
			}
		}
		//------rikiavimas------------------------

		//pradedam rikiavimo dali

		if (in_array($sort, array(1, 2, 3))) {
			//nusistatom rezi
			$max=0;
			for ($x=1;$x<=$place;$x++) {
				if ($this->user->getitembyplace("BANK_$x"))
					$max=$x;
			}
			if ($max>=2) {
				//pasidarom rikiavimo masiva
				$item_array=array();
				for ($x=1;$x<=$max;$x++) {
					$item_array[$x]=$this->user->getitembyplace("BANK_$x");
					$item_array[$x]->$place='';
					$item_array[$x]->save();
				}

				switch ($sort) {
					case 1 : $funkcija="sort_by_lvl"; break;
					case 2 : $funkcija="sort_by_type"; break;
					case 3 : $funkcija="sort_by_mix"; break;
				}

				Quick_sort($item_array, 1, $max, $funkcija);

				foreach ($item_array as $key => $v) {
					if ($v!=null) {
						$v->place="BANK_$key";
						$v->save();
					}
				}

				$this->user->reload_item_cache();
			}
		}


		$this->user->save_item_cache();
        //$this->user->reload_item_cache();

		$items_DB2=$items_DB;
		$items_DB2['JOIN']['num']=1;
		$items_DB2['JOINAS']['num']=2;
		foreach ($items_DB2 as $key => $v) {
			for ($x=1;$x<=$v['num'];$x++) {
				$TYPE=($v['num']==1)? $key : "{$key}_{$x}";

				$items[$TYPE]=$this->user->getitembyplace($TYPE);
			}
		}
		unset($items_DB2);

		for ($x=1;$x<=$place;$x++) {
			$TYPE="BANK_$x";
			if (!$this->user->getitembyplace($TYPE)) {
				$item=$this->user->getunsetitem();
				if ($item) {
					$item->setplace($TYPE);
				}
			 } else {
				$item=$this->user->getitembyplace($TYPE);
			 }
			 if ($item)
				 if ($item->id!=$deleted)
					$items[$TYPE]=$item;
			 unset($item);
		}

		$this->view->items=$items;
		$this->view->items_DB=$items_DB;
		$this->view->bankplace=$place;
		$this->view->screen=$this->screen;
		$this->view->lvl=$this->user->getlvl();
		$this->view->race=$this->user->getrace();
		$this->render();
	}
	public function workAction() {
		$do=$this->_getParam('do');
		if (in_array($do,array(JOB_HUNT,JOB_JOB)) && ($this->post->time>0) && $this->user->isfree()) {
			$this->view->set=$this->user->setjob($do,$this->post->time);
		}

        list($work, $time) = $this->user->uzimtumas('work');
        
		$this->view->work=$work;
		$this->view->time=$time;
		$this->view->maxhunt=$this->user->get_max_hunt();
		$this->view->hp=$this->user->gethp();
		$this->view->maxhp=$this->user->maxhp();
		$this->view->hunt_bonus=$this->user->get_hunt_bonus();

		$this->render();
	}
	public function eventAction() {
		if ($this->_getParam('refresh')==1) {
			$this->user->renew_last_event();
		}

		$type = $this->_getParam('tipas', EVENT_ALL);

		$max=20;
		$page=$this->_getParam('page',1);
		$start = $max*($page-1);

		$event = new Events();

		if ($this->_getParam('delete')==1) {
			$event->delevents($this->user->getid(), $type);
		}

		$num = $event->num_events($this->user->getid(), $type);

		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_Null($num));
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($max);
		$paginator->setPageRange(10);

		$this->view->paginator = $paginator;
		$this->view->data = $event->getevents($this->user->getid(),$max,$start, $type);
		$this->view->last_event = date(DATE_FORMAT,$this->user->get_last_event());
		$this->view->num = $num;
		$this->view->screen = $this->screen;
		$this->view->page = $page;
		$this->view->type = $type;

		$this->render();
	}

	public function pmAction() {
		if ($this->user->demo) {
			$this->getResponse()->appendBody("demo vartotojams čia negalima");
			return false;
		}
		$max=20;
		$page=$this->_getParam('page',1);
		$start=$max*($page-1);

		$PM=new PMs();

		if ($this->_getPost('send')==1) {
			$to = $this->_getPost('to');
			$title = $this->_getPost('title');
			$text = $this->_getPost('text');
			$fromid = $this->_getPost('fromid');

			$social = new Social();

			$users = new Users();

			if (!preg_match(PATTER_NAME,$to)) {
				$this->alert("Blogai nurodytas adresatas");
			} elseif (!($toid = $users->getid($to))) {
				$this->alert("Neegzistuojantis vartotojas");
			} elseif ($social->getStatus($toid,$this->user->getid()) == Social::IGNORE) {
				$this->alert("$to jus užblokavo");
			} elseif (!$PM->sendpm($toid,$this->user->getid(),$title,$text,$fromid)) {
				$this->alert("Klaida siunčiant žinutę");
			} else {
				$this->alert("Žinutė išsiųsta");
			}
		}

		$delete=$this->_getParam('delete');
		if ($delete>0) {
			$msg=($PM->deletepm($this->user->getid(),$delete))?
				"Pranešimas ištrintas" :
				"Deja pranešimos ištrinti nepavyko";
			$this->alert($msg);
		}
		unset($delete);

		$delete2=$this->_getPost('delete2');
		if ($delete2==1) {
			$num=0;
			foreach ($this->post->items as $v) {
				if ($PM->deletepm($this->user->getid(),$v))
					$num++;
			}
			$this->alert("Ištrinta $num pranešimų");
		}
		unset($delete2);

		if ($this->_getPost('deleteall')==1) {
			$num=$PM->deleteall($this->user->getid());
			$this->alert("Ištrinta $num pranešimų");
		}

		$refresh=$this->_getParam('refresh');
		if ($refresh==1) {
			$num=0;
			foreach ($this->post->items as $v) {
				if ($PM->makeread($v))
					$num++;
			}
			$this->addrun("Dialogs.alert('Pažymėti kaip skaityti $num pranešimai');");
		}
		unset($refresh);

		$num=$PM->count_pm($this->user->getid());

		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_Null($num));
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($max);
		$paginator->setPageRange(10);

		$this->view->paginator=$paginator;
		$this->view->data=$PM->getpms($this->user->getid(),$max,$start);
		$this->view->num=$num;
		$this->view->screen=$this->screen;
		$this->view->page=$page;

		$this->view->users=new Users();

		$this->render();
	}

	public function atackAction() {
		if ($this->user->demo) {
			$this->getResponse()->appendBody("demo vartotojams čia negalima");
			return false;
		}
		$this->view->lvl=$this->user->getlvl();

		$this->view->search=$this->_getParam('search');

		$users=new Users();
		if (isset($this->post->search_name)) {
			$search_name=$this->post->search_name;
			if (strtoupper($search_name)==strtoupper($this->user->getname())) {
				$this->view->error1="Negalima pulti savęs";
				
			} elseif (!$users->exsist($search_name, null, true)) {
				$this->view->error1="Deja tokio žaidėjo čia nėra";
			} else {
				$user=new User($users->getid($search_name));
			}
		}
		if (isset($this->post->search_lvl)) {
			if ($this->user->getmoney()>=10) {
				$lvl=new Minmax($this->post->min_lvl,$this->post->max_lvl);
				$user=$users->search4atack($lvl,$this->user->enemy());
				$this->user->addmoney(-10);
				if (!$user) {
					$this->view->error1="Pagal nurodytus kriterijus neradome jums priešo";
				}
			} else {
				$this->view->error1="Deje neturite agonų paieškai";
			}
		}
		if (isset($user)) {
			if ($user) {
				$stop=false;
				if (!$user->candefence()) {
					$this->view->error1="Jūsų priešininkas turi ilsėtis";
					$stop=true;
				}
				if ($user->getrace()==$this->user->getrace()) {
					$this->view->error1="Jūsų priešinikas negali būti tos pačios rasės kaip ir jūs";
					$stop=true;
				}
				if (($user->gethp()/$user->maxhp())<0.2) {
					$this->view->error1="Jūsų priešininkas neturi pakankamai gyvybių";
					$stop=true;
				}
				if (($user->get_job()==JOB_HEAL) && ($user->time_until()>0)) {
					$this->view->error1="Jūsų priešininkas gydosi ligoninėje";
					$stop=true;
				}
				if (($user->demo)) {
					$this->view->error1="Demo vartotojo pulti negalima";
					$stop=true;
				}
				if ($user->hidden > $this->user->getOrientacija()) {
					$this->view->error1 = "Deja varžovas yra gerai pasislėpęs. Norėdami jį rasti turite tobulinti orientaciją";
					$stop = true;
				}
				if (!$stop) {
					$_SESSION['atack_user']=$user->getid();
					$this->view->finded=true;
					$this->view->deflvl=$user->getlvl();
					$this->view->defname=$user->getname();
				}
			}
		}

		if ((isset($this->post->atack)) && (isset($_SESSION['atack_user']))) {
			$user=new User($_SESSION['atack_user']);
			unset($_SESSION['atack_user']);
			if ($user->candefence() && (($user->gethp()/$user->maxhp())>0.2)) {
				$msg=$this->user->atack($user);
				if ($msg->winner=="A") {
					$this->view->good=$msg->event_a;
				} else {
					$this->view->bad=$msg->event_a;
				}
				$this->view->atacked=true;
				$this->view->finded=true;
			}
		}

		$hunt=$this->_getPost('hunt');
		if ($hunt>0 && (@$_SESSION['last-hunt']<time())) {
			$mob=new Mob($hunt);

			$msg=$this->user->atack($mob,true);
			if ($msg->winner=="A") {
				$this->view->good=$msg->event_a;
			} else {
				$this->view->bad=$msg->event_a;
			}
			$this->view->atacked=true;
			$this->view->finded=true;
			$_SESSION['last-hunt']=time()+100;
		}

		$this->render();
	}
	public function onlineAction() {
		$online=new Online();
		$data=$online->get_online_list(array('name','exp','race','id'),'exp DESC',true);

		$this->view->data=$data;

		$this->render();
	}
	public function helpAction() {
		$help=new Help();

		$page=$this->_getParam('page',1);
		$num=$help->num();

		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_Null($num));
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage(1);
		$paginator->setPageRange(15);

		$filterChain = new Zend_Filter();
		$filterChain->addFilter(new BBcode());

		$item=$help->get_one($page);

		$this->view->title=$item->title;
		$this->view->text=$filterChain->filter($item->text);
		$this->view->paginator=$paginator;
		$this->view->screen=$this->screen;

		$this->render();
	}
	public function topAction() {
		$users=new Users();

		$race=$this->_getParam("race",RACE_ALL);
		$type=$this->_getParam("type","exp");

		$s_name=$this->_getPost('s_name');
		$s_place=$this->_getPost('s_place');

		$s_id=$users->getid($s_name);

		$s_id=($s_id)? $s_id : $this->user->getid();


		$num=$users->countuser($race);
		$this->view->screen=$this->screen;
		if ($s_place) {
			$this->view->place=$s_place;
		} else {
			$this->view->place=$users->get_place($s_id,$type,($this->user->getrace()==$race)? $race : RACE_ALL);
		}

		$userplace=($this->user->getid()==$s_id)?
			 $this->view->place :
			 $users->get_place($this->user->getid(),$type,($this->user->getrace()==$race)? $race : RACE_ALL);

		$max=20;
		$def_place=floor($this->view->place/$max)+1;
		$page=$this->_getParam('page',$def_place);
		$start=$max*($page-1);

		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_Null($num));
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($max);
		$paginator->setPageRange(5);

		$this->view->paginator=$paginator;
		$this->view->data=$users->get_top("$type DESC",$race,$max,$start);
		$this->view->start=$start;
		$this->view->page=$page;
		$this->view->race=$race;
		$this->view->type=$type;
		$this->view->userplace=$userplace;

		$this->view->user=$this->user;

		$this->render();
	}

	public function marketAction() {
		if ($this->user->demo) {
			$this->getResponse()->appendBody("demo vartotojams čia negalima");
			return false;
		}
		$cost=$this->_getParam('cost');
		$type=$this->_getParam('itemtype');
		$min_lvl=$this->_getParam('min_lvl',ceil($this->user->getlvl()/2));
		$max_lvl=$this->_getParam('max_lvl',$this->user->getlvl());
		$title=$this->_getParam('title');
		$owner=($this->_getParam('owner')==1)? $this->user->getid() : null;
		$race=($this->_getParam('race2',1)==1)? $this->user->getrace() : null;



		$max=50;
		$page=$this->_getParam('page',1);
		$start=$max*($page-1);

		$market=new Market();

		$buy=$this->_getPost('buy');
		if ($buy>0) {
			$costas=$market->getcost($buy);
			$owneris=$market->getowner($buy);
			if ($owneris==$this->user->getid()) {
				$msg="Tai jūsų daiktas";
			} elseif ($this->user->getmoney()<$costas) {
				$msg="Deja neturi agonų";
			} else {
				$itemas=$market->getItem($buy);
				if ($market->buy($buy,$this->user->getid())) {
					$this->user->addmoney(-1*$costas);
					$msg="Daiktas nupirktas už $costas";
					$user=new User($owneris);
					$user->addmoney($costas);
					$user->sendevent("Iš jūsų nupirko {$this->user->getname()} <b>{$itemas->itemLink()}</b> už $costas", EVENT_OTHER);
					$this->user->sendevent("Nupirkote <b>{$itemas->itemLink()}</b> iš {$user->getname()} už $costas", EVENT_OTHER);
					$user->save();
					unset($user);
				} else {
					$msg="Perkant įvyko klaida. Gali būti jog nespėjote";
				}
			}
			$this->addrun("Dialogs.alert('$msg');");
		}
		$remove=$this->_getPost('remove');
		if ($remove>0) {
			$owneris=$market->getowner($remove);
			if ($owneris==$this->user->getid()) {
				$market->buy($remove,$owneris);
				$msg="Daiktas sugrąžintas jums";
			} else {
				$msg="Tai ne jūsų daiktas";
			}
			$this->addrun("Dialogs.alert('$msg');");
		}

		$num=$market->count((int)$min_lvl,(int)$max_lvl,$cost,$type,$owner,$race,$title);

		$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_Null($num));
		$paginator->setCurrentPageNumber($page);
		$paginator->setItemCountPerPage($max);
		$paginator->setPageRange(10);

		$items_DB=include('app/data/item_DB.php');
		unset($items_DB['GUN_MAIN']);
		unset($items_DB['GUN_SECOND']);

		$items_DB['GUN']=array('title' => 'Ginklas');

		$this->view->paginator=$paginator;
		$this->view->cost=$cost;
		$this->view->type=$type;
		$this->view->min_lvl=$min_lvl;
		$this->view->max_lvl=$max_lvl;
		$this->view->owner=$owner;
		$this->view->race=$race;
		$this->view->title=$title;
		$this->view->lvl=$this->user->getlvl();
		$this->view->userid=$this->user->getid();
		$this->view->user_race=$this->user->getrace();
		$this->view->items=$items_DB;
		$this->view->all_place=max_market_slot($this->user->get_skill_sum("TRADE"));
		$this->view->use_place=$market->count_by_user($this->user->getid());

		//spalvų rodymas
		if (!isset($_SESSION['colors']))
			$_SESSION['colors']=false;

		$colors=$this->_getParam("colors");
		$setcolors=$this->_getParam("setcolors");
		if (isset($setcolors)) {
			$_SESSION['colors']=($colors==1)? true : false;
			$colors=$_SESSION['colors'];
		} else {
			$colors=$_SESSION['colors'];
		}
		$this->view->colors=$colors;

		$this->view->data=$market->getitems($max,$start,(int)$min_lvl,(int)$max_lvl,$cost,$type,$owner,$race,$title);
		$this->render();
	}

	public function hospitalAction() {
		$this->view->user=$this->user;

		$doctors=require('app/data/doctors.php');

		$idoctor=$this->_getPost("doctor");
		$time=$this->_getPost("time");

		if (($idoctor>0) && ($time>0) && ($this->user->isfree())) {
			$doctor=$doctors[$idoctor];

			$diff=$this->user->maxhp()-$this->user->gethp();
			$max4hp=floor($diff/$doctor['hp']);
			$max4time=floor($this->user->getmoney()/$doctor['cost']);
			$time=min($time, $max4hp, $max4time);

			if ($time>0) {
				$cost=$time*$doctor['cost'];

				$this->user->heal=$idoctor;
				$this->user->setjob(JOB_HEAL,$time);
				$this->user->addmoney(-1*$cost);

				$this->view->setted=true;
				$this->view->good="Gydimas pradėtas ir bus baigtas po $time minučių";
			} else {
				$this->view->bad="Jūs esate sveikas arba neturite pakankamai agonų gydymui";
			}
		}
		$this->view->doctors=$doctors;

		$this->render();
	}

	public function forumAction() {
		if ($this->user->demo) {
			$this->getResponse()->appendBody("demo vartotojams čia negalima");
			return false;
		}
		$subid=$this->_getParam("subid");
		$root=$this->_getParam("root");

		if (($this->_getParam("clan")) || ($this->_getParam("cid"))) {
			$clanmembers=new ClanMembers();
			$cid=$clanmembers->getClanId($this->user->getid());
			$subid=0;
		} else {
			$cid=0;
		}

		$forum=new Forum();

		if (empty($subid) && ($cid==0)) {
			$forums=include('app/data/forum.php');

			switch ($this->user->getrace()) {
				case RACE_MAN : unset($forums[3]); break;
				case RACE_RJAL : unset($forums[2]); break;
			}

			foreach ($forums as $key => &$v) {
				$v['topics']=$forum->count($key,0);
				$v['posts']=$forum->count($key)-$v['topics'];
				$v['lastdate']=$forum->getLastDate($key,null,$cid);
				$v['lastname']=$forum->getLastName($key,null,$cid);
			}

			$this->view->forums=$forums;
			$this->render("forum_start");
		} elseif (empty($root)) {
			$write=$this->_getPost('write');
			if ($write==1) {
				$title=$this->_getPost('title');
				$text=$this->_getPost('text');
				$forum->newTopic($title,$text,$this->user->getid(),$subid,$cid);
				$this->addrun("Dialogs.alert('Tema sukurta');");
			}

			$max=20;
			$page=$this->_getParam('page',1);
			$start=$max*($page-1);

			$num=$forum->count($subid,0,$cid);

			$paginator=new Zend_Paginator(new Zend_Paginator_Adapter_Null($num));
			$paginator->setCurrentPageNumber($page);
			$paginator->setItemCountPerPage($max);
			$paginator->setPageRange(10);

			$this->view->data=$forum->get_forum($subid,$max,$start,$cid);
			$this->view->screen=$this->screen;
			$this->view->paginator=$paginator;
			$this->view->subid=$subid;
			$this->view->cid=$cid;

			$this->render("forum-topics");
		} else {
			$write=$this->_getPost('write');
			if ($write==1) {
				$text=$this->_getPost('text');
				$forum->newPost($text,$this->user->getid(),$subid,$root,$cid);
				$this->addrun("Dialogs.alert('Atsakyta');");
			}

			$this->view->root=$root;
			$this->view->cid=$cid;
			$this->view->subid=$subid;
			$this->view->screen=$this->screen;
			$this->view->title=$forum->find($root)->getRow(0)->title;
			$this->view->data=$forum->getmsg($root);

			$this->render("forum-view");
		}
	}

	public function clanAction() {
		if ($this->user->demo) {
			$this->getResponse()->appendBody("demo vartotojams čia negalima");
			return false;
		}
		$clan=new Clans();
		$clanmembers=new ClanMembers();
		$claninvites=new ClanInvites();
		$users=new Users();

		$make_clan = $this->_getParam('make_clan');
		$clan_name = $this->_getPost('clan_name');
		$clan_tag = $this->_getPost('clan_tag');
		if ($make_clan && $clan_name && $clan_tag) {
			if ($this->user->getlvl() < 20) {
				$this->alert("Klaną galima kurti tik nuo 20 lygio");
			} elseif ($this->user->getmoney() > 50000) {
				$clan->makeClan($clan_name,$clan_tag,$this->user->getid());
				$this->alert('Aljansas sukurtas');
				$this->user->addmoney(0-50000);
			} else {
				$this->alert('Deja, jūs neturite pakankamai agonų');
			}
		}

		$goto=$this->_getPost('goto');
		if ($goto>0) {
			$row=$claninvites->find($goto)->getRow(0);

			$cid=$row->cid;
			$clanas=$clan->getClan($cid);

			if (!$clanas) {
				$this->alert("Toks klanas neegzistuoja");
			} elseif ($clanmembers->getmembers($cid)->count()>=$clanas->max_member()) {
				$this->alert("Klane yra daugiau negu {$clanas->max_member()} žmonės");
			} elseif ($clanmembers->add($this->user->getid(),$cid)) {
				$this->alert("Jūs sėkmingai įstojote į klaną");
				$this->user->sendevent("Jūs sėkmingai įstojote į klaną", EVENT_CLAN);
				$_SESSION['atag']=$clan->getClan($cid)->tag;
				$row->delete();
				$clanas->add_log("{$this->user} įstojo į klaną");
			} else {
				$this->alert("Klaida stojant į klaną");
			}
		}

		$cid=$clanmembers->getClanId($this->user->getid());

		if ($this->_getPost('delete_clan') && $cid) {
			$clan=$clanas=$clan->getClan($cid);

			if ($clan->leader == $this->user->getid()) {
				$nano = $clan->nano;
				$money = $clan->money;
				if ($clan->remove()) {
					$this->alert("Klanas ištrintas");
					$cid = null;
					$this->user->addnano($nano);
					$this->user->addmoney($money);
				} else {
					throw new Exception("Įvyko klaida trinant klaną", ERROR);
				}
			}
		}

		if (!$cid) {
			$this->view->cantCreate = ($this->user->getlvl() < 20);
			$this->view->invites = $claninvites->getinvites($this->user->getid());
			$this->render('noclan');
			return 0;
		}

		$this->view->clan=$clanas=$clan->getClan($cid);
		$this->view->user=$this->user;
		$this->view->members=$clanmembers->getmembers($cid);
		$this->view->leadername=$users->getname($this->view->clan->leader);

		$send_money_num=$this->_getPost('send_money_num');
		if ($send_money_num>0) {
			if ($this->user->getmoney()>=$send_money_num) {
				$clanas->money+=$send_money_num;
				$this->user->addmoney(0-$send_money_num);
				$this->alert("Sėkmingai paaukojote $send_money_num");
				$clanas->save();
				$clanas->add_log("{$this->user} paaukojo $send_money_num agonų");
			} else {
				$this->alert("Neturite $send_money_num agonų");
			}
		}

		$send_nano=$this->_getPost('send_nano');
		if ($send_nano) {
			if ($this->user->ckecknano(1)) {
				$this->user->addnano(-1);
				$this->alert('Klanui paaukojote 1 nano kreditą');
				$clanas->nano++;
				$clanas->save();
				$clanas->add_log("{$this->user} paaukojo 1 nano kreditą");
			} else {
				$this->alert('Neturite nano kredito');
			}
		}

		if ($this->_getPost('money_out')==1) {
			$player=$this->_getPost('player');
			$money=$this->_getPost('send_money_out');
			try {
				if ($this->user->getid()!=$clanas->leader) {
					throw new Exception("Jūs neesate lyderis", ERROR);
				} elseif ($money<=0) {
					throw new Exception("Negalima siųsti tokio kiekio pinigų",ERROR);
				} elseif (!$clanas->is_user($player)) {
					throw new Exception("Tai ne jūsų klaniškis", ERROR);
				} else {
					$money=min($clanas->money,$money);
					$money_get=round($money*0.95);
					$clanas->money-=$money;
					$useris=($this->user->getid()==$player)? $this->user : new User($player);

					$clanas->add_log("Iš klano iždo {$useris} pervesta $money agonų");
					$clanas->save();

					$useris->addmoney($money_get);
					$useris->save();
					unset($useris);

					$this->alert("Pinigai sėkmingia pervesti");
				}
			} catch (Exception $e) {
				if ($e->getCode()==ERROR) {
					$this->alert($e->getMessage());
				} else {
					throw $e;
				}
			}
		}

		$this->view->clan_lvl=$clan_lvl=include("app/data/clan_lvl_DB.php");
		$lvl_up=$this->_getPost('lvl_up');
		$clvl=$clanas->lvl;
		if ($lvl_up) {
			if ($clanas->money<$clan_lvl[$clvl]['money']) {
				$this->alert("Ižde nėra pakankamai agonų klano tobulinimui");
			} elseif ($clanas->nano<$clan_lvl[$clvl]['nano']) {
				$this->alert("Klanas neturi pakankamai nano kreditų");
			} else {
				$clanas->lvl++;
				$clanas->money-=$clan_lvl[$clvl]['money'];
				$clanas->nano-=$clan_lvl[$clvl]['nano'];
				$clanas->save();
				$this->alert("Klano lygis padidintas");
				$clanas->add_log("Klano lygis pakeltas. Dabar jo lygis yra {$clanas->lvl}");
			}
		}

		if ($this->_getPost('invite')==1) {
			$name=$this->_getPost('name');
			$reason=$this->_getPost('reason');

			$userid=$users->getid($name);

			if ($name && $reason) {
				if (!$userid) {
					$this->alert("Deja, šiats žaidėjas neegzistuoja");
				} elseif ($this->view->members->count()>=$clanas->max_member()) {
					$this->alert("Klane telpa tik 5 žmonės");
				} elseif ($clanmembers->getClanId($userid)) {
					$this->alert("Šis narys jau klane");
				} elseif ($users->getuser($userid)->getrace()!=$this->user->getrace()) {
					$this->alert("Narys negali būti kitos rasės");
				} elseif ($claninvites->invite($cid,$userid,$reason)) {
					$this->alert("$name sėkmingai pakviestas į klaną");
					$user=new User($userid);
					$user->sendevent("Jus kviečia į klaną {$this->view->clan->title}. Priežastis: $reason", EVENT_CLAN);
					$clanas->add_log("$name pakviestas į klaną");
				} else {
					$this->alert("Deja $name pakviesti nepavyko");
				}
			}
		}

		$remove=$this->_getPost('remove');
		if ($remove>0) {
			$data=$clanmembers->find($remove);
			if ($data->count()==0) {
				throw new Exception('Klaida',ERROR);
			}
			$cm=$data->getRow(0);

			$data=$clan->find($cm->cid);

			if ($data->count(0)==0) {
				throw new Exception('Neegzistuojantis klanas',ERROR);
			}

			$clanas=$data->getRow(0);

			if ((($clanas->leader==$this->user->getid()) && ($cm->user!=$this->user->getid())) || (($clanas->leader!=$this->user->getid()) && ($cm->user==$this->user->getid()))) {
				$this->view->members=$clanmembers->getmembers($cid);
				$user=new User($cm->user);
				if ($clanas->leader==$this->user->getid()) {
					$user->sendevent("Jūs buvote pašalintas iš <b>{$clanas->title}</b> klano", EVENT_CLAN);
					$clanas->add_log("{$user} buvo pašalintas iš klano");
				} else {
					$_SESSION['atag']=null;
					$clanas->add_log("{$user} išėjo iš klano");
				}
                $cm->delete();
				unset($user);
			} else {
				throw new Exception('Jūs neturite teisių išmesti šio žmogaus iš aljanso',ERROR);
			}
		}

		$leader=($this->user->getid()==$this->view->clan->leader);

		$this->view->leader=$leader;
		$this->render();
	}

	public function settingsAction() {
		if ($this->user->demo) {
			$this->getResponse()->appendBody("demo vartotojams čia negalima");
			return false;
		}
		$users=new Users();
		$this->view->data=$users->find($this->user->getid())->getRow(0);
		$this->view->user=$this->user;
		
		if (isset($_SESSION['delete-key']) && $_SESSION['delete-key'] == $this->_getParam('delete', -5)) {
			$u = $users->find($this->user->getid())->getRow(0);
			$u->visible = 0;
			$u->was_mail = $u->mail;
			$u->mail = null;
			$u->save();
			$this->alert("Jūsų registracija panaikinta. Ačiū, kad buvote su mumis. Sėkmės gyvenime ;)");
		}

		if ($this->_getPost('val')==1) {
			$mail=$this->_getPost('mail');
			if (!preg_match(PATTER_MAIL,$mail) || preg_match(PATTER_NOMAIL,$mail)) {
				$this->view->error_mail="Netinkamas pašto formatas";
			} else {
				$this->view->data->new_mail=$mail;
				$this->view->data->save();
				if ($users->sendval($this->user->getid())) {
					$this->view->good="Nusiųstas patvirtinimo laiškas į jūsų pašto dėžutę.";
				} else {
					$this->view->error="Laiškas nenusiųstas į jūsų pašto dėžute. Gal būt jus per dažnai siunčiate patvirtinimo laišką";
				}
			}
		}

		if ($this->_getPost('save')==1) {
			$psw1=$this->_getPost('psw1');
			$psw2=$this->_getPost('psw2');
			$mail=$this->_getPost('mail');
			$sex=$this->_getPost('sex');
			$vardas=$this->_getPost('vardas');
			$about=$this->_getPost('about');
			if ($psw1 && $psw2) {
				$gerai=true;
				if (!preg_match(PATTER_PSW,$psw2)) {
					$this->view->error_psw="Netinkamas slaptažodis";
					$gerai=false;
				}
				if ($psw1!=$psw2) {
					$this->view->error_psw="Nesutampa slaptažodžiai";
					$gerai=false;
				}
				if ($gerai) {
					$this->view->data->psw=encodepsw($psw1);
					$this->view->data->save();
					$this->view->good="Slapažodis pakeistas";
				}
			}

			if ($mail!=$this->view->data->mail) {
				if (!preg_match(PATTER_MAIL,$mail) || preg_match(PATTER_NOMAIL,$mail)) {
					$this->view->error_mail="Netinkamas pašto formatas";
				} else {
					$this->view->data->new_mail=$mail;
					$this->view->data->val=genkey(16);
					$this->view->data->save();

					$users->sendval($this->user->getid());

					$this->view->good="Pašto adresas pakeistas";
				}
			}

			if (preg_match('/^[\S]+$/',$vardas)) {
				$this->view->data->vardas=$vardas;
			}
			if (in_array($sex,array("N","V","M"))) {
				$this->view->data->sex=$sex;
			}
			$this->view->data->about=$about;

			$this->view->data->save();

			if (!$this->view->good) {
				$this->view->good="Duomenys pakeisti";
			}
		}

		$this->render();
	}

	public function userinfoAction() {
		$name=$this->_getParam("name");
		if ($name) {
			$users=new Users();
			$uid=$users->getid($name);
		} else {
			$uid=$this->_getParam('uid',$this->user->getid());
		}

		if ($this->user->getid()==$uid) {
			$this->view->user=$this->user;
		} else {
            $users = new Users();
            $this->view->user = $users->getuser($uid, false, true);
		}
		$this->view->myuser=$this->user;

		if ($this->_getPost('vote')) {
			$this->view->user->has+=1;
			$this->view->myuser->vote+=1;
		}

		$this->render();
	}

	public function claninfoAction() {
		$clans=new Clans();
		$clanmembers=new ClanMembers();
		$users=new Users();

		$tag=$this->_getParam("tag");
		if ($tag) {
			$clan=$clans->tag2Clan($tag);
		} else {
			$clan=$clans->getClan($this->_getParam("cid",$clans->getClanByUser($this->user->getid())));
		}

		if (!($clan instanceof Clan))
			throw new Exception('Toks klanas neegzistuoja',ERROR);

		$this->view->members=$clanmembers->getmembers($clan->id);
		$this->view->clan=$clan;
		$this->view->leadername=$users->getname($clan->leader);
		$this->render();
	}
	
	public function workshopAction() {
		$place = $this->_getParam('place', 1);
		
		if ($place == 1) {
			$this->render();
		} elseif ($place == 2) {
			$this->user->reload_item_cache();
			
			$i1 = $this->_getPost('i1');
			$i2 = $this->_getPost('i2');
			
			if ($i1 && $i2) {
				$item1 = new Item($i1);
				$item2 = new Item($i2);
				
				$tipas = $this->_getPost('tipas');
				
				if ($item1->owner != $this->user->getid() || $item2->owner != $this->user->getid())
					$this->alert("Tai ne jūsų emblemos");
				elseif ($tipas < 1 || $tipas > 2)
					$this->alert("Blogas tipas");
				elseif ($tipas == 1 && Item::countJoinEmblemCost($item1, $item2) > $this->user->getmoney())
					$this->alert("Neturite pakankamai agonų");
				elseif ($tipas == 2 && $this->user->getnano() < 3)
					$this->alert("Neturite nano kreditu");
				else { 
					$item = Item::joinEmblem($item1, $item2);
					$this->alert("Sujungėte emblemas ir gavote: {$item->itemLink()}");
					
					if ($tipas == 1)
						$this->user->addmoney(-Item::countJoinEmblemCost($item1, $item2));
					else
						$this->user->addnano(-3);
					
					$this->user->reload_item_cache();
					$this->user->save_item_cache();
				}
			}
			
			$emblems = $this->user->getItemsByType("EMBLEM");
			
			$this->view->emblems = $emblems;
			
			$this->render("workshop2");
		} elseif ($place == 3) {
			$this->user->reload_item_cache();
			
			$i1 = $this->_getPost('i');
			
			if ($i1) {
				$item1 = new Item($i1);
				
				$tipas = $this->_getPost('tipas');
				
				if ($item1->owner != $this->user->getid())
					$this->alert("Tai ne jūsų emblemos");
				elseif ($tipas < 1 || $tipas > 2)
					$this->alert("Blogas tipas");
				elseif ($tipas == 1 && Item::countEmblemChangeCost($item1) > $this->user->getmoney())
					$this->alert("Neturite pakankamai agonų");
				elseif ($tipas == 2 && $this->user->getnano() < 1)
					$this->alert("Neturite nano kreditu");
				else { 
					$item = Item::changeEmblem($item1);
					$this->alert("Pakeitėte emblemą ir gavote: {$item->itemLink()}");
					
					if ($tipas == 1)
						$this->user->addmoney(-Item::countEmblemChangeCost($item1));
					else
						$this->user->addnano(-1);
					
					$this->user->reload_item_cache();
					$this->user->save_item_cache();
				}
			}
			
			$emblems = $this->user->getItemsByType("EMBLEM");
			
			$this->view->emblems = $emblems;
			
			$this->render("workshop3");
		}
	}

	public function clantopAction() {
		$clans=new Clans();
		$clan_members=new ClanMembers();
		$this->view->clanid=$clan_members->getClanId($this->user->getid());
		$this->view->data=$clans->clanTop();
		$this->view->screen=$this->screen;
		$this->render();
	}

	public function nanoAction() {
		$this->view->nano=$this->user->getnano();
		$this->view->id=$this->user->getid();
		$this->view->name=$this->user->getname();
		$this->render();
	}

	public function shopAction() {
		if (!$this->user->isadmin()) {
		//	$this->getResponse()->appendBody('Kažkur dingo pardavėja!!, gal matei?');
		}

		$shop=new Shop();

		$buy=$this->_getPost('buy');
		$type=$this->_getPost('type');
		if (in_array($type,array(1,2)) && ($buy>0)) {
			$data=$shop->find($buy);
			if ($data->count()==0) {
				$this->alert("Neegzistuojanti prekė");
			} else {
				$row=$data->getRow(0);
				$need=($type==1)? $row->nano : $row->money;
				$has=($type==1)? $this->user->getnano() : $this->user->getmoney();

				if ($need<=$has) {
					$item=new Item($row->item);
					$item->owner=$this->user->getid();
					$item->save();

					if ($type==1) {
						$this->user->addnano(0-$need);
					} else {
						$this->user->addmoney(0-$need);
					}
					$shop->buy($buy);
					$this->alert("Sėkmingai nusipirkote {$item->itemLink()}");
				} else {
					$this->alert("Neturite pakankamai ".(($type==1)? "nano kreditų" : "pinigų"));
				}
			}
		}

		$this->view->data=$shop->get($this->user->getid(),false);
		$this->render();
	}

	public function socialAction() {
		$social = new Social();
		$this->view->members = $social->get($this->user->getid());

		$this->render();
	}
    
    public function missionAction() {
        $mission = new Missions();
        
        $accept = $this->_getPost('accept');
        if (is_numeric($accept)) {
            if ($m = $mission->getById($accept, true)) {
                if (($m->minlvl > $this->user->getlvl()) || ($m->maxlvl < $this->user->getlvl()))
                    throw new Exception("Netinkamas jūsų lygis", ERROR);
                    
                $m->uid = $this->user->getid();
                $m->start();
                $this->view->good = "Sėkmingai pradėjote misiją";    
            } else {
                $this->view->bad = "Toks darbas neegzistuoja, arba jis užimtas";
            }
        }
        
        $select = $mission->select()->where("minlvl < ?", $this->user->getlvl())
                                    ->where("maxlvl > ?", $this->user->getlvl())
                                    ->where("uid = 0")
                                    ->limit(10);
                                    
        $this->view->data = $mission->fetchAbstract($select);
        
        $this->render();
    }

	public function arenaAction() {
		if (!Zend_Registry::get('config')->arena)
			throw new Exception("Arena išjungta", ERROR);
		if ($this->user->demo)
			throw new Exception("Demo vartotojui čia negalima", ERROR);

		$atack=new Atack();
		$fighters=new Fighters();
		$maps=include("app/data/maps.php");

		$mysql_date=date(DATE_FORMAT2);

		$this->view->shedules=$fighters->countFightShedules($this->user->getid());
		$this->view->creates=$atack->countCreator($this->user->getid());
		$this->view->maps=$maps;

		$this->view->title=$title=$this->_getPost('title');
		$this->view->min_lvl=$min_lvl=$this->_getPost('min_lvl');
		$this->view->max_lvl=$max_lvl=$this->_getPost('max_lvl');
		$this->view->time=$time=$this->_getPost('time');
		$this->view->duration=$duration=$this->_getPost('duration');
		$this->view->map=$map=$this->_getPost('map');
		$this->view->width=$width=$this->_getPost('width');
		$this->view->height=$height=$this->_getPost('height');
		$this->view->size=$size=$this->_getPost('size');
		$this->view->password=$password=$this->_getPost('password');
		$this->view->create=$create=$this->_getPost('create');

		if ($create==1) {
			$start=strtotime($time);
			$end=$start+$duration*60;

			$start=date(DATE_FORMAT2, $start);
			$end=date(DATE_FORMAT2, $end);

			if ($this->view->creates>0) {
				$this->view->bad="Jūs galite būti vienu metu sukuręs ne daugiau kaip vieną kovą";
			} elseif (!trim($title)) {
				$this->view->bad="Neįrašėte pavadinimo";
			} elseif (min($max_lvl, $min_lvl)<0) {
				$this->view->bad="Lygis negali būti neigiamas";
			} elseif ($max_lvl<$min_lvl && $max_lvl>0) {
				$this->view->bad="Maksimalus lygis negali būti mažesnis už minimalų";
			} elseif (($this->user->getlvl()<$min_lvl && $min_lvl>0) || ($this->user->getlvl()>$max_lvl && $max_lvl>0)) {
				$this->view->bad="Pagal nurodytus lygius jūs negalite dalyvauti kovoje";
			} elseif (date(DATE_FORMAT2)>$time) {
				$this->view->bad="Jūsų nurodytas laikas jau praėjo";
			} elseif ($duration<0 | $duration>120) {
				$this->view->bad="Blogai nurodytas maksimalus kovos ilgis";
			} elseif (!array_key_exists($map, $maps)) {
				$this->view->bad="Toks Žemėlapis neegzistuoja";
			} elseif ($maps[$map]['min_width']>$width || $maps[$map]['max_width']<$width) {
				$this->view->bad="Ilgis gali būti nuo {$maps[$map]['min_width']} iki {$maps[$map]['max_width']}";
			} elseif ($maps[$map]['min_height']>$height || $maps[$map]['max_height']<$height) {
				$this->view->bad="Aukštis gali būti nuo {$maps[$map]['min_height']} iki {$maps[$map]['max_height']}";
			} elseif ($size%2==1 || $size<=0 || $size>10) {
				$this->view->bad="Blogai pasirinktas komandų dydis";
			} elseif ($atack->checkFighterByRezis($this->user->getid(), $start, $end)) {
				$this->view->bad="Jūs nustatytų laiku esate užimtas";
			} else {
				$data=array(
					"create_date" => date(DATE_FORMAT2),
					"start" => $start,
					"end" => $end,
					"fight" => "N",
					"public" => "N",
					"can_revive" => "N",
					"dead_limit" => 0,
					"creator" => $this->user->getid(),
					"money" => 0,
					"can_join" => 0,
					"width" => $width,
					"height" => $height,
					"title" => $title,
					"mix" => "N",
					"size" => $size,
					"password" => $password,
					"map" => $map,
					"auto" => "N",
					"min_lvl" => $min_lvl,
					"max_lvl" => $max_lvl,
					'side' => "L",
					'type' => ATACK_TYPE_ARENA
				);

				if ($aid=$atack->insert($data)) {
					$data=array(
						"uid" => $this->user->getid(),
						"side" => "L",
						"auto" => "N",
						'aid' => $aid
					);

					if ($fighters->insert($data)) {
						$this->view->good="Kova sukurta";
						$this->view->creates++;
					} else {
						$this->view->bad="Kovos sukurti nepavyko";
					}
				} else {
					$this->view->bad="Kovos sukurti nepavyko";
				}
			}
		}

		$join=$this->_getPost('join');
		$password=$this->_getPost('password');
		if (is_numeric($join) && $join>0) {
			$data=$atack->find($join);
			if ($data->count()==0) {
				$this->view->bad="Tokia kova neegzisutoja";
			} else {
				$row=$data->getRow(0);

				$max=$row->size/2;

				$figers_data=$fighters->getFighters($row->id);

				$left=array();
				$right=array();
				$is=false;
				foreach ($figers_data as $v) {
					if ($v->uid==$this->user->getid())
						$is=true;

					if ($v->side=='L') {
						$left[]=$v;
					} else {
						$right[]=$v;
					}
				}

				$side=($this->user->getrace()==$left[0]->getuser()->getrace())? "L" : "R";

				if ($is) {
					$this->view->bad="Jūs jau esate užsiregistravęs";
				} elseif ($fighters->countFightShedules($join)>=3) {
					$this->view->bad="Jūs negalite būti užsiregistravęs daugiau kaip trijose kovose";
				} elseif ((count($left)>=$max && $side=="L") || (count($right)>=$max && $side=="R")) {
					$this->view->bad="Vietų jums nebeliko";
				} elseif ($row->password && ($row->password!=$password)) {
					$this->view->bad="Blogas slaptažodis";
				} else {
					$data=array(
						"aid" => $join,
						"uid" => $this->user->getid(),
						"side" => $side,
						"auto" => "N"
					);

					if ($fighters->insert($data)) {
						$this->view->good="Jūs prisijungėte";
					} else {
						$this->view->bad="Įvyko klaida";
					}
				}
			}
		}

		$leave=$this->_getPost("leave");

		if (is_numeric($leave) && $leave>0) {
			$data=$fighters->find($leave);

			if ($data->count()==0)
				throw new Exception("Tokia kova neegzisutoja", ERROR);

			$fighter=$data->getRow(0);

			$data=$atack->find($fighter->aid);

			if ($data->count()==0)
				throw new Exception("Tokia kova neegzisutoja", ERROR);

			$ataka=$data->getRow(0);

			if ($ataka->creator==$this->user->getid()) {
				//jeigu reikia trinti visa kova

				if ($fighters->countFightShedules($atack->id)>1) {
					$this->view->bad="Gali panaikinti tik tusčią kovą";
				} else {
					$ataka->delete();
					$fighter->delete();
				}
			} else {
				$date=date(DATE_FORMAT2, time()+3600);

				if ($ataka->start<$date) {
					$this->view->bad-"Negalima pasišalinti iš kovos likus mažiau kaip vienai valandai iki jos pradžios";
				} else {
					$fighter->delete();
				}
			}
		}


		$select=$atack->select()->where("(min_lvl < ?) OR (min_lvl=0)",$this->user->getlvl())
								->where("(max_lvl > ?) OR (max_lvl=0)",$this->user->getlvl())
								->where("start > ?", $mysql_date)
								->where("type = ?", ATACK_TYPE_ARENA);

		$atacks=$atack->fetchAll($select);

		$data=array();
		foreach ($atacks as $v) {
			$info=new stdClass();

			$info->info=$v;
			$info->fighters=$fighters->getFighters($v->id);

			$data[$v->id]=clone $info;

			unset($info);
		}

		$this->view->data=$data;

		$this->render();
	}

	public function reportAction() {
		$tipai = array(
			1 => "Pagalbos prašymas",
			2 => "Pranešti apie žaidime esančia klaidą",
			3 => "Pranešti apie taisyklių pažeidimą",
			4 => "Pasiūlimas",
			5 => "Pranešti apie paramą"
		);

		$ok = $this->_getPost('ok');

		if ($ok == 1) {
			$tipas = $this->_getPost('tipas');
			$zinute = $this->_getPost('zinute');

			$uid = $this->user->getid();
			$name = $this->user->getname();

			$date = date(DATE_FORMAT);

			$report = new Report();

			if ($report->insert(array(
				"uid" => $uid,
				"username" => $name,
				"type" => $tipas,
				"text" => $zinute
			))) {
				$this->view->good = "Žinutė išsiusta administracijai";

				$mail = new Zend_Mail("utf8");
				$mail->setBodyText("user: $name (#$uid)\nserver: ".getsid()."\ndate: $date\nzinute:\n\n$zinute");
				$mail->setSubject("REPORTAS: {$tipai[$tipas]} nuo $name");
				$mail->setFrom($this->user->mail, $name);
				$mail->addTo("admin@agonija.eu", "Administracija");
				$mail->send();
			} else {
				$this->view->bad = "Žinutės nepavyko nusiųsti";
			}
		}

		$this->view->tipai = $tipai;

		$this->render();
	}
	
	public function eggAction() {
		$user = $this->_getPost('crash');
		
		$users = new Users();
		$def = $users->getuser($user, false);
		
		if (!$def) {
			$this->view->error = 'Varžovas neegzisutoja';
		} elseif ($this->user->getid() == $def->getid()) {
			$this->view->error = 'Savo varžovu negalite būti pats';
		} elseif ($def->egg->has == 0) {
			$this->view->error ='Varžovas neturi velykinių kiaušinių';
		} elseif ($this->user->egg->has == 0) {
			$this->view->error = 'Jūs neturite kiaušinių';
		} else {
			$this->user->egg->has--;
			$def->egg->has--;
			
			if (rand(0, 1) == 0) {
				$this->user->egg->crash++;
				$this->view->good = 'Jūs sukūlėte varžovo kiaušinius';
			} else {
				$def->egg->crash++;
				$this->view->error = 'Jūsų kiaušinis sukultas';
			}
			
			$this->user->egg->save();
			$def->egg->save();
		}
		$this->render();
	}

	public function nologedAction() {
		$this->getResponse()->appendBody("Jūs esate neprisijungęs, arba neatitinka sesijos raktas (tai gali būti dėlto, jog žaidimą žaidžiate per dvi naršykles)");
	}

	public function __call($methodName, $args) {
		$this->getResponse()->appendBody("Tokia užklausa negalima");
	}

	public function postDispatch() {
		if (!$this->loged)
			throw new Exception("Čia leidžiama būti tik prisijungusiems", ERROR);

		$response=$this->getResponse();
		$content=$response->getBody();
		$response->clearBody();

		//siunciam info
		if (@$_SESSION['last_tip']+30<time()) {
			$itemssend=new Itemssend();
			$data=$itemssend->getToItems($this->user->getid());

			if ($data->count()>0) {
				$users=new Users();
				$i=mt_rand(0,$data->count()-1);
				$row=$data->getRow(0);
				$item=new Item($row->item);
				$this->sendinfo("{$users->getname($row->from)} siųlo įsigyti {$item->itemLink()} už {$row->cost}. <a onclick=\"show_pmarket({$row->id});\">Norint atverti dialogą spausti čia</a>");
			} else {
				$polls=new Polls();

				$poll=$polls->getlast($this->user->getid());

				if ($poll) {
					$info=array();
					$info[]=$poll->title;

					$a=array();
					foreach ($poll->items as $v) {
						$a[]="<span style=\"white-space: nowrap; margin-right:20px;\"><input name=\"poll-itemas\" type=\"radio\" id=\"poll-item-{$v->id}\" class=\"poll-item\">{$v->title}</span>";
					}
					$info[]=implode(" ",$a);
					$info[]="<input type=\"button\" value=\"Balsuoti\" onclick=\"vote();\" />";
					$this->sendinfo(implode("<br />",$info));
				} elseif (rand(0,3)==0) {
					$this->sendinfo("Karšta naujiena: ".addslashes(Zend_Registry::get('config')->topic));
				} else {
					$TIPS=include('app/data/tips_DB.php');
					$i=rand(0,count($TIPS)-1);
					$this->sendinfo($TIPS[$i]);
				}
			}
			$_SESSION['last_tip']=time();
		}

		$data=new stdClass();

		$data->content=$content;
		$data->time=time();
		$data->reg_time=$this->time;
		$data->unrun=$this->unrun;
		$data->info=$this->info;
		$data->TRACK_NUM=$this->TRACK_NUM;

		$data->user=new stdClass();
		$data->user->lvl=$this->user->getlvl();
		$data->user->money=$this->user->getmoney();
		$data->user->need_exp=$this->user->needexp();
		$data->user->have_exp=$this->user->haveexp();
		$data->user->hp=$this->user->gethp();
		$data->user->maxhp=$this->user->maxhp();
		$data->user->nano=$this->user->getnano();
		
		$data->user->work = $this->user->get_max_hunt();

		$pm=new PMs();
		$data->pm=$pm->count_unread($this->user->getid());

		$data->event=($this->user->read_last_event())? 1 : 0;

		$script=Zend_Registry::get('config')->script;
		$css=Zend_Registry::get('config')->css;

		$data->version=$script+$css;

		//tvarkom runa
		if (Zend_Registry::isRegistered('SEND_RUN')) {
			$run=array_merge($this->run,Zend_Registry::get('SEND_RUN'));
		} else {
			$run=$this->run;
		}
		$data->run=join(';',$run);

		$response->appendBody(json_encode($data));
	}
}
?>