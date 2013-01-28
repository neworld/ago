<?php
class Main_ItemLinks {
	/**
	* @desc padarom aktvu linka daiktui
	* @param Item daiktas
	* @return String linkas
	*/
	public function itemLinks($item) {
		if (!$item)
			throw new Exception('Deja norite atvaizduoti ne daikta');

		return "<a style=\"font-weight:bold;color:#008E03\" class=\"pointer\" onmouseover=\"showitem({$item->id});\" onmouseout=\"closeitem()\">[{$item->title}]</a>";
	}
}

?>
