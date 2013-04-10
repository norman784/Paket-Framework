<?php
/***************************************************************************
keyword_extract.php
originally (c) Aaron Robbins
www.aaronrobbins.com
aaron@aaronrobbins.com

This script is free software; you can redistribute it and/or
modify it under the terms of the GNU Lesser General Public
License as published by the Free Software Foundation; either
version 2.1 of the License, or (at your option) any later version.

This library is distributed WITHOUT ANY WARRANTY; without even the implied
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public
License along with this library; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
****************************************************************************

******************************   WHAT IT DOES   ****************************
keyword_extract(); will search string/text content and find words that
are longer than 3 letters and used more than 3 times. It will then
return these words in a list with the most frequently used words first.

The list has links to search for the word on google or get keyword suggestions
for the word form Wordpot.

The script has a common words filter found on line 53 (top of the function) and
a punctuation filter found on line 62.
Feel free to add or remove words that you do or do not want filtered out.
List common words in a comma seperated value format.
List punctuation space seperated

****************************************************************************

******************************   HOW TO USE IT   ***************************
1.Function Placement:
Include keyword_extract.php in any php document before you make a call to the
function
  example: include("keyword_extract.php");

or copy and paste the function somewhere before you plan on calling it.

2.Function usage.
Assign your string text content to a variable.
  example: $text = "This is my string content"

Call the function by assigning it to a variable.
  example: $keywords = keyword_extract($text);

Echo the result wherever you want.
  example: echo $keywords;

****************************************************************************/

class PK_Keyword {
	function extract($text, $max_words=0){
	    $text = str_replace(",","", $text);
	    $text = str_replace(".","", $text);
	    $text = str_replace(";","", $text);
	    $text = strtolower($text);
	    $punc =". , : ; ' ? ! ( ) \" \\";
	    $punc = explode(" ",$punc);
	    foreach($punc as $value){
	        $text = str_replace($value, " ", $text);
	    }
	    $commonWords ="about,that's,this,that,than,then,them,there,their,they,it's,with,which,were,where,whose,when,what,her's,he's,have,".
	    			  "acerca,eso,esto,esos,entonces,ellos,ahi,estos,es,con,donde,para,cual,que,suyo,suya,tiene";
	    $commonWords = strtolower($commonWords);
	    $words = explode(" ", $text);
	    $commonWords = explode(",", $commonWords);
	    foreach ($words as $value) {
	        $common = false;
	        if (strlen($value) > 3){
	            foreach($commonWords as $commonWord){
	                if ($commonWord == $value){
	                    $common = true;
	                }
	            }
	            if($common != true){
	                $keywords[] = $value;
	            }
	        }
	    }
	
	    $keywords = array_count_values($keywords);
	    
	    $return_keywords = '';
	    $count_words = 0;
	    
	    arsort($keywords);
	    foreach ($keywords as $key => $value) {
	    	if ($max_words > 0) {
	    		if ($count_words >= $max_words)
	    			break;
	    		++$count_words;
	    	}
	    	
	        if ($value < 2)
	        	continue;
	        
//	        echo "<p><strong>" . ucfirst($key) . "</strong>\" is used <strong>" . $value . "</strong> times. | <a href=\"http://www.google.com/search?q=$key\" target=\"_blank\">Search Google</a> | <a href=\"http://www.wordpot.com/KeywordResearch?word=$key\" target=\"_blank\">Wordpot Keyword Suggestions</a></p>";
			$return_keywords .= ' '.strtr($key, array("\n"=>'', "\r"=>'')).
//							    ' ['.$value.']'.
			                    '';
	    }
		
	    return trim(strip_tags($return_keywords));
	}
}
?>