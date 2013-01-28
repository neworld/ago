<?php
class ItemsSets extends Zend_Db_Table {
    protected $_name = 'items_sets';
    protected $_primary = array("id");
    
    static function genKey($uid) {
        return "AG_itemsset_".getsid()."_$uid";
    }
    
    static function setSets($uid, $data) {
        return cacheAdd(self::genKey($uid), $data);
    }
    
    static function getSets($uid) {
        return cacheGet(self::genKey($uid));
    }
    
    public function get($sid, $num = null) {
        $select = $this->select()->where("setID = ?", $sid)
                                 ->order("num ASC");
                                 
        if ($num !== null)
            $select->where("num <= ?", $num);
            
        return $this->fetchAll($select);
    }
}       
?>
