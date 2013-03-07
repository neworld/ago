<?php
class IndexController extends Zend_Controller_Action {
	private $ctitle;
	private $error;

	//pradzia
	public function init() {
		//isjungiam automatini renderinima:
		$this->_helper->viewRenderer->setNoRender(true);
		$this->view->addHelperPath(MY_HELPER_PATH,'Main');
	}

	//pirmas puslapis
	public function indexAction() {
		$this->ctitle='Sveiki atvykę į agonija.eu';
		switch ($this->_getParam('error')) {
			case 1 : $this->error="Neteisingas slaptažodis arba tokio vartoto nėra";break;
			case 2 : $this->error="Šitas vartotojas yra išbanintas";break;
		}

		$this->render('first');
	}

	public function aboutAction() {
		$this->ctitle='Apie žaidimą';
		$this->render();
	}

	public function screenAction() {
		$this->ctitle='Žaidimo vaizdai';
		$this->render();
	}

	public function valAction() {
		$this->ctitle='Pašto adreso patvirtintas';
		$key=$this->_getParam('key');
		$uid=$this->_getParam('uid');
		Zend_Loader::loadClass('Users');
		$users=new Users();
		if ($users->val($uid,$key)) {
			$this->view->good="Pašto adresas sėkmingai patvirtintas";
		} else {
			$this->view->bad="Pašto adreso nepavyko patvirtinti";
		}
		$this->render();
	}
	
	public function rulesAction() {
		$this->render();
	}

	public function regAction() {
		if (!Zend_Registry::get('config')->registration) {
			$this->render('no-reg');
			return 0;
		}
		
		$this->ctitle='Registracija';

		if ($this->_getParam('submit')) {
			//tikrinam
			$gerai=true;
			//tikrinam varda
			$name=$this->_getParam('name');
			$this->view->name=$name;
			if (!preg_match(PATTER_NAME,$name)) {
				$this->view->error_name="Netinkamas formatas ar simboliai";
				$gerai=false;
			}
			Zend_Loader::loadClass('Users');
			$users=new Users();
			if ($users->exsist($name)) {
				$this->view->error_name="Toks vartotojas jau egzistuoja";
				$gerai=false;
			}
			//tikrinam pasworda
			$psw1=$this->_getParam('psw1');
			$psw2=$this->_getParam('psw2');
			if (!preg_match(PATTER_PSW,$psw2)) {
				$this->view->error_psw="Netinkamas slaptažodis";
				$gerai=false;
			}
			if ($psw1!=$psw2) {
				$this->view->error_psw="Nesutampa slaptažodžiai";
				$gerai=false;
			}
			if (!$this->view->error_psw) {
				$this->view->psw=$psw1;
			}
			//tikrinam emeila
			$mail=$this->_getParam('mail');
			$this->view->mail=$mail;
			if (!preg_match(PATTER_MAIL,$mail) || preg_match(PATTER_NOMAIL,$mail)) {
				$this->view->error_mail="Netinkamas pašto formatas";
				$gerai=false;
			}
			//tikrinam ar toks vartotojas egzistuoja
			if (!$users->checkmail($mail)) {
				$this->view->error_mail = 'Toks pašto adresas jau naudojamas';
				$gerai = false;
			}
			//tikrinam rase
			$race=$this->_getParam('race');
			$this->view->race=$race;
			if (!in_array($race,array(RACE_RJAL,RACE_MAN))) {
				$this->view->error_race="Nepasirinkote rasės";
				$gerai=false;
			}
			//tikrinam terms
			$terms=$this->_getParam('terms');
			$this->view->terms=$terms;
			if ($terms!='yes') {
				$this->view->error_terms="Turite sutikti su sąlygomis";
				$gerai=false;
			}
			//tikrinam capcha
			$capcha=$this->_getParam('capcha');
			if ($_SESSION['capcha']!=$capcha) {
				$this->view->error_capcha="Blogai suvedėte skaičius. Bandykite dar kartą.";
				$gerai=false;
			} else {
				$_SESSION['set_capcha']=$capcha;
				$this->view->capcha=$capcha;;
			}
			if (!$gerai) {
				$this->view->error="Registracija nepavyko. Žemiau rasite klaidų aprašymus";
				$this->render();
				return false;
			}

			$newid=$users->create($name,$psw2,$mail,$race);
			Zend_Loader::loadClass('Items');
			$items=new Items();
			$items->from_template($newid,null,1);
			if ($race==RACE_MAN) {
				$items->from_template($newid,null,2);
			} else {
				$items->from_template($newid,null,3);
			}
			$this->_redirect('/index/reg2');
		}
		$this->render();
	}

	public function reg2Action() {
		$this->render();
	}

	public function newsAction() {
		Zend_Loader::loadClass('BBcode');
		Zend_Loader::loadClass('Smilein');
		Zend_Loader::loadClass('ActivateUrl');
		$filter = new Zend_Filter();
		$filter->addFilter(new BBcode());
		$filter->addFilter(new Smilein());
		$filter->addFilter(new ActivateUrl());
		$view=Zend_Registry::get('db')->fetchAll('SELECT * FROM `news` ORDER BY `id` DESC LIMIT 5');
		foreach ($view as &$v) {
			$v['text']=$filter->filter($v['text']);
		}
		$this->view->news=$view;
		$this->render();
		$this->ctitle='Naujienos';
	}

	public function lostpswAction() {
		$this->ctitle='Slaptažodžio atkūrimas';
		$mail=$this->_getParam('mail');
		if ($mail) {
			$users=new Users();

			if ($users->recovery_psw($mail)) {
				$this->view->good="Laiškas su atkūrimo raktu jums nusiųstas į pašto dėžutę. Prašome sekti tolesnius laiško nurodymus";
			} else {
				$this->view->bad="Nėra žaidėjo su tokiu pašto adresu, arba jūs nepatvirtinote pašto adreso";
			}
		}

		$this->render();
	}

	public function recpswAction() {
		$this->ctitle='Slaptažodžio atkūrimas';

		$key=$this->_getParam('key');
		$uid=$this->_getParam('uid');

		$newpsw=$this->_getParam('newpsw');

		if ($key && $uid) {
			if (strlen($newpsw)>=3) {
				$users=new Users();
				$select=$users->select()->where('id = ?',$uid)
										->where('recpsw = ?',$key);

				$data=$users->fetchAll($select);
				if ($data->count()==1) {
					if ($users->setpsw($uid,$newpsw)) {
						$this->view->good="Slaptažodis pakeistas sėkmingai. Gero žaidimo";
					} else {
						$this->view->bad="Deja, slaptažodžio atkurti nepavyko. Praneškite administracijai";
					}
				} else {
					$this->view->bad="Blogas raktas. Tai galėjo atsitikti, jeigu bandėte atkurti slapžtažodį ne su paskutiniu gautu raktu";
				}
			}
		} else {
			$this->view->bad="Blogas raktas";
		}
		$this->render();
	}


	//visa kita nukeliam i pirma
	public function __call($methodName, $args) {
		$this->ctitle='Klaida';
		$this->getResponse()->appendBody('Toks puslapis neegzistuoja');
	}
	public function postDispatch() {
		//darom menu
		include('lib/menu.php');
		$menu=array();
		$menu[]=new Tmenu('Pagrindinis','/');
		$menu[]=new Tmenu('Naujienos','/index/news');
		$menu[]=new Tmenu('Registruotis','/index/reg');
		$menu[]=new Tmenu('Apie','/index/about');
		$menu[]=new Tmenu('Žaidimo vaizdai','/index/screen');
		//$menu[]=new Tmenu('Taisyklės','/index/rules');

		$this->view->menu=$menu;

		//turinys
		$response=$this->getResponse();

		$this->view->ctitle=$this->ctitle;
		$this->view->error=$this->error;
		$this->view->contentas=$response->getBody();
		$this->view->title=Zend_Registry::get('config')->title;
		$this->view->des=Zend_Registry::get('config')->des;

		$response->clearBody();
		//statom style sheetus
		$script=Zend_Registry::get('config')->script;
		$css=Zend_Registry::get('config')->css;

		$this->view->headLink()->appendStylesheet('/css/screen.css?'.$css)
				   ->headLink(array('rel' => 'favicon', 'href' => '/favicon.ico'), 'PREPEND')
				   ->headLink()->appendStylesheet('/css/dialog.css?1');

		//kita info
		$this->view->headMeta()->appendName('keywords', 'Agonija online RPG žaidimas');
		$this->view->headMeta()->appendHttpEquiv('pragma', 'no-cache')
							   ->appendHttpEquiv('Cache-Control', 'no-cache')
							   ->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');

		//scriptai
		$this->view->headScript()->appendFile('/lib/prototype.js?'.$script);
		$this->view->headScript()->appendFile('/lib/jquery.js?'.$script);
		$this->view->headScript()->appendFile('/lib/scriptaculous.js?'.$script);
		$this->view->headScript()->appendFile('/lib/overdiv.js?'.$script);
		$this->view->headScript()->appendFile('/script.js?'.$script);
		$this->view->headScript()->appendFile('/lib/dialog.js?'.$script);

		//skaiciuojam userius
		$users=new Users();
		$this->view->num_man=$users->countuser(RACE_MAN);
		$this->view->num_rjal=$users->countuser(RACE_RJAL);
		$this->view->kill_man=$users->count_kill(RACE_MAN);
		$this->view->kill_rjal=$users->count_kill(RACE_RJAL);

		Zend_Loader::loadClass('Online');
		$online=new Online;
		$this->view->num_online=$online->getonline();

		$this->render('index');
	}
}
?>