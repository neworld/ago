<?php
class Users extends Zend_Db_Table {
	protected $_name = 'users';
	protected $_primary = array('id');
	/**
	* @desc sukuriam useri
	* @param string $name vartotjo vardas
	* @param string $psw slaptazodis
	* @param string $mail pasto adresas
	* @param int $race rase
	* $return int naujai sukurto userio id;
	*/
	public function create($name,$psw,$mail,$race) {
		if ((($race==RACE_MAN) || ($race==RACE_RJAL)) && (!$this->exsist($name)) && (validmail($mail))) {
			$val=genkey(16);
			$data=array(
				'name' => $name,
				'psw' => encodepsw($psw),
				'mail' => $mail,
				'race' => $race,
				'regdate' => date(DATE_FORMAT),
				'lasthit' => time(),
				'val' => $val
			);

			$uid=$this->insert($data);

			$Mail = new Zend_Mail("utf-8");
			$Mail->setBodyHtml("Ačiū kad užsiregistravote agonija.eu žaidime<br /><br />
								Jūsų prisijungim duomenys yra:\nVartotojas: $name<br />
								slaptažodis: $psw<br /><br />
								Taip pat jūs turite patvirtint savo pašto adresą paspaude ant šios nuorodos:
								<a href=\"http://".(Zend_Registry::get('config')->webhost)."/index/val/key/$val/uid/$uid\" target=\"_blank\">patvirtinti</a><br /><br />
								Gero žaidimo<br /><br />
								agonija.eu administracija");

			$Mail->setFrom('admin@agonija.eu', 'agonija.eu administracija');
			$Mail->addTo($mail, '');
			$Mail->setSubject('Sėkmingai užsiregistravote agonija.eu');
			$Mail->send();

			return $uid;
		} else {
			return false;
		}
	}

	public function set_ses_key($user) {
		$data=$this->find($user);
		if ($data->count()>0) {
			$key=genkey(32);
			$row=$data->getRow(0);
			$row->ses_key=$key;
			$row->save();
			return $key;
		}
		return false;
	}
	public function check_ses_key($user,$ses_key) {
		$data=$this->find($user);
		if ($data->count()>0) {
			return ($data->getRow(0)->ses_key==$ses_key);
		}
		return false;
	}

	public function get_top($order="exp DESC", $race=RACE_ALL, $num=null,$start=null) {
		$select=$this->select()->order($order)
							   ->setIntegrityCheck(false)
							   ->from(array("t1" => $this->_name),"t1.* , (`kill`+`dead`) AS all")
                               ->where('visible = ?', 1)
							   ->joinLeft(array("t2" => "clan_members"),"t2.user=t1.id",array())
							   ->joinLeft(array("t3" => "clans"), "t3.id=t2.cid", array('tag'))
							   ->joinLeft(array("t4" => "online"), "t4.user=t1.id", array("isonline" => "id"));

		if ($race!=RACE_ALL)
			$select->where('race = ?', $race);

		if ($num)
			$select->limit($num,$start);

//		/echo $select;
		return $this->fetchAll($select);
	}

	public function get_place($user,$type="exp",$race=RACE_ALL) {
		$select=$this->select();

		if ($type=='all') {
			$select->from($this->_name,'(`kill`+`dead`) AS num');
		} else {
			$select->from($this->_name,"$type AS num");
		}

		$select->where('id = ?',$user);

		if ($race!=RACE_ALL)
			$select->where('race = ?',$race);

		$num=$this->fetchRow($select)->num;

		unset($select);
		$select=$this->select()->from($this->_name,"COUNT(*) AS place");
        $select->where('visible = ?', 1);

		if ($type=='all') {
			$select->where("(`kill`+`dead`) > ?", $num);
		} else {
			$select->where("`$type` > ?", $num);
		}

		if ($race!=RACE_ALL)
			$select->where('race = ?',$race);


		$place=$this->fetchRow($select)->place;

		return $place+1;
	}


	public function deleteuser($id) {
		$where = $this->getAdapter()->quoteInto('id = ?', $id);

		return ($this->delete($where)>=1)? true : false;
	}

	public function exsist($nick=null, $id=null, $visible = false) {
		if ($nick) {
			$select=$this->select()->from($this->_name,'COUNT(id) AS num')
												->where('name like ?',$nick);
            
            if ($visible)
                $select->where('visible = ?', 1);
            
            $row = $this->fetchRow($select);
			return ($row->num==1)? true : false;
		}
		if ($id) {
			$a=$this->find($id);
			return ($a->count()==1 && (!$visible || $a->getRow(0)->visible == 1))? true : false;
		}
		return false;
	}

	public function getname($id) {
		$data=$this->find($id);
		return ($data->count()==1)? $data->getRow(0)->name : null;
	}
	/**
	* @desc gaunam vartotojo varda pagal id
	* @param String vartotojo vardas
	* @return Int|false vartotojo id arba false, jeigu tokiu vardu vartotojas neegzistuoja
	*/
	public function getid($name) {
		if (!$name)
			return false;
		$row=$this->fetchRow($this->select()->from($this->_name,'id')
											  ->where("name like ?",$name));
		return (empty($row->id))? false : $row->id;
	}
	/**
	* @desc validanti useri
	* @param integer $id userio id
	* @param string $val validatinimo raktas
	* @return boolean ar suvalidatino
	*/
	public function val($id,$val) {
		$data=$this->find($id);
		$row=$data->getRow(0);
		if ($row->val==$val) {
			$row->val='';
			if ($row->new_mail) {
				$row->mail=$row->new_mail;
				$row->new_mail='';
			}
			$row->save();
			return true;
		}
		return false;
	}

	public function sendval($id) {
		$data=$this->find($id);
		$row=$data->getRow(0);
		if ($row->val) {
			$to=($row->new_mail)? $row->new_mail : $row->mail;
			$val=$row->val;

			$mail = new Zend_Mail("utf-8");
			$mail->setBodyHtml("Jūs paprašėye pakartotinai atsiųsti pašto patvirtinimo raktą<br />Štai jis:
								<a href=\"http://".(Zend_Registry::get('config')->webhost)."/index/val/key/$val/uid/$id\" target=\"_blank\">patvirtinti</a><br /><br />
								Gero žaidimo<br /><br />
								agonija.eu administracija");

			$mail->setFrom('admin@agonija.eu', 'agonija.eu administracija');
			$mail->addTo($to, '');
			$mail->setSubject('Pašto patvirtinimas agonija.eu');
			$mail->send();
			return true;
		} else {
			return false;
		}
	}

	public function setpsw($id,$newpsw) {
		$data=$this->find($id);
		$row=$data->getRow(0);
		$row->psw=encodepsw($newpsw);
		$row->recpsw='';
		$row->save();
		return true;
	}

	public function recovery_psw($mail) {
		$select=$this->select()->where('mail like ?',$mail)
							   ->where("val = ''");

		$data=$this->fetchAll($select);
		if ($data->count()==1) {
			$row = $data->getRow(0);
			if ($row->recpsw) {
				$key = $row->recpsw;
			} else {
				$key = genkey(16);
				$row->recpsw = $key;
				$row->save();
			}
			$uid = $row->id;
			$mail = new Zend_Mail("utf-8");
			$mail->setBodyHtml("Tai pamiršto slaptažodžio atkūrimo vedlys. Jeigu jūs pamenate savo slaptažodį ir nenorite jį atkurti, tiesiog ingonuorkite šį laišką.<br />
								Jūs norite atkurti <b>{$row->name}</b> slapažodį<br />
								Norėdami pradėti atkūrimo procesą, turite paspausti ant šios nuorodos:
								<a href=\"http://".(Zend_Registry::get('config')->webhost)."/index/recpsw/key/$key/uid/$uid\" target=\"_blank\">Atkurti slaptažodį</a><br /><br />
								Gero žaidimo<br /><br />
								agonija.eu administracija");

			$mail->setFrom('admin@agonija.eu', 'agonija.eu administracija');
			$mail->addTo($row->mail, '');
			$mail->setSubject('Prarasto slaptažodžio atkūrimas');
			$mail->send();
			return true;
		} else {
			return false;
		}
	}
    
	/**
	* @desc gauname userio objekta
	* @param integer $id userio id
	* @param boolean $readonly ar ikrauti useri tik skaitymui
	* @param boolean $story ar saugoti useri registre
	* @return User usrio objektas
	*/
	public function getuser($id, $readonly = false, $store = false) {
		$REGNAME = "user_".getsid()."_$id";

		if ($this->exsist(null,$id, true)) {
			if ($store)
				if (Zend_Registry::isRegistered($REGNAME))
					return Zend_Registry::get($REGNAME);

			Zend_loader::loadClass('User');
			$user = new User($id, $readonly);

			if ($store)
				Zend_Registry::set($REGNAME, $user);

			return $user;
		} else {
			return false;
		}
	}

	public function checkpsw($id,$psw) {
		$data=$this->find($id);
		if ($data->count()==0)
			return false;
		return (encodepsw($psw)==$data->getRow(0)->psw && $data->getRow(0)->visible == 1)? true : false;
	}

	public function checkban($id) {
		$data=$this->find($id);
		if ($data->count()==1) {
			return ($data->getRow(0)->ban==1);
		} else {
			return false;
		}
	}

	public function changedata($id,$data) {
		$where = $this->getAdapter()->quoteInto('id = ?', $id);
		return ($this->update($data,$where)==1)? true : false;
	}

	public function getrace($id) {
		$data=$this->find($id)->toArray();
		return $data[0]->race;
	}
	public function countuser($race=RACE_ALL) {
		$select=$this->select();
		$select->from($this->_name,array('num' => 'COUNT(*)'))
               ->where('visible = ?', 1);
		if ($race!=RACE_ALL) {
			$select->where('race = ?', $race);
		}
		$row=$this->fetchRow($select);
		return $row->num;
	}
	public function count_kill($race=RACE_ALL,$type='kill') {
		$select=$this->select();
		$select->from($this->_name,array('num' => "SUM(`$type`)"));
		if ($race!=RACE_ALL) {
			$select->where('race = ?', $race);
		}
		$row=$this->fetchRow($select);
		return $row->num;
	}
	/**
	* @desc gaunam vartotoja kariavimui
	* @param Minmax lygio rangas
	* @param RACE rase
	* @return User
	*/
	public function search4atack($lvl,$race) {
		$min=getexp($lvl->min-1,Zend_Registry::get('config')->ratio);
		$max=getexp($lvl->max,Zend_Registry::get('config')->ratio)-1;
		$select=$this->select()->setIntegrityCheck(false)
							   ->where('exp > ?',$min)
							   ->where('exp < ?',$max)
							   ->where('next_defence <= ?',time())
							   ->where('race = ?',$race)
							   ->where('demo = \'0\'')
                               ->where('visible = ?', 1)
							   ->from($this->_name,'id');
		$data=$this->fetchAll($select);
		if ($data->count()>0) {
			$i=rand(0,$data->count()-1);
			$row=$data->getRow($i);
			return new User($row->id);
		} else {
			return false;
		}
	}

	public function setIP($uid) {
		$data=$this->find($uid);
		if ($data->count()==1) {
			$row=$data->getRow(0);
			$row->IP=getip();
			$row->save();
			return true;
		}
		return false;
	}

	public function getAvgLvl($race = RACE_ALL) {
		$select = $this->select()->from($this->_name, "AVG(exp) as num");

		if ($race != RACE_ALL)
			$select->where("race = ?", $race);

		return lvl($this->fetchRow($select)->num);
	}

	public function getRandomLvl($race = RACE_ALL, $minLvl = 20) {
		$exp = getexp($minLvl, getbit());
        
		$select = $this->select()->from($this->_name, 'COUNT(*) as num')
								 ->where("exp > ?", $exp);
                                 
                                 

		if ($race != RACE_ALL)
			$select->where("race = ?", $race);
            
		$num = $this->fetchRow($select)->num;

		$rand = rand(0, $num - 1);

		$select = $this->select()->from($this->_name, "exp")
                                 ->where("exp > ?", $exp)
								 ->limit(1, $rand);

		if ($race != RACE_ALL)
			$select->where("race = ?", $race);

		return lvl($this->fetchAll($select)->getRow(0)->exp);
	}
	
	public function checkmail($mail) { 
		$select = $this->select()->where('mail = ?', $mail);
		
		$data = $this->fetchAll($select);
		
		return ($data->count() == 0);
	}
}
?>
