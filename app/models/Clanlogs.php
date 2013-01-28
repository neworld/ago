<?
class Clanlogs extends Zend_Db_Table {
	protected $_name="clan_logs";
	protected $_primary=array("id");
	/**
	* pridedam irasa
	*
	* @param Int $clan klano id
	* @param String $text zinute
	* @return array
	*/
	public function add($clan,$text) {
		return $this->insert(array(
								"clan" => $clan,
								"text" => $text
				));
	}
	/**
	* Gaunam kelis paskutinius irasus
	*
	* @param Int $clan klano id
	* @param Int $num irasu skaicius
	* @return Zend_Db_Table_Rowset_Abstract
	*/
	public function getlast($clan, $num=10) {
		$select=$this->select()->where('clan = ?',$clan)
							   ->order("id DESC")
							   ->limit($num);

		return $this->fetchAll($select);
	}
}