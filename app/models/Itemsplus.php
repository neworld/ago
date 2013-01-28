<?php
class Itemsplus extends Zend_Db_Table {
	protected $_name="items_plus";
	protected $_primary = array('id'); 
	
	public function getplus($item) {
		$select=$this->select();
		$select->where("item = ?",$item);
		$data=$this->fetchAll($select);
		$rez=array(); 
		if ($data->count()>0) { 
			foreach ($data as $row) {
				$rez[$row->type]=$row->value;
			}
		}
		return $rez;
	}
	
	public function setplus($item,$plus) {
		$this->deleteplus($item);
		
		if ((count($plus>0)) && (is_array($plus))) {
			foreach ($plus as $key => $value) {
				$data=array(
					'item' => $item,
					'type' => $key,
					'value' => $value
				);
				$this->insert($data);
			}
			return true;
		} else {
			return false;
		}
	}
	
	public function deleteplus($item) {
		$this->delete("item = $item");
	}
}		
			
	
?>
