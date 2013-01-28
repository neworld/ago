<?
require_once('mapmakers/MakerBase.php');
class Maker_1 extends MakerBase {
	public function generate() {
		$size=0;
		$left=array();
		$right=array();
		$user_aid=0;
		foreach ($this->fighters_data as $v) {
			if ($v->side=='L') {
				$left[]=$v;
			} else {
				$right[]=$v;
			}
		}

		$players=min(count($left),count($right));

		if ($players<=0)
			return false;

		$i=-1;
		for ($x=1; $x<=$players; $x++) {
			$i++;
			if (isset($left[$i]) && isset($right[$i])) {
				$top=floor($this->atack->height/$players*($i+1));
				$this->set(1, $top, $left[$i]->id);
				$this->set($this->atack->width, $top, $right[$i]->id);
			} else {
				if (isset($left[$i])) {
					$useris=$left[$i]->getUser();
					$useris->sendevent("Buvote pašalintas iš {$this->atack->title}", EVENT_OTHER);
					$left[$i]->delete();
				}
				if (isset($right[$i])) {
					$useris=$right[$i]->getUser();
					$useris->sendevent("Buvote pašalintas iš {$this->atack->title}", EVENT_OTHER);
					$right[$i]->delete();
				}
			}
		}

		return true;
	}
}