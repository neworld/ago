<?
class Fighter extends Zend_Db_Table_Row {
	const TIME_RELOAD=50;
	const TIME_MOVE=70;

	//kesavimo sumetimais
	private $name;

	private function isFighting() {
		$atack=new Atack();
		return $atack->isFight($this->aid);
	}
	/**
	* logas
	*
	* @var atacklogs
	*/
	private $atacklogs;

	private function loadLogs() {
		$this->atacklogs = new Atacklogs();
	}

	public function addLog($message) {
		if (!$this->atacklogs)
			$this->loadLogs();

		return $this->atacklogs->add($this->aid, $message);
	}

	private function getName() {
		if (!$this->name) {
			$users = new Users();
			$this->name = $users->getname($this->uid);
		}

		return $this->name;
	}

	public function compositeLog($message) {
		$this->addLog("{$this->getName()} $message");
	}

	public function getUser($readonly=false) {
        $users = new Users();
        return $users->getuser($this->uid, $readonly, true);
	}
	public function setFinish() {
		if (!$this->isFighting())
			throw new Exception("Dar kova neprasidėjo", ERROR);

		$this->finish="Y";
		$this->save();
	}

	private function convert($x, $y) {
		$x1 = $y - floor(($x-1) / 2);
		$y1 = $x;
		$z1 = $y - 1 + floor($x/2);
		return array($x1, $y1, $z1);
	}

	public function atstumas($x1, $y1, $x2, $y2) {
		list($x, $y, $z) = $this->convert($x1, $y1);
		list($xe, $ye, $ze) = $this->convert($x2, $y2);

		return max(abs($x - $xe), abs($y - $ye), abs($z - $ze));
	}

	private function checkMove($x, $y) {
		if ($x<=0 || $y<=0)
			throw new Exception("Išeinate už žemėlapio ribų", ERROR);

		if ($this->x%2==0) {
			$difs=array(
				array("x" => 0, "y" => -1),
				array("x" => 1, "y" => 0),
				array("x" => 1, "y" => 1),
				array("x" => 0, "y" => 1),
				array("x" => -1, "y" => 1),
				array("x" => -1, "y" => 0)
			);
		} else {
			$difs=array(
				array("x" => 0, "y" => -1),
				array("x" => 1, "y" => -1),
				array("x" => 1, "y" => 0),
				array("x" => 0, "y" => 1),
				array("x" => -1, "y" => 0),
				array("x" => -1, "y" => -1)
			);
		}

		$can=false;
		foreach ($difs as $v) {
			if ($this->x+$v['x']==$x && $this->y+$v['y']==$y) {
				$can=true;
				break;
			}
		}

		if (!$can)
			throw new Exception("Per toli einate", ERROR);

		$atack=new Atack();
		$row=$atack->find($this->aid)->getRow(0);

		if ($x>$row->width || $y>$row->height)
			throw new Exception("Išeinate už žemėlapio ribų", ERROR);

		$objects=new Objects();
		if ($objects->getByPosition($x, $y, $this->aid))
			throw new Exception("Kelyje yra objektas", ERROR);

		$fighters=new Fighters();
		if ($fighters->getByPosition($x, $y, $this->aid))
			throw new Exception("Kelyje yra kitas kovotojas", ERROR);

		return true;
	}


	public function move($x,$y) {
		if (!$this->isFighting())
			throw new Exception("Dar kova neprasidėjo", ERROR);

		if (!$this->checkMove($x, $y))
			throw new Exception ("Čia negalima judeti", ERROR);

		$user=$this->getUser();
		$speed=$user->get_skill_sum("SPEED");

		$time=Fighter::TIME_MOVE-floor($speed/10);

		if ($this->time_left<$time)
			throw new Exception ("Neturite laiko ėjimui" , ERROR);

		$this->compositeLog("pajudėjo ({$this->x}; {$this->y}) -> ($x; $y)");

		$this->x=$x;
		$this->y=$y;
		$this->time_left-=$time;
		$this->save();
	}

	public function realod() {
		$needTime=$this->count_reload();
		if ($this->time_left>=$needTime) {
			$this->time_left-=$needTime;
			$this->shot_left1=130;
			$this->shot_left2=130;
			$this->compositeLog("perkrovė amuniciją");
			$this->save();
		} else {
			throw new Exception("Neturite laiko ėjimui. Jums reikia $needTime", ERROR);
		}
	}

	private function count_reload() {
		$user = $this->getUser(true);
		$mainGun = $user->getitembyplace("GUN_MAIN");
		$secondGun = $user->getitembyplace("GUN_SECOND");

		$needTime1 = 0;
		$needTime2 = 0;

		if ($mainGun && $mainGun->guntype!=GUNTYPE_MELLE) {
			$needTime1 = $mainGun->plus['SPEED_RELOAD'] * (1 - 0.01*sqrt($user->get_skill_sum('')));
		}

		if ($secondGun && $secondGun->guntype!=GUNTYPE_MELLE) {
			$needTime2 = $secondGun->plus['SPEED_RELOAD'] * (1 - 0.01*sqrt($user->get_skill_sum('')));
		}

		return round(max($needTime1, $needTime2));
	}

	public function atack($x, $y) {
		$fighters=new Fighters();

		$def=$fighters->getByPosition($x, $y,$this->aid);

		if (!$def)
			throw new Exception("Tai ne žaidėjas", ERROR);

		if ($def->side==$this->side)
			throw new Exception("Tai jūsų komandos žaidėjas", ERROR);

		$user=$this->getUser(true);

		$mainGun = $user->getitembyplace("GUN_MAIN");
		$secondGun = $user->getitembyplace("GUN_SECOND");

		if (!($mainGun || $secondGun))
			throw new Exception("Neturite ginklų", ERROR);

		$atstumas=$this->atstumas($this->x, $this->y, $x, $y);

		$ranged1 = ($mainGun && $mainGun->guntype != GUNTYPE_MELLE);
		$ranged2 = ($secondGun && $secondGun->guntype != GUNTYPE_MELLE);

		if ($atstumas > 5 && ($ranged1 || $ranged2)) {
			throw new Exception("Esate per toli", ERROR);
		} elseif ($atstumas > 1 && !($ranged1 || $ranged2)) {
			throw new Exception("Turite būti šalia varžovo, jeigu kovojate šaltaisiais ginklais", ERROR);
		}

		$needTime1 = 0;
		$needTime2 = 0;

		if ($mainGun) {
			if ($ranged1 && $mainGun) {
				$shotLeft1 = $this->shot_left1;
			} elseif (!$ranged1 && $atstumas == 1) {
				$shotLeft1 = 300;
			} else {
				$shotLeft1 = 0;
			}

			$duraton1 = $user->gunDuration(1);
			$shotLeft1 = min($shotLeft1, round($this->time_left/$duraton1));

			$needTime1 = $shotLeft1 * $duraton1;

			if ($ranged1)
				$this->shot_left1 -= $shotLeft1;
		}

		if ($secondGun) {
			if ($ranged2 && $secondGun) {
				$shotLeft2 = $this->shot_left1;
			} elseif (!$ranged2 && $atstumas == 1) {
				$shotLeft2 = 300;
			} else {
				$shotLeft2 = 0;
			}

			$duraton2 = $user->gunDuration(2);
			$shotLeft2 = min($shotLeft2, round($this->time_left/$duraton2));

			$needTime2 = $shotLeft2 * $duraton2;
			if ($ranged2)
				$this->shot_left2 -= $shotLeft2;
		}

		$this->time_left -= round(max($needTime1, $needTime2));

		if (($mainGun && $shotLeft1>0) || ($secondGun  && $shotLeft2>0)) {
			$udef=$def->getUser();

			$taiklumas = 60 + ($user->getlvl() - $udef->getlvl())*0.05 - ($atstumas - 1) * 2;

			$DEF_D_RANGED=$udef->get_skill_sum("DEXTERITY");
			$DEF_D_MELLE=$udef->get_skill_sum("MEELE_DEFENCE");

			$zala = 0;
			$pataike = 0;
			$prasove = 0;

			for ($i=1; $i<=2; $i++) {
				if ($i==1) {
					$gunas = $mainGun;
					if ($gunas) {
						$shotLeft = $shotLeft1;
						$ranged = $ranged1;
					}
				} else {
					$gunas = $secondGun;
					if ($gunas) {
						$shotLeft = $shotLeft2;
						$ranged = $ranged2;
					}
				}

				if ($gunas && $shotLeft>0) {
					$defas=($ranged)? $DEF_D_RANGED : $DEF_D_MELLE;

					$DEF=new Def($DEF_D_RANGED, $DEF_D_MELLE);

					$minus = 0;

					switch ($gunas->guntype) {
						case GUNTYPE_MELLE : $minus = $user->getskillsum('HEAVY_GUN');
						case GUNTYPE_LIGHT : $minus = $user->getskillsum('LIGHT_GUN');
						case GUNTYPE_HEAVY : $minus = $user->getskillsum('MEELE_GUN');
					}

					$dmg = $user->getDMG($i, $DEF);
					$gunTaiklumas = ($gunas->guntype == GUNTYPE_MELLE)? $taiklumas + 5 : $taiklumas + $user->getskillsum('RANGED')*0.01;
					$gunTaiklumas -= $defas*0.04;

					$gunTaiklumas += $minus*0.04;

					for ($x=1; $x<=$shotLeft; $x++) {
						$k=(rand(0, 1000) <= $gunTaiklumas*10)? 1 : 0;

						switch ($k) {
							case 0 : $prasove++; break;
							case 1 : $pataike++; break;
						}

						$hp=$k*($dmg->average() * (rand(80, 100)/100) / (1+0.025*$udef->get_skill_plus("ARMOR")));

						$udef->addHP(0 - $hp);

						$zala += $hp;
					}
				}
			}

			$zala = round($zala, 2);

			$this->compositeLog("pataikė į {$udef->getname()} {$pataike} kartus iš " . ($prasove + $pataike) . " ir padarė $zala žalos. Taiklumas $gunTaiklumas%. Atstumas: $atstumas");
			$this->point += round($pataike / ($prasove + $pataike) * 10);

			if ($udef->gethp()<1) {
				$this->point += 100;
				$def->compositeLog("žuvo nuo {$user->getname()}");
				$def->dead='Y';
				$def->save();
			}
		} else {
			throw new Exception('Neturite ginklų, arba baigėsi kulkos', ERROR);
		}
		$this->save();
	}

	public function getAtack() {
		$atack=new Atack();

		return $atack->find($this->aid)->getRow(0);
	}

}