<?php
class Mission_Simple extends Mission_Abstract {
    public $__type = 1;
    
	public function setSkillType($skill) {
		return $this->setData("skill", $skill);
	}

	public function make() {
        $this->type = $this->__type;

		$this->duration = 1800 + rand(-400, 400);

		$data = include("app/data/misssionSimple.php");

		$r = rand(1, count($data));

		$m = $data[$r];

		$names = new Names();

		$name1 = $names->generate();
		$name2 = $names->generate();
        $lvl = $this->getRandomLvl(RACE_ALL, $m['minlvl']);

        $this->minlvl = ($lvl <= 31)? 1 : $lvl - 30;
        $this->maxlvl = $this->minlvl + 50;

		$a1 = array("%1", "%2");
		$a2 = array($name1, $name2);

		$this->title = str_replace($a1, $a2, $m['title']);
		$this->des = str_replace($a1, $a2, $m['des']);
        $this->dificulity = min(10, rand(0,2) + $m['dificulity']);

		$this->money = $this->countMoney(1200, $m['money'], $lvl * (0.2 + rand(-50, 50) / 1000));

		$this->setData('mType', $r);
		$this->setData('name1', $name1);
		$this->setData('name2', $name2);
	}

	public function _finish() {
        $data = include("app/data/misssionSimple.php");
        $m = $data[$this->getData('mType')];
        
		$skill = $m['skill'];
        
        if (is_string($skill)) {
            $uSkill = $this->user->get_skill_sum($skill);
        } elseif (is_array($skill)) {
            $uSkill = 0;
            foreach ($skill as $key => $v) {
                $uSkill += $this->user->get_skill_sum($key) * $v;
            }
        } else {
            throw new Exception("Neapibreztas skilas");
        }
        
        $mm = new Minmax($this->minlvl, $this->maxlvl);
        $lvl = $mm->average();
        
		$dif = $this->user->getlvl() - $lvl;

		$skill_require = $lvl * (1 + 0.3 * $this->dificulity + rand(-20, 20) * 0.01);
		
		$exp = $this->countMoney(1000 + 35 * $this->dificulity, $m['exp'], sqrt($mm->average()));
        
		$name1 = $this->getData('name1');
		$name2 = $this->getData('name2');

		if ($uSkill >= $skill_require) {
			$event = $m['win'];
			$money = $this->money;
		} elseif (($uSkill >= $skill_require * rand(500, 999) / 1000) && $m['half']) {
			$event = $m['half'];
			$money = $this->money * 0.3;
			$exp = $exp * 0.6;
		} else {
			$event = $m['no'];
			$exp = $exp * 0.3;
			$money = 0;
		}

        $a1 = array("%1", "%2", "%3", "%4");
        $a2 = array($name1, $name2, $money, $exp);

		$this->user->sendevent(str_replace($a1, $a2, $event));
		$this->user->addmoney($money);
		$this->user->addexp($exp);
        $this->user->save();
	}
}
?>
