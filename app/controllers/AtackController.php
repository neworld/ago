<?php
class AtackController extends Zend_Controller_Action {
	private $user;
	private $atack;
	private $aid;
	private $atacks;
	private $fighters;
	private $obejcts;
	private $obejctai;
	private $fighteriai;
	private $atacklogs;
	/**
	* mano fighteris
	*
	* @var Fighter
	*/
	private $myFighter;

	public function init() {
		//isjungiam automatini renderinima:
		$this->_helper->viewRenderer->setNoRender(true);

		if (!$this->user=$this->get_user())
			throw new Exception("Turite būtinai prisijungti", ERROR);

		if (!$this->aid=$this->user->getCurrentFight())
			throw new Exception("Kova baigta", ERROR);

		$this->atacks = new Atack();
		$this->fighters = new Fighters();
		$this->objects = new Objects();
		$this->atacklogs = new Atacklogs();

		$this->atack = $this->atacks->find($this->aid)->getRow(0);
		$this->fighteriai = $this->fighters->getFighters($this->aid);
		$this->obejctai = $this->objects->getFieldByMap($this->aid);
		$this->myFighter = $this->fighters->getFighter($this->aid, $this->user->getid());
	}

	private function get_user() {
		Zend_Loader::loadClass('Session');
		$session=new Session();
		if ($session->get()) {
			Zend_Loader::loadClass('Users');
			return new User($session->get(), true);
		} else {
			return false;
		}
	}

	public function indexAction() {
		$data=new stdClass();

		$data->time_left = max($this->atack->turn_end - time(), 0);

		$type = $this->_getParam('type');
		$x = $this->_getParam('x');
		$y = $this->_getParam('y');

		try {
			if ($this->atack->side != $this->myFighter->side && $type!=0)
				throw new Exception("Tai ne jūsų ėjimas", ERROR);

			if ($data->time_left==0 && $type!=0)
				throw new Exception("Laikas baigėsi", ERROR);

			if ($this->myFighter->dead == 'Y')
				throw new Exception("Jūs esate miręs", ERROR);

			switch ($type) {
				case '1' : $this->myFighter->move($x, $y); break;
				case '2' : $this->myFighter->realod(); break;
				case '3' : $this->myFighter->atack($x, $y); break;
			}
		} catch (Exception $e) {
			$error = $e->getMessage();
		}

		if ($type!=0)
			$this->fighteriai = $this->fighters->getFighters($this->aid);

		$data->fighters = array();
		$data->objects = array();

		foreach ($this->fighteriai as $v) {
			$data->fighters[] = new Afighter($v);

			if ($v->uid == $this->user->getid()) {
				$data->left=$v->time_left;
				$gun1=$this->user->getitembyplace('GUN_MAIN');
				$gun2=$this->user->getitembyplace('GUN_SECOND');
				if ($gun1 && $gun1->guntype!=GUNTYPE_MELLE) {
					$data->shot_left1=$v->shot_left1;
				} else {
					$data->shot_left1='--';
				}
				if ($gun2 && $gun2->guntype!=GUNTYPE_MELLE) {
					$data->shot_left2=$v->shot_left2;
				} else {
					$data->shot_left2='--';
				}
			}
		}

		$data->turn = $this->atack->turns;
		$data->time_left = max($this->atack->turn_end - time(), 0);
		$data->side = $this->atack->side;

		//generuojam loga

		$data->logs = array();

		$since = $this->_getParam('since', 0);
		$logas = new Atacklogs();

		$logai = $logas->getSince($this->atack->id, $since);

		$data->last = 0;

		foreach ($logai as $v) {
			$ob = new stdClass();

			$ob->id = $v->id;
			$ob->date = $v->date;
			$ob->text = $v->text;

			$data->logs[] = $ob;

			unset($ob);

			$data->last = $v->id;
		}

		if (isset($error)) {
			$ob = new stdClass();
			$ob->id = 0;
			$ob->date = date(DATE_FORMAT2);
			$ob->text = "<span style=\"color:red\">$error</span>";

			$data->logs[] = $ob;

			unset($ob);
		}

		foreach ($this->obejctai as $v) {
			$data->objects[] = new Aobject($v);
		}

/*		$atackInfo = new stdClass();
		$atackInfo->width = $this->atack->width;
		$atackInfo->height = $this->atack->height;
		$atackInfo->id = $this->atack->id;

		apc_store("atackInfo_{$this->user->getid()}_{$this->atack->id}", array(
			'fighters' => $data->fighters,
			'objects' => $data->objects,
			'info' => $atackInfo
		), 120);*/

		$this->getResponse()->appendBody(json_encode($data));
	}
}