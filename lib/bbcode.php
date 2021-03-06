<?php
   //Local copy

   function BBCode($Text)
	   {
		 // Replace any html brackets with HTML Entities to prevent executing HTML or script
			// Don't use strip_tags here because it breaks [url] search by replacing & with amp
			$Text = str_replace("<", "&lt;", $Text);
			$Text = str_replace(">", "&gt;", $Text);

			// Convert new line chars to html <br /> tags
			$Text = nl2br($Text);

			// Set up the parameters for a URL search string
			$URLSearchString = " a-zA-Z0-9\:\/\-\?\&\.\=\_\~\#\'";
			// Set up the parameters for a MAIL search string
			$MAILSearchString = $URLSearchString . " a-zA-Z0-9\.@";

			// Perform URL Search
			//$Text = preg_replace("/\[url\]([$URLSearchString]*)\[\/url\]/i", '<a href="$1" target="_blank">$1</a>', $Text);
			//$Text = preg_replace("(\[url\=([$URLSearchString]*)\](.+?)\[/url\])i", '<a href="$1" target="_blank">$2</a>', $Text);
		 //$Text = preg_replace("(\[url\=([$URLSearchString]*)\]([$URLSearchString]*)\[/url\])", '<a href="$1" target="_blank">$2</a>', $Text);
			$Text = preg_replace('#\[url\]([^\[]*?)\[/url\]#ei','handle_url_tag(\'$1\')',$Text);
			$Text = preg_replace('#\[url=([^\[]*?)\](.*?)\[/url\]#ei','handle_url_tag(\'$1\', \'$2\')',$Text); 
			$Text=do_clickable($Text); 
			// Perform MAIL Search
			$Text = preg_replace("(\[mail\]([$MAILSearchString]*)\[/mail\])", '<a href="mailto:$1">$1</a>', $Text);
			$Text = preg_replace("/\[mail\=([$MAILSearchString]*)\](.+?)\[\/mail\]/", '<a href="mailto:$1">$2</a>', $Text);
		 
			// Check for bold text
			$Text = preg_replace("(\[b\](.+?)\[\/b])is",'<span class="bold">$1</span>',$Text);

			// Check for Italics text
			$Text = preg_replace("(\[i\](.+?)\[\/i\])is",'<span class="italics">$1</span>',$Text);

			// Check for Underline text
			$Text = preg_replace("(\[u\](.+?)\[\/u\])is",'<span class="underline">$1</span>',$Text);

			// Check for strike-through text
			$Text = preg_replace("(\[s\](.+?)\[\/s\])is",'<span class="strikethrough">$1</span>',$Text);

			// Check for over-line text
			$Text = preg_replace("(\[o\](.+?)\[\/o\])is",'<span class="overline">$1</span>',$Text);

			// Check for colored text
			$Text = preg_replace("(\[color=(.+?)\](.+?)\[\/color\])is","<span style=\"color: $1\">$2</span>",$Text);

			// Check for sized text
			$Text = preg_replace("(\[size=([12]?[0-9])\](.+?)\[\/size\])is","<span style=\"font-size: $1px\">$2</span>",$Text);

			// Check for list text
			$Text = preg_replace("/\[list\](.+?)\[\/list\]/is", '<ul class="listbullet">$1</ul>' ,$Text);
			$Text = preg_replace("/\[list=1\](.+?)\[\/list\]/is", '<ul class="listdecimal">$1</ul>' ,$Text);
			$Text = preg_replace("/\[list=i\](.+?)\[\/list\]/s", '<ul class="listlowerroman">$1</ul>' ,$Text);
			$Text = preg_replace("/\[list=I\](.+?)\[\/list\]/s", '<ul class="listupperroman">$1</ul>' ,$Text);
			$Text = preg_replace("/\[list=a\](.+?)\[\/list\]/s", '<ul class="listloweralpha">$1</ul>' ,$Text);
			$Text = preg_replace("/\[list=A\](.+?)\[\/list\]/s", '<ul class="listupperalpha">$1</ul>' ,$Text);
			$Text = str_replace("[*]", "<li>", $Text);

			// Check for font change text
			$Text = preg_replace("(\[font=(.+?)\](.+?)\[\/font\])","<span style=\"font-family: $1;\">$2</span>",$Text);

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
			$Text = preg_replace("/\[code\](.+?)\[\/code\]/is","$CodeLayout", $Text);

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
			$Text = preg_replace("/\[quote\](.+?)\[\/quote\]/is","$QuoteLayout", $Text);
		 
			// Images
			// [img]pathtoimage[/img]
			$Text = preg_replace("/\[img\](.+?)\[\/img\]/i", '<img src="$1">', $Text);
		 
			// [img=widthxheight]image source[/img]
			$Text = preg_replace("/\[img\=([0-9]*)x([0-9]*)\](.+?)\[\/img\]/", '<img src="$3" height="$2" width="$1">', $Text);
			$Text=smile($Text);

			$Text=preg_replace("/\[google\](.*)\[\/google\]/i"," <a href=\"http://www.google.lt/search?q=$1\" target=\"_blank\">Look in google ($1)</a> ",$Text);
			$Text=preg_replace("/\[help\](.*)\[\/help\]/i"," <a href=\"http://help.neworldwar.com/index.php?search=$1&go=Rodyti\" target=\"_blank\">Look in help ($1)</a> ",$Text); 
		   return $Text;
	  }
?>