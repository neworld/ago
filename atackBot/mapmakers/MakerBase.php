<?php
class MakerBase {
	public $atacks;
	public $atack;
	public $fighters;
	public $objects;
	public $fighters_data;

	public $mapas;
	public $map;

	public $width;
	public $height;


	public function __construct() {
		$this->atacks=new Atack();
		$this->fighters=new Fighters();
		$this->objects=new Objects();
	}

	public function generate() {
		//sita funkcija turi buti perrasoma
		return false;
	}

	public function free($x,$y) {
		return ($this->mapas[$x][$y]==0);
	}

	public function generate_freemap($width, $height) {
		for ($x=1; $x<=$width; $x++)
			for ($y=1; $y<=$height; $y++)
				$this->mapas[$x][$y]=0;

		$this->width=$width;
		$this->height=$height;
	}

	public function set($x, $y, $type) {
		if ($this->width<$x || $this->height<$y)
			return false;

		$this->mapas[$x][$y]=$type;
	}

	public function check() {
		$registered=array();
		for ($x=1; $x<=$this->width; $x++)
			for ($y=1; $y<=$this->height; $y++)
				if ($this->mapas[$x][$y]>0)
					$registered[]=$this->mapas[$x][$y];

		if (count($registered)==0)
			return false;

		if (count($registered)!=count(array_unique($registered, SORT_NUMERIC)))
			return false;

		//if (count($registered)!=count($this->fighters_data))
		//	return false;

		foreach ($this->fighters_data as $v) {
			$uid=$v->id;

			$key=array_search($uid, $registered);

			if (is_numeric($key))
				unset($registered[$key]);
		}



		if (count($registered)!=0)
			return false;

		return true;
	}

	public function save() {
		for ($x=1; $x<=$this->width; $x++)
			for ($y=1; $y<=$this->height; $y++) {
				if ($this->mapas[$x][$y]>0) {
					$fighteris=$this->fighters->find($this->mapas[$x][$y])->getRow(0);
					$fighteris->x=$x;
					$fighteris->y=$y;
					$fighteris->save();
				} elseif ($this->mapas[$x][$y]<0) {
					$tipas=0-$mapas[$x][$y];
					$this->objects->addField($x, $y, $tipas, $this->atack->id);
				}
			}

		return true;
	}

	public function make_map($atack) {
		$this->atack=$atack;
		$this->fighters_data=$this->fighters->getFighters($atack->id);
		$this->generate_freemap($atack->width, $atack->height);

		if ($this->generate() && $this->check()) {
			return $this->save();
		} else {
			return false;
		}
	}

	public function fight($atack) {
		$this->atack=$atack;
		$this->fighters_data=$this->fighters->getFighters($atack->id);

		$this->map=array();
		for ($x=1; $x<=$atack->width; $x++)
			for ($y=1; $y<=$atack->height; $y++)
				$this->map[$x][$y]=0;

		foreach ($this->fighters_data as $v) {
			$this->map[$v->x][$v->y]=$v;
		}

		$objektai=$this->objects->getFieldByMap($atack->id);

		foreach ($objektai as $v) {
			$this->map[$v->x][$v->y]=0-($v->type);
		}

		$finish=$this->check_finish();

		$time=date(DATE_FORMAT2);

		if ($finish) {
			$this->finish($finish);
			return true;
		} elseif ($atack->end>$time) {
			$this->next_turn();
			return false;
		} else {
			$win=$this->check_terminate();

			$this->finish($win);
			return true;
		}
	}

	public function next_turn() {
		switch ($this->atack->side) {
			case 'R' : $this->atack->side='L'; break;
			case 'L' : $this->atack->side='R'; break;
		}

		$this->atack->turns++;
		$this->atack->turn_end=time()+30;

		$this->atack->save();

		foreach ($this->fighters_data as $v) {
			if ($v->side==$this->atack->side) {
				$v->time_left=min($v->time_left+100, 150);
				$v->save();
			}
		}
	}

	public function check_terminate() {
		$right=0;
		$left=0;

		foreach ($this->fighters_data as $v) {
			switch ($v->side) {
				case 'L' : $left+=$v->point;break;
				case 'R' : $right+=$v->point; break;
			}
		}

		if ($left>$right) {
			return 'L';
		} else {
			return 'R';
		}
	}

	public function finish($win) {
		foreach ($this->fighters_data as $v) {
			$user=$v->getUser();
			$exp=$this->count_exp($v, $user);
			if ($v->side==$win) {
				$user->addexp($exp);
				$user->sendevent("Jūs laimėjote {$this->atack->title}. Surinkote {$v->point} taškų ir gavote $exp patirties", EVENT_OTHER);
			} else {
				$exp=round($exp/10);
				$user->addexp($exp);
				$user->sendevent("Jūs pralaimėjote {$this->atack->title}. Surinkote {$v->point} taškų ir gavote $exp patirties", EVENT_OTHER);
			}
			$user->save();
			unset($user);
			$v->delete();
		}

		$this->atack->delete();
	}

	public function count_exp($fighter, $user) {
		$start_stamp=strtotime($this->atack->start);
		$end_stamp=strtotime($this->atack->end);

		$dif=ceil(($end_stamp-$start_stamp)/60);

		$lvl=$user->getlvl();
		$exp=$this->fighters_data->count()*100+$lvl;
		$exp=$exp/60*$dif;

		$maxexp=round($exp*0.7);
		$minexp=round($exp*0.5);

		return rand($minexp, $maxexp);
	}


	public function check_finish() {
		$left=0;
		$right=0;

		foreach ($this->fighters_data as $v) {
			if ($v->dead=='N') {
				switch ($v->side) {
					case 'L' : $left++; break;
					case 'R' : $right++; break;
				}
			}
		}

		if ($left==0) {
			return 'R';
		} elseif ($right==0) {
			return 'L';
		} else {
			return false;
		}
	}
}
?>