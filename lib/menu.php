<?php
class Tmenu {
	public $adr;
	public $text;
	public $title;
	public $onclick;
	public $id;
	public $classs;
	
	function __construct($text,$adr=null,$title=null,$onclick=null,$id=null,$classs=null) {
		$this->adr=$adr;
		$this->text=$text;
		$this->title=$title;
		$this->onclick=$onclick;
		$this->id=$id;
		$this->classs=$classs;
	}
	
	public function makehref() {
		$a=array();
		if ($this->adr)
			$a[]="href=\"{$this->adr}\"";
		if ($this->title)
			$a[]="title=\"{$this->title}\"";
		if ($this->onclick)
			$a[]="onclick=\"".str_replace('"','\\"',$this->onclick)."\"";
		if ($this->id)
			$a[]="id=\"{$this->id}\"";
		if ($this->classs)
			$a[]="class=\"{$this->classs}\"";
		
		$aa=implode(' ',$a);
		
		return "<a $aa>{$this->text}</a>";
	}
}
	
?>
