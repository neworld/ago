<?php
class BBcode implements Zend_Filter_Interface {
	public function filter($Text) {
		$text=$Text;
		$text = str_replace("<", "&lt;", $text);
		$text = str_replace(">", "&gt;", $text);
		$text = nl2br($text);
		$URLSearchString = " a-zA-Z0-9\:\/\-\?\&\.\=\_\~\#\'";
		$MAILSearchString = $URLSearchString . " a-zA-Z0-9\.@";
		
		$text = preg_replace('#\[url\]([^\[]*?)\[/url\]#ei','handle_url_tag(\'$1\')',$text);
		$text = preg_replace('#\[url=([^\[]*?)\](.*?)\[/url\]#ei','handle_url_tag(\'$1\', \'$2\')',$text); 
		//$text=do_clickable($text); 

		$text = preg_replace("(\[mail\]([$MAILSearchString]*)\[/mail\])", '<a href="mailto:$1">$1</a>', $text);
		$text = preg_replace("/\[mail\=([$MAILSearchString]*)\](.+?)\[\/mail\]/", '<a href="mailto:$1">$2</a>', $text);
	 
	 	// tikrinam header
	 	$text = preg_replace("(\[h([1-6])\](.+?)\[\/h([1-6])])is",'<h$1>$2</h$1>',$text);
	 
		// Check for bold text
		$text = preg_replace("(\[b\](.+?)\[\/b])is",'<span class="bold">$1</span>',$text);

		// Check for Italics text
		$text = preg_replace("(\[i\](.+?)\[\/i\])is",'<span class="italics">$1</span>',$text);

		// Check for Underline text
		$text = preg_replace("(\[u\](.+?)\[\/u\])is",'<span class="underline">$1</span>',$text);

		// Check for strike-through text
		$text = preg_replace("(\[s\](.+?)\[\/s\])is",'<span class="strikethrough">$1</span>',$text);

		// Check for over-line text
		$text = preg_replace("(\[o\](.+?)\[\/o\])is",'<span class="overline">$1</span>',$text);

		// Check for colored text
		$text = preg_replace("(\[color=(.+?)\](.+?)\[\/color\])is","<span style=\"color: $1\">$2</span>",$text);

		// Check for sized text
		$text = preg_replace("(\[size=([12]?[0-9])\](.+?)\[\/size\])is","<span style=\"font-size: $1px\">$2</span>",$text);

		// Check for list text
		$text = preg_replace("/\[list\](.+?)\[\/list\]/is", '<ul class="listbullet">$1</ul>' ,$text);
		$text = preg_replace("/\[list=1\](.+?)\[\/list\]/is", '<ul class="listdecimal">$1</ul>' ,$text);
		$text = preg_replace("/\[list=i\](.+?)\[\/list\]/s", '<ul class="listlowerroman">$1</ul>' ,$text);
		$text = preg_replace("/\[list=I\](.+?)\[\/list\]/s", '<ul class="listupperroman">$1</ul>' ,$text);
		$text = preg_replace("/\[list=a\](.+?)\[\/list\]/s", '<ul class="listloweralpha">$1</ul>' ,$text);
		$text = preg_replace("/\[list=A\](.+?)\[\/list\]/s", '<ul class="listupperalpha">$1</ul>' ,$text);
		$text = str_replace("[*]", "<li>", $text);

		// Check for font change text
		$text = preg_replace("(\[font=(.+?)\](.+?)\[\/font\])","<span style=\"font-family: $1;\">$2</span>",$text);

		// Declare the format for [code] layout
		$CodeLayout = '<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
							<tr>
								<td class="quotecodeheader"> Code:</td>
							</tr>
							<tr>
								<td class="codebody">$1</td>
							</tr>
					   </table>';
		// Check for [code] text
		$text = preg_replace("/\[code\](.+?)\[\/code\]/is","$CodeLayout", $text);

		// Declare the format for [quote] layout
		$QuoteLayout = '<table width="90%" border="0" align="center" cellpadding="0" cellspacing="0">
							<tr>
								<td class="quotecodeheader"> Quote:</td>
							</tr>
							<tr>
								<td class="quotebody">$1</td>
							</tr>
					   </table>';
				 
		// Check for [code] text
		$text = preg_replace("/\[quote\](.+?)\[\/quote\]/is","$QuoteLayout", $text);
	 
		// Images
		// [img]pathtoimage[/img]
		$text = preg_replace("/\[img\](.+?)\[\/img\]/i", '<img src="$1">', $text);
	 
		// [img=widthxheight]image source[/img]
		$text = preg_replace("/\[img\=([0-9]*)x([0-9]*)\](.+?)\[\/img\]/", '<img src="$3" height="$2" width="$1">', $text);

		$text=preg_replace("/\[google\](.*)\[\/google\]/i"," <a href=\"http://www.google.lt/search?q=$1\" target=\"_blank\">Look in google ($1)</a> ",$text);
		//$text=preg_replace("/\[help\](.*)\[\/help\]/i"," <a href=\"http://help.neworldwar.com/index.php?search=$1&go=Rodyti\" target=\"_blank\">Look in help ($1)</a> ",$text); 
	   return $text;
	}
}
		
?>