<?php
class MainController extends Zend_Controller_Action {
	public function init() {
		$this->view->addHelperPath(MY_HELPER_PATH,'Main');
	}
	function indexAction() {
		Zend_Loader::loadClass('Session');
		$session=new Session();
		if (!$session->isloged()) {
			$this->_redirect('/index/index');
			return false;
		}
		$userid=$session->get();

		$script=Zend_Registry::get('config')->script;
		$css=Zend_Registry::get('config')->css;

		$this->view->headLink()->appendStylesheet('/css/screen.css?'.$css)
				   ->headLink()->appendStylesheet('/css/protoload.css?'.$css)
				   ->headLink()->appendStylesheet('/css/dialog.css?1')
				   ->headLink()->appendStylesheet('/css/dark-hive/jquery-ui.min.css?'.$css)
				   ->headLink()->appendStylesheet('/css/dark-hive/theme.css?'.$css)
				   ->headLink(array('rel' => 'favicon', 'href' => '/favicon.ico'), 'PREPEND');

		//kita info
		$this->view->headMeta()->appendName('keywords', 'Agonija online RPG žaidimas');
		$this->view->headMeta()->appendHttpEquiv('pragma', 'no-cache')
							   ->appendHttpEquiv('Cache-Control', 'no-cache')
							   ->appendHttpEquiv('Content-Type', 'text/html; charset=UTF-8');

		//scriptai
		$this->view->headScript()->appendFile('/lib/prototype.js?'.$script);
		$this->view->headScript()->appendFile('/lib/jquery.js?'.$script);
		$this->view->headScript()->appendFile('/lib/prototype-base-extensions.js?'.$script);
		$this->view->headScript()->appendFile('/lib/prototype-date-extensions.js?'.$script);
		$this->view->headScript()->appendFile('/lib/scriptaculous.js?'.$script);
		$this->view->headScript()->appendFile('/lib/overdiv.js?'.$script);
		$this->view->headScript()->appendFile('/lib/carousel.js?1'.$script);
		$this->view->headScript()->appendFile('/lib/timer.js?'.$script);
		$this->view->headScript()->appendFile('/lib/protoload.js?'.$script);
		$this->view->headScript()->appendFile('/lib/dialog.js?'.$script);
		$this->view->headScript()->appendFile('/lib/datepicker.js?'.$script);
		$this->view->headScript()->appendFile('/lib/datepicker-locale-lt_LT.js?'.$script);
		$this->view->headScript()->appendFile('/lib/nwBar.js?'.$script);
		$this->view->headScript()->appendFile('/lib/atackGUI.js?'.$script);
		$this->view->headScript()->appendFile('/lib/nwCounter.js?'.$script);
		$this->view->headScript()->appendFile('/lib/nwList.js?'.$script);
		$this->view->headScript()->appendFile('/lib/nwLog.js?'.$script);
		$this->view->headScript()->appendFile('/lib/NWLib_jqeury/NWCheck.js?'.$script);
		$this->view->headScript()->appendFile('/lib/dissableTextSelect.js?'.$script);
		$this->view->headScript()->appendFile('/lib/jquery-ui.js?'.$script);
		//$this->view->headScript()->appendFile('/lib/debug.js?'.$script);
		$this->view->headScript()->appendFile('/script.js?'.$script);

		//gaunam userio duomenis
		Zend_Loader::loadClass('Users');
		$users=new Users();
		$user=$users->getuser($userid,false,true);

		$this->view->user=$user;

		$this->view->ses_key=$users->set_ses_key($userid);
		$this->view->name=$user->getname();
		$this->view->lvl=$user->getlvl();
		$this->view->needexp=$user->needexp();
		$this->view->haveexp=$user->haveexp();
		$this->view->race=$user->getrace();
		$this->view->money=$user->getmoney();
		$this->view->maxhp=$user->maxhp();
		$this->view->hp=$user->gethp();
		$this->view->nano=$user->getnano();
		$_SESSION['last_tip']=time();
		$_SESSION['DEMO']=$user->demo;
		$_SESSION['drink']=$user->getdrink();
        $_SESSION['sid'] = getsid();

		$clans=new Clans();
		$clanmembers=new ClanMembers();

		if ($clanmembers->getClanId($userid)) {
			$_SESSION['atag']=$clans->getClan($clanmembers->getClanId($userid))->tag;
			$_SESSION['alord']=($clans->getClan($clanmembers->getClanId($userid))->leader==$userid)? 1 : 0;
		} else {
			$_SESSION['atag']=null;
			$_SESSION['alord']=null;
		}
	}

	function loginAction() {
		$name=$this->_getParam('name');
		$psw=$this->_getParam('psw');
		Zend_Loader::loadClass('Users');
		$users=new Users();
		if ($users->checkban($users->getid($name))) {
			$this->_redirect('/index/index/error/2');
		} elseif ($users->checkpsw($users->getid($name),$psw)) {
			Zend_Loader::loadClass('Session');
			$session=new Session();
			$session->set($users->getid($name));
			$_SESSION['username']=$name;
			$users->setIP($users->getid($name));

			include('./lib/Browscap.php');
			$a = new Browscap('/tmp');
			$data = $a->getBrowser();
			$history = new LoginHistory();
			$history->add($users->getid($name), getip(), $data->Parent, $data->Platform);

			$this->_redirect('/main/index');
		} else {
			$this->_redirect('/index/index/error/1');
		}
		return false;
	}

	function logoutAction() {
		Zend_Loader::loadClass('Session');
		$session=new Session();
		$session->unlog();
		$this->_redirect('/index/index');
		return false;
	}

	public function __call($methodName, $args) {
		$this->_redirect('/index/index');
	}
}
?>
