<?php
class Mission_Abstract extends Zend_Db_Table_Row {
	private $__data;
    public $__type = 0;
	/**
	* useris
	*
	* @var User
	*/
	protected $user;


	public function init() {
		if ($this->id) {
			$this->__data = unserialize($this->data);

            if ($this->uid) {
			    $users = new Users();
			    $this->user = $users->getuser($this->uid,false, true);

			    if ($this->end < time())
				    $this->finish();
            }
		} else {
            $this->__data = array();
			$this->make();
		}
	}
    
    public function left() {
        return $this->end - time();
    }

	protected function getData($name) {
		return @$this->__data[$name];
	}

	protected function setData($name, $value) {
		$this->__data[$name] = $value;
	}

	public function prepare() {
		//tikrinam
		if (!$this->type) {
			throw new Exception("Nėra nustatytas tipas", ERROR);
		} elseif (!$this->id) {
			throw new Exception("Nėra užsaugotas", ERROR);
		} elseif (!$this->duration) {
			throw new Exception("Nėra nustatyto laiko", ERROR);
		} elseif (!$this->title) {
			throw new Exception("Nėra nustatyto pavadinimo", ERROR);
		} elseif (!$this->uid) {
			throw new Exception("Nėra vartotojo", ERROR);
		}

		$this->start = time();
		$this->end = time() + $this->duration;

		$users = new Users();
		$this->user = $users->getuser($this->uid,false, true);
    }
    
	public function start() {
		$this->prepare();
        
		$this->_start();
		$this->save();
	}

	public function finish() {
		$this->_finish();
		$this->delete();
	}

	public function halt() {
		$this->_halt();
		$this->delete();
	}

	protected function worked() {
		return time() - $this->start;
	}

	public function _start() {
		$this->user->sendevent("Pradėjote {$this->__get('title')} misiją", EVENT_OTHER);
	}
	public function _finish() {
		$this->user->sendevent("Užbaigėte {$this->title} misiją ir uždirbote {$this->money}", EVENT_OTHER);
		$this->user->addmoney($this->money);
	}
	public function _halt() {
		$this->user->sendevent("Deja {$this->title} misija žlugo", EVENT_OTHER);
	}

	public function info() {
        return false;
	}
    /**
    * kontrole
    * 
    * @param Str $command
    * @param Zend_Controller_Action $controller
    */
	public function control($command, $controller) {

	}

	public function save() {
		$this->data = serialize($this->__data);
		parent::save();
	}

	protected function getAvgLvl($race = RACE_ALL) {
		$users = new Users();
		return $users->getAvgLvl($race);
	}

	protected function getRandomLvl($race = RACE_ALL, $minLvl = null) {
		$users = new Users();
		return $users->getRandomLvl($race, $minLvl);
	}

	protected function countMoney($time, $nominal, $lvlDif) {
		$k = $time / 3600;

		return round(getexp($lvlDif, $nominal) * $k);
	}

	public function make() {

	}
}
?>
