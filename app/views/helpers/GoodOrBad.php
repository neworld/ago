<?php
class Main_GoodOrBad {
	private $badClass='bad';
	private $goodClass='good';
	
	public function goodOrBad($good=null,$bad=null) {
		$rez='';
		if ($good) {
			$rez.="<div class=\"{$this->goodClass}\">$good</div>";
		}
		if ($bad) {
			$rez.="<div class=\"{$this->badClass}\">$bad</div>";
		}
		return $rez;
	}
}			
?>