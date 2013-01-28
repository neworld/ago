<?php
class Main_UserLink {
	public function userLink($text,$id=null,$name=null) {
		if ($id) {
			$a="uid/$id";
		} else {
			$a="name/$name";
		}
		return "<a class=\"pointer\" onclick=\"content('/page/userinfo/$a');\">$text</a>";
	}
}
?>
