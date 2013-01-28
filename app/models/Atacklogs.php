<?
class Atacklogs extends Zend_Db_Table {
	protected $_name="logs_atack";
	protected $_primary=array("id");
	/**
	* pridedam irasa
	*
	* @param Int $aid atakos id
	* @param String $text zinute
	* @return array
	*/
	public function add($aid,$text) {
		return $this->insert(array(
								"aid" => $aid,
								"text" => $text
				));
	}
	/**
	* Gaunam kelis paskutinius irasus
	*
	* @param Int $clan atakos id
	* @param Int $num irasu skaicius
	* @return Zend_Db_Table_Rowset_Abstract
	*/
	public function getlast($aid, $num=10) {
		$select=$this->select()->where('aid = ?',$aid)
							   ->order("id ASC")
							   ->limit($num);

		return $this->fetchAll($select);
	}

	public function getSince($aid, $since) {
		$select = $this->select()->where('aid = ?', $aid)
								 ->where('id > ?', $since)
								 ->order('id ASC');

		return $this->fetchAll($select);
	}
}