<?php
class Skills extends Zend_Db_Table {
	protected $_name = 'skills';
	protected $_primary = array('id');
	
	public function getskills($user,$lvl) {
		$data=$this->fetchAll($this->select()->where('`user` = ?', $user));
		$skills_db=include('app/data/skills_DB.php');
		//skaiciuojam visus
		$select=$this->select();
		$select->from($this->_name,'SUM(lvl) AS lvl')
		       ->where('`user` = ?', $user);
		Zend_Registry::set('all_skill_'.$user,($this->fetchRow($select)->lvl)-$data->count());
		
		$rez=array();
		Zend_Loader::loadClass('Skill');
		foreach ($data as $row) {
			if (isset($skills_db[$row->type])) {
				$rez[]=new Skill($row->id,
								 $row->type,
								 $row->lvl,
								 $skills_db[$row->type]['COST'],
								 $skills_db[$row->type]['MAX']*$lvl,
								 $user,
								 $skills_db[$row->type]['NAME'],
								 $skills_db[$row->type]['APIE']);
				unset($skills_db[$row->type]);
			}
		}
		foreach ($skills_db as $key => $value) {
			$data=array(
				'user' => $user,
				'type' => $key
			);
			$sid=$this->insert($data);
			$rez[]=new Skill($sid,
							 $key,
							 1,
							 $value['COST'],
							 $value['MAX']*$lvl,
							 $user,
							 $value['NAME'],
							 $value['APIE']);
		}
		return $rez;
	}
	
	public function deletebyuser($user) {
		$where = $this->getAdapter()->quoteInto('`user` = ?', $user);
		return $this->delete($where);
	}
}				
?>
