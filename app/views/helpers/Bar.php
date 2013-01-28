<?php
class Main_Bar {
	public function bar($w=240,$h=19,$bgc='001133',$c='556699',$tc='ffffff',$bc='4d835f',$p=1,$all=100,$size=9,$text='',$style='',$id='') {
		return "<img width=\"$w\" height=\"$h\" style=\"$style\" id=\"$id\" src=\"/bar.png?w=$w&amp;h=$h&amp;bgc=$bgc&amp;c=$c&amp;p=$p&amp;size=$size&amp;text=$text&amp;tc=$tc&amp;bc=$bc&amp;all=$all\" alt=\"\" />";
	}
}
?>
