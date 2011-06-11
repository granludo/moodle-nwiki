<?php

//this array is where parser saves it's global variables.
$parser_vars = array();
//this array is where parser saves logs (like TOC...)
$parser_logs = array();

$parser_format = array();

$courseid = 0;
$entryid = 0;
$groupid = 0;
$pagename = '';
$userid = 0;
$wikiid = 0;

//*********************************** PARSER TO HTML ****************************************
function dfwiki_sintax_html_bis (&$text, $editor, $course, $wiki, $entry, $page){
	global $courseid, $entryid, $groupid, $pagename, $userid, $wikiid, $parser_logs, $parser_format;
	
	$courseid = $course;
	$entryid = $entry;
    $groupid = $page->groupid;
    $pagename = $page->pagename;
    $userid = $page->userid;
    $wikiid = $wiki;

    dfwiki_parser_reset_vars_bis();
	dfwiki_parser_reset_logs_bis();
  
    $parser_format['pre-parser'] = array();
	$parser_format['pre-parser']['smilies']->func = 'replace_smilies';
	$parser_format['pre-parser']['smilies']->reference = true;
	
	$parser_format['no-parse'] = array();
	$parser_format['no-parse']['nowiki']->marks = array ('<nowiki>','</nowiki>');
	
	$parser_format['line-definition']->marks = "\r\n";
	
	$parser_format['whole-line'] = array ();
	$parser_format['whole-line']['hr']->marks = '-';
	$parser_format['whole-line']['hr']->subs = "<hr noshade>\n";
	$parser_format['whole-line']['hr']->multisubs = false;
	
	$parser_format['line-start'] = array ();
	
	$parser_format['line-start-end'] = array ();
	$parser_format['line-start-end']['h1']->marks = array ('===','===');
	$parser_format['line-start-end']['h1']->subs = array ('<h1>','</h1>');
	$parser_format['line-start-end']['h2']->marks = array ('==','==');
	$parser_format['line-start-end']['h2']->subs = array ('<h2>','</h2>');
	$parser_format['line-start-end']['h3']->marks = array ('=','=');
	$parser_format['line-start-end']['h3']->subs = array ('<h3>','</h3>');
	
	$parser_format['start-end'] = array ();
	$parser_format['start-end']['b']->marks = array ("'''","'''");
	$parser_format['start-end']['b']->subs = array ('<b>','</b>');
	$parser_format['start-end']['i']->marks = array ("''","''");
	$parser_format['start-end']['i']->subs = array ('<i>','</i>');
	$parser_format['start-end']['internal links']->marks = array ("[[","]]");
	$parser_format['start-end']['internal links']->func = 'dfwiki_parser_default_internal_link_bis';
	$parser_format['start-end']['external links']->marks = array ("[","]");
	$parser_format['start-end']['external links']->func = 'dfwiki_parser_default_external_link_bis';
	
	$parser_format['direct-substitution'] = array ();
	$parser_format['direct-substitution']['br']->marks = '%%%';
	$parser_format['direct-substitution']['br']->subs = '<br>';
	
	$parser_format['line-count-start'] = array ();
	$parser_format['line-count-start']['preformat']->marks = ' ';
	$parser_format['line-count-start']['preformat']->subs = array ('<pre>',"</pre>\n");
	$parser_format['line-count-start']['preformat']->func = 'dfwiki_parser_default_open_group_bis';
	$parser_format['line-count-start']['preformat']->elsefunc = 'dfwiki_parser_default_close_group_bis';
	$parser_format['line-count-start']['ul']->marks = '*';
	$parser_format['line-count-start']['ul']->subs = array('<ul>','</ul>');
	$parser_format['line-count-start']['ul']->func = 'dfwiki_parser_default_list_bis';
	$parser_format['line-count-start']['ul']->elsefunc = 'dfwiki_parser_default_list_bis';
	$parser_format['line-count-start']['ol']->marks = '#';
	$parser_format['line-count-start']['ol']->subs = array('<ol>','</ol>');
	$parser_format['line-count-start']['ol']->func = 'dfwiki_parser_default_list_bis';
	$parser_format['line-count-start']['ol']->elsefunc = 'dfwiki_parser_default_list_bis';
	$parser_format['line-count-start']['tabulacions']->marks = ':';
	$parser_format['line-count-start']['tabulacions']->subs = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	
	$parser_format['line-array-definition'] = array ();
	$parser_format['line-array-definition']['header']->marks = '!';
	$parser_format['line-array-definition']['header']->func = 'dfwiki_parser_default_table_bis';
	$parser_format['line-array-definition']['header']->elsefunc = 'dfwiki_parser_default_table_bis';
	$parser_format['line-array-definition']['header']->subs = array ('<th class="header c0">','</th>');
	$parser_format['line-array-definition']['row']->marks = '|';
	$parser_format['line-array-definition']['row']->func = 'dfwiki_parser_default_table_bis';
	$parser_format['line-array-definition']['row']->elsefunc = 'dfwiki_parser_default_table_bis';
	$parser_format['line-array-definition']['row']->subs = array ('<td class="cell c0">','</td>');
	
	$parser_format['post-line'] = array();
	
	$parser_format['post-parser'] = array();
	$parser_format['post-parser']['moodle']->func = 'dfwiki_moodle_format_text_bis';
	$parser_format['post-parser']['toc']->func = 'dfwiki_parser_default_toc_bis';
    
	$res = dfwiki_parse_text_bis ($text,$editor);
	return preg_replace("/(\[)\[(.*)\](\])/", '$1$2$3', $res);
}
//-------------------------------- FUNCIONS PRIVADES -----------------------------

//reset vars array
function dfwiki_parser_reset_vars_bis(){
	global $parser_vars;
	unset($parser_vars);
	$parser_vars = array();
	return true;
}
//reset log array
function dfwiki_parser_reset_logs_bis(){
	global $parser_logs;
    unset($parser_logs);
	$parser_logs = array();
	return true;
}
//reset sintax
function dfwiki_parser_reset_sintax_bis(){
	global $parser_format;
    unset($parser_format);	
	$parser_format = array();
	return true;
}


//convert wiki content to html content
function dfwiki_parse_text_bis($text,$format){
	global $dfwiki_parse, $parser_format,$CFG;
	
	//PRE-PARSE
	if (isset($parser_format['pre-parser'])){
		foreach ($parser_format['pre-parser'] as $par){
			if (isset($par->func)){
				$func = $par->func;
				if (!isset ($par->reference))$par->reference = false;
				if ($par->reference){
					$func($text);
				}else{
					$text = $func($text);
				}
			}
		}
	}
	
	//NO-PARSE
	if (isset($parser_format['no-parse'])){
		//if it's not an array
		if (!is_array($parser_format['no-parse'])) return $parser_format['no-parse']($text);
		
		$noparsetext = array();
		
		foreach ($parser_format['no-parse'] as $par){
    		//text with no-parse chops
			$before = '';
			//text still with no-parse chops
			$after = $text;
			//begining and ending marks
			$startmark = $par->marks[0];
			$endmark = $par->marks[1];
			$endmark_len = strlen($endmark);
			$startmark_len = strlen($startmark);
			
			//initialize this text chops which aren't parsed
			$noparsetext[$startmark] = array();
			
			//this index is used to be foreach free
			$index = 0;
			
			while((($l = strpos($after, $startmark)) !== false) && ($r = strpos($after, $endmark, $l + $startmark_len))) {
				//save no-parse text
				if (!isset($par->delmarks)) $par->delmarks = true;
				$subtext = ($par->delmarks)? substr($after, $l + $startmark_len, $r - $l - $startmark_len) : $startmark.substr($after, $l + $startmark_len, $r - $l - $startmark_len).$endmark;
				if (isset($par->func)){
					$func = $par->func;
					$noparsetext[$startmark][$index] = $func($subtext);
				}else{
					$noparsetext[$startmark][$index] = $subtext;
				}
				//parse before part
				$before.= substr($after, 0, $l).$startmark;
				$after = substr($after, $r + $endmark_len);
				$index++;
			}
			$before.= $after;
			//save $text without no-parse chops
			$text = $before;
			
		}
		
    	//text is parsed here
		$text = dfwiki_parser_inlines_bis($text);
		
		$res = $text;
		foreach ($noparsetext as $mark => $mtext){
			$index = 0;
			$after = $res;
			$res = '';
			$startmark_len = strlen($mark);
			while (($l = strpos($after, $mark)) !== false){
				$res.= substr($after, 0, $l).$mtext[$index];
				$index++;
				$after = substr($after, $l + $startmark_len);
			}
			$res.=$after;
		}
	} else {
		//all text is passed
		$res = dfwiki_parser_inlines_bis($text);
	}
	
	//POST-PARSER
	if (isset($parser_format['post-parser'])){
		foreach ($parser_format['post-parser'] as $par){
			if (isset($par->func)){
				$func = $par->func;
				if (!isset ($par->reference))$par->reference = false;
				if ($par->reference){
					$func($res);
				}else{
					$res = $func($res);
				}
			}
		}
	}
	
	return $res;
}

//this function parses every paragraf
function dfwiki_parser_inlines_bis (&$text){
	global $dfwiki_parse,$dfwiki_first,$parser_format;

	//LINE-DEFINITION
	if (!isset($parser_format['line-definition'])) $parser_format['line-definition']->marks = "\r\n";
	
	if (isset($parser_format['line-definition']->func)){
		$func = $parser_format['line-definition']->func;
		if(isset($parser_format['line-definition']->marks)){
			$lines = $func ($text, $parser_format['line-definition']->marks);
		}else{
			$lines = $func ($text);
		}
	}else{
		$lines = explode($parser_format['line-definition']->marks,$text);
	}
	
	//this is the result string
	$res = '';
	//analice every paragraf
	foreach ($lines as $line){
		$res.= dfwiki_parser_line_bis($line);
		//new paragraf
		if(chop($line)==''){
			$res.='<br><br>';
		}
		$res.="\n";
	}
	//cheat!!! this line end all opened tables and lists
	$line='';
	$res.= dfwiki_parser_line_bis($line);
	
	return $res;
}

//private function to convert a line in fomrat wiki into html
function dfwiki_parser_line_bis(&$line){

	//sintax is in ewiki.php between 1841 and 2035

	global $listtypes, $tabletypes,$parser_format;

	$res = $line;

	//PRE-LINE
	if (isset($parser_format['pre-line'])){
		foreach ($parser_format['pre-line'] as $par){
			if (isset($par->func)){
				$func = $par->func;
				$res = $func($res);
			}
		}
	}
	
	//SPECIAL-LINE
	if (isset($parser_format['special-line'])){
		foreach ($parser_format['special-line'] as $par){
			if (chop($line)==$par->marks){
				if (isset($par->func)){
					$func = $par->func;
					$res = (isset($par->subs))? $func($line,$par->marks,$par->subs) : $func($line,$par->marks);
				}else{
					$res = $par->subs;
				}
				return $res;
			} else {
				if (isset($par->elsefunc)){
					$func = $par->elsefunc;
					$res = (isset($par->subs))? $func($res,$par->marks,$par->subs) : $func($res,$par->marks);
					if (!isset ($par->exit)) $par->exit = false;
					if($par->exit) return $res;
				}
			}
		}
	}
	
	//WHOLE-LINE
	if (isset($parser_format['whole-line'])){
		$chopline = chop($line);
		foreach ($parser_format['whole-line'] as $par){
			if (str_replace ( $par->marks, '', $chopline)=='' && $chopline!=''){
				$res='';
				if (!isset($par->multisubs)) $par->multisubs = true;
				if ($par->multisubs){
					$max = strlen($chopline)/strlen($par->marks);
					for ($i=0;$i<$max;$i++){
						if (isset($par->func)){
							$func = $par->func;
							$res.= (isset($par->subs))? $func($line,$par->subs) : $func($line);
						}else{
							$res.= $par->subs;
						}
					}
				}else{
					if (isset($par->func)){
						$func = $par->func;
						$res = (isset($par->subs))? $func($line,$par->marks,$par->subs) : $func($line,$par->marks);
					}else{
						$res = $par->subs;
					}
				}
				return $res;
			}else{
				if (isset($par->elsefunc)){
					$func = $par->elsefunc;
					$res = (isset($par->subs))? $func($line,$par->marks,$par->subs) : $func($line,$par->marks);
				}
			}
		}
		unset ($chopline);
	}

	//LINE-START
	if (isset($parser_format['line-start'])){
		$chopline = chop($line);
		foreach ($parser_format['line-start'] as $par){
			$mark_len = strlen($par->marks);
			if (strpos($res, $par->marks) === 0) {
				$inside = substr($res, $mark_len);
				if (!isset($par->func)) $par->func = 'dfwiki_parser_default_encapsule_bis';
				
				$func = $par->func;
				$part = (isset($par->subs))? $func($inside,$par->marks,$par->subs) : $func($inside,$par->marks);
				$res = $part;
				unset ($part);
			} else {
				//elsefunc
				if (isset($par->elsefunc)){
					$func = $par->elsefunc;
					$res = (isset($par->subs))? $func($res,$par->marks,$par->subs) : $func($res,$par->marks);
				}
			}
		}
		unset ($chopline);
	}
	
	//LINE-END
	if (isset($parser_format['line-end'])){
		foreach ($parser_format['line-end'] as $par){
			$mark_len = strlen($par->marks);
			$chopline = chop($line);
			if (strrpos($chopline, $par->marks) === strlen($chopline)-strlen($par->marks)) {
				$inside = substr($res,0, strrpos($chopline, $par->marks));
				if (!isset($par->func)) $par->func = 'dfwiki_parser_default_encapsule_bis';
				
				$func = $par->func;
				$part = (isset($par->subs))? $func($inside,$par->marks,$par->subs) : $func($inside,$par->marks);
				$res = $part;
				unset ($part);
			} else {
				//elsefunc
				if (isset($par->elsefunc)){
					$func = $par->elsefunc;
					$res = (isset($par->subs))? $func($res,$par->marks,$par->subs) : $func($res,$par->marks);
				}
			}
		}
		unset ($chopline);
	}	
	
	//LINE-START-END
	if (isset($parser_format['line-start-end'])){
		foreach ($parser_format['line-start-end'] as $par){
			$startmark_len = strlen($par->marks[0]);
			$endmark_len = strlen($par->marks[1]);
			if((($l = strpos($res, $par->marks[0])) !== false) && ($r = strpos($res, $par->marks[1], $l + $startmark_len))) {
				if ($l==0){
					$inside = substr($res, $l + $startmark_len, $r - $l - $startmark_len);
					$after = substr($res, $r + $endmark_len);
					
					if (!isset($par->func)) $par->func = 'dfwiki_parser_default_header_bis';
					$func = $par->func;
					$part = (isset($par->subs))? $func($inside,$par->marks,$par->subs) : $func($inside,$par->marks);
					$res = $part.$after;
					unset ($part);	
				}
			} else {
				//elsefunc
				if (isset($par->elsefunc)){
					$func = $par->elsefunc;
					$res = (isset($par->subs))? $func($res,$par->marks,$par->subs) : $func($res,$par->marks);
				}
			}
		}
	}
	
	//LINE-COUNT-START (conta les vegades que apareix)
	if (isset($parser_format['line-count-start'])){
		$chopline = chop($res);
		foreach ($parser_format['line-count-start'] as $par){
			$mark_len = strlen($par->marks);
			$num=0;
			while (($l = strpos($chopline, $par->marks)) === 0){
				$chopline = substr($chopline, $l + $mark_len);
				$num++;
			}
			
			if ($num>0){
				$max = $num*$mark_len;
				$inside = substr($res, $max*$mark_len);
				if (!isset($par->func)) $par->func = 'dfwiki_parser_default_repeat_before_bis';
				$func = $par->func;
				$part = (isset($par->subs))? $func($inside,$max,$par->marks,$par->subs) : $func($inside,$max,$par->marks);
				$res = $part;
				unset ($part);
			} else {
				//executem la elsefunc
				if (isset($par->elsefunc)) {
					$func = $par->elsefunc;
					$part = (isset($par->subs))? $func($res,0,$par->marks,$par->subs) : $func($res,0,$par->marks);
					$res = $part;
				}
			}
		}
		unset ($chopline);
	}
	
	//START-END
	if (isset($parser_format['start-end'])){
		foreach ($parser_format['start-end'] as $par){
			$startmark_len = strlen($par->marks[0]);
			$endmark_len = strlen($par->marks[1]);
			$loop = 20;

			while(($loop--) && (($l = strpos($res, $par->marks[0])) !== false) && ($r = strpos($res, $par->marks[1], $l + $startmark_len))) {
				$before = substr($res, 0, $l);
				$inside = substr($res, $l + $startmark_len, $r - $l - $endmark_len);
				$after = substr($res, $r + $endmark_len);

				//if not function is found then
				if (!isset($par->func)) $par->func = 'dfwiki_parser_default_encapsule_bis';
				
				$func = $par->func;
				$part = (isset($par->subs))? $func($inside,$par->marks,$par->subs) : $func($inside,$par->marks);
				$res = $before.$part.$after;
				unset ($part);
			}
		}
	}
	
	
	//LINE-ARRAY-DEFINITION
	if (isset($parser_format['line-array-definition'])){
		foreach ($parser_format['line-array-definition'] as $par){
			$chopline = chop($line);
			$mark_len = strlen($par->marks);
			if((strpos($chopline, $par->marks)=== 0) && (strrpos($chopline, $par->marks)) === strlen ($chopline)-1) {
				$inside = explode ($par->marks,substr($res, $mark_len,  strrpos($res, $par->marks) - $mark_len));
				
				if (!isset($par->func)) $par->func = 'dfwiki_parser_default_array_encapsule_bis';
				$func = $par->func;
				$res = (isset($par->subs))? $func($inside,$par->marks,$par->subs) : $func($inside,$par->marks);
				unset ($part);
			} else {
				//elsefunc
				if (isset($par->elsefunc)){
					$func = $par->elsefunc;
					$res = (isset($par->subs))? $func($res,$par->marks,$par->subs) : $func($res,$par->marks);
				}
			}
		}
	}
	
	//DIRECT-SUBSTITUTION
	if (isset($parser_format['direct-substitution'])){
		foreach ($parser_format['direct-substitution'] as $par){
			$mark_len = strlen($par->marks);
			$loop = 20;
			//echo 'dins '.$par->marks[0].' ';
			while(($loop--) && (($l = strpos($res, $par->marks)) !== false)) {
				$before = substr($res, 0, $l);
				$inside = substr($res, $l, $mark_len);
				$after = substr($res, $l + $mark_len);
				//if not functions is found then set a substituting one
				if (!isset($par->func)) $par->func = 'dfwiki_parser_default_substitution_bis';
				
				$func = $par->func;
				$part = (isset($par->subs))? $func($inside,$par->marks,$par->subs) : $func($inside,$par->marks);
				$res = $before.$part.$after;
				unset ($part);
			}
		}
	}
	
	//POST-LINE
	if (isset($parser_format['post-line'])){
		foreach ($parser_format['post-line'] as $par){
			//execute the function
			if (isset($par->func)){
				$func = $par->func;
				$res = $func($res);
			}
		}
	}
	
	return $res;
}

//funcions auxiliar que aniran modificades---------------

function dfwiki_moodle_format_text_bis(&$text){
	return format_text($text, FORMAT_MARKDOWN);
}

//funci� identitat, retorna el text igual
function dfwiki_parser_default_identity_bis (&$line,$p1='',$p2='',$p3='',$p4=''){
	echo '-&gt;identityt<br>';
	return $line;
}

function dfwiki_parser_default_internal_link_bis (&$line,$marks){
	return dfwiki_sintax_create_internal_link_bis ($line);
}

function dfwiki_parser_default_external_link_bis (&$line,$marks){
	return dfwiki_sintax_create_external_link_bis ($line);
}

function dfwiki_parser_default_encapsule_bis (&$line,$marks,$subs=false){
	if ($subs === false) $subs = array('','');
	return $subs[0].$line.$subs[1];
}

function dfwiki_parser_default_substitution_bis ($text,$marks,$subs=false){
	if ($subs===false) $subs = '';
	if (is_array($subs)) $subs = implode('',$subs);
	return $subs;
}

function dfwiki_parser_default_header_bis (&$line,$marks,$subs=false){
	global $parser_logs, $parser_vars;
	
	if ($subs===false) $subs = array ('','');
	if (!is_array($subs)) $subs = array ($subs,$subs);
	
	//import parser_vars
	if (!isset($parser_vars['header'])){
		//indica si estem dins del grup
		$parser_vars['header'] = 1;
	}
	
	//create anchor label
	$anchor = 'toc'.$parser_vars['header'];
	$parser_vars['header']++;
	
	//save header into log
	if (!isset($parser_logs['toc'])) $parser_logs['toc'] = array();
	$parser_logs['toc'][] = array ($subs[0],$anchor,$line);
	
	//add anchors to $subs
	$subs = array ('<a name="'.$anchor.'"></a>'.$subs[0] , $subs[1]);
	
	//encapsule header:
	$res = dfwiki_parser_default_encapsule_bis ($line,$marks,$subs);
	return $res;
}

//this function sets $max times $subs in the line begining
function dfwiki_parser_default_repeat_before_bis ($line,$max,$marks,$subs=''){
	$res='';
	for ($i=0;$i<$max;$i++){
		$res.=$subs;
	}
	$res.=$line;
	return $res;
}

//encapsulates an array
function dfwiki_parser_default_array_encapsule_bis ($line,$marks,$subs = false){
	if ($subs === false) $subs = array ('','');
	if (!is_array($line)) $line = array ($line);
	$res = '';
	foreach ($line as $part){
		$res.= dfwiki_parser_default_encapsule_bis ($part,'',$subs);
	}
	return $res;
}

function dfwiki_parser_default_toc_bis (&$text){
	global $parser_logs;

	if (!isset($parser_logs['toc'])) return $text;

    $res = '<table align="center" width="95%"  class="generalbox bordarkgrey" border="0" cellpadding="0" cellspacing="0">
    				<tr><td bgcolor="#EEEEEE">';

    $num = array(0,0,0);
    foreach ($parser_logs['toc'] as $header){

    	//get level
    	$lev = substr ($header[0],2,1);
    	//set $numt (number text)
    	if ($lev<2) $num[2]=0;
    	if ($lev<3) $num[1]=0;
    	$num[$lev-1]++;

    	$numt = '';
    	for ($i=0;$i<$lev;$i++){
    		$res.='&nbsp;&nbsp;&nbsp;';
    		$numt.= $num[$i].'.';
    	}
    	$res.= '<a href="#'.$header[1].'">'.$numt.' '.$header[2].'</a><br />';//($subs[0],$anchor,$line);
    }
    $res.= "</td></tr></table>\n".$text;
    unset($parser_logs['toc']);
    return $res;
}

//encapsulate lines
function dfwiki_parser_default_open_group_bis ($line,$p1='',$p2='',$p3=false){
	global $parser_vars;
	//si tenim 4 parametres podem ignorar el segon (com en el cas de les llistes)
	if ($p3===false){
		$marks = $p1;
		$subs = $p2;
	}else{
		$marks = $p2;
		$subs = $p3;
		$num = $p1;
	}
	$subs = (is_array($subs))? $subs : array ($subs,$subs);
	$marks = (is_array($marks))? $marks[0] : $marks;
	
	//initialize parse_vars[list]
	if (!isset($parser_vars['enc_group'])) $parser_vars['enc_group'] = array();
	if (!isset($parser_vars['enc_group'][$marks])){
		//indica si estem dins del grup
		$parser_vars['enc_group'][$marks] = false;
	}
	
	$res = '';
    //if required, begin the group
		if (!$parser_vars['enc_group'][$marks]){
		$res = $subs[0];
		$parser_vars['enc_group'][$marks] = true;
	}
    //if there's a mark acces then repeat them
	if (isset($num)){
		for ($i=0;$i<$num-1;$i++){
			$res.= $marks;
		}
	}
    //place the line
	$res.= $line;
	
	return $res;
}

//encapsulates a bunch of lines
function dfwiki_parser_default_close_group_bis ($line,$p1='',$p2='',$p3=false){
	global $parser_vars;

	if ($p3===false){
		$marks = $p1;
		$subs = $p2;
	}else{
		$marks = $p2;
		$subs = $p3;
		$num = $p1;
	}
	$subs = (is_array($subs))? $subs : array ($subs,$subs);
	$marks = (is_array($marks))? $marks[0] : $marks;
	
	//initialize parse_vars[list]
	if (!isset($parser_vars['enc_group'])) $parser_vars['enc_group'] = array();
	if (!isset($parser_vars['enc_group'][$marks])){
		//indica si estem dins del grup
		$parser_vars['enc_group'][$marks] = false;
	}
	
	$res = '';
	//si cal, arraquem el grup
	if ($parser_vars['enc_group'][$marks]){
		$res = $subs[1];
		$parser_vars['enc_group'][$marks] = false;
	}
	//posem la linia
	$res.= $line;
	
	return $res;
}

function dfwiki_parser_default_table_bis ($parts,$marks,$subs='') {
	global $parser_vars;
	//initialize parse_vars[list]
	if (!isset($parser_vars['table'])){
		$parser_vars['table'] = array();
		$parser_vars['table']['find'] = array();
		$parser_vars['table']['num'] = 0;
		//indica si estem en una taula
		$parser_vars['table']['intable'] = false;
	}

	if (!is_array($subs)) $subs = array ($subs,$subs);
	
    //if no type is set, just do it
	if (!in_array($marks,$parser_vars['table']['find'])){
		$parser_vars['table']['find'][] = $marks;
	}
	
    //if we're at the first symbol then set num to 0
	if ($parser_vars['table']['find'][0] == $marks){
		$parser_vars['table']['num'] = 0;
	}
	
	//detectar si tenim alguna taula o no dins la l�nia
	if (!is_array($parts)){
		if ($parser_vars['table']['num'] > -1) $parser_vars['table']['num']++;
	} else {
		$parser_vars['table']['num'] = -1;
	}
	
	$res = '';
    //if num equals -1 means it's line ending. If-2 means it's already written
	if ($parser_vars['table']['num'] < 0){
		//Open up the tableif needed
		if ($parser_vars['table']['num'] == -1){
			if (!$parser_vars['table']['intable']){
				$res = '<table align="center" width="80%"  class="generalbox" border="0" cellpadding="0" cellspacing="0">
					<tr><td bgcolor="#ffffff" class="generalboxcontent">
					<table width="100%" border="0" align="center"  cellpadding="5" cellspacing="1" class="generaltable" >';
				$parser_vars['table']['intable'] = true;
			}
			
			//write the line
			$res.='<tr>';
			$res.=dfwiki_parser_default_array_encapsule_bis ($parts,$marks,$subs);
			$res.='</tr>';
			//set it written
			$parser_vars['table']['num']=-2;
		} else {
			//if the line is already weritten then just return the string
			$res = (is_array($parts)) ? implode ('',$parts) : $parts;
		}
	} else{
		//if there's an equal num type we then know we're not in a table
		if (count ($parser_vars['table']['find']) == $parser_vars['table']['num']){
			//close the table if needed
			if ($parser_vars['table']['intable']){
				$res = '</table></td></tr></table>';
				//$res = '</table>';
				$parser_vars['table']['intable'] = false;
			}
			//set the parts
			$res.= $parts;
		}else{
			//keep searching
			$res = $parts;
		}
	}
	return $res;
}

function dfwiki_parser_default_list_bis ($inside,$max,$marks,$subs) {
	global $parser_vars;
	
	//initialize parse_vars[list]
	if (!isset($parser_vars['list'])){
		$parser_vars['list'] = array();
		$parser_vars['list']['find'] = array();
		$parser_vars['list']['num'] = 0;
		//insert last level
		$parser_vars['list']['lastlevel'] = 0;
		//indica quin tipus de nivell hi ha obert (etiqueta per tancar)
		$parser_vars['list']['listtype'] = array();
		//etiqueta per als elements
		$parser_vars['list']['listelem'] = array();
	}

	if (!is_array($subs)) $subs = array ($subs,$subs);
	
	//si no tenim enregistrat el tipus, l'enregistrem
	if (!in_array($marks,$parser_vars['list']['find'])){
		$parser_vars['list']['find'][] = $marks;
	}
	
	//si estem al primer s�mbol posem num a 0
	if ($parser_vars['list']['find'][0] == $marks){
		$parser_vars['list']['num'] = 0;
	}
	
	//detects id there's a table inline
	if ($max==0){
		if ($parser_vars['list']['num'] > -1) $parser_vars['list']['num']++;
	} else {
		$parser_vars['list']['num'] = -1;
	}
	
	$res = '';
	//if num = -1 then a row has been found. If num = -2 it's already written
	if ($parser_vars['list']['num'] < 0){
		//open the table id needed
		if ($parser_vars['list']['num'] == -1){
			
			//open up to the level
			if ($parser_vars['list']['lastlevel'] != $max){
				//should either open or close levels
				$inc = ($parser_vars['list']['lastlevel']>$max)? -1 : 1;
				while ($parser_vars['list']['lastlevel'] != $max){
					if ($inc<0){
						//close
						$res.=$parser_vars['list']['listtype'][$parser_vars['list']['lastlevel']-1];
					} else {
						//open
						$res.= $subs[0];
						$parser_vars['list']['listtype'][$parser_vars['list']['lastlevel']] = $subs[1];
					}

					$parser_vars['list']['lastlevel'] = $parser_vars['list']['lastlevel']+$inc;
				}
			}
			$res.='<li>'.$inside;
			//indicates the lines is written
			$parser_vars['list']['num']=-2;
		} else {
			$res = $inside;
		}
	} else{
		if (count ($parser_vars['list']['find']) == $parser_vars['list']['num']){
			//close all levels
			if ($parser_vars['list']['lastlevel'] > 0){
				$inc = ($parser_vars['list']['lastlevel']>$max)? -1 : 1;
				while ($parser_vars['list']['lastlevel'] > 0){
					//close levels
					$res.=$parser_vars['list']['listtype'][$parser_vars['list']['lastlevel']-1];
					$parser_vars['list']['lastlevel']--;
				}
			}
		}
		$res.=$inside;
	}
	return $res;
}

//this function creates de url to a link
function dfwiki_sintax_create_internal_link_bis (&$linktext) {
	global $parser_logs,$CFG, $courseid, $wikiid, $entryid, $pagename;
	$res = '';
	
	//separate type link from link text
	$parts = explode (":",$linktext);
	
	if (count($parts)==1){
		$linktype = 'internal';
		$linkname = $parts[0];
	}else{
		$linktype = $parts[0];
		$linkname = $parts[1];
	}
	echo $linktype." - ".$linkname;
	
	switch ($linktype){
		case 'internal': //normal internal links
			//separate linktext into pagename and text
			$parts = explode ("|",$linkname);
			
			if (count($parts)==1){
				$linkpage = $parts[0];
				$linktext = $parts[0];
			}else{
				$linkpage = $parts[0];
				$linktext = $parts[1];
			}
			
			$cleanpagename = clean_filename($linkpage);
			
	        if (dfwiki_page_exists($linkpage)){
    			//if the page already exists
    			$res = '['.dfwiki_view_page_url($linkpage).']';
    		}else{
    			//to create the page
    			$res = '<b><u>'.$linktext.'</u></b> ['.dfwiki_view_page_url($linkpage).']';
    		}
			
			//save link into log
			if (!isset($parser_logs['internal'])) $parser_logs['internal'] = array();
			if (!in_array($linkpage,$parser_logs['internal'])) $parser_logs['internal'][] = $linkpage;
			
			break;
			
			
		case 'user':
			$res = dfwiki_get_user_info ($linkname,25);
			break;
		
		case 'attach':
			//get url extension
			$res = "[internal://$linkname]";
    		break;
		default: //error
	}
	
	return $res;
}

//this function creates de url to a link
function dfwiki_sintax_create_external_link_bis (&$linktext) {
	
    global $CFG;

    $res = '';
	
	//if text doesn't start with http://, return the internal link.
	if (stripos ($linktext,'://')===false) {
		return dfwiki_sintax_create_internal_link_bis ($linktext);
	}
	//separate type link from link text
	$parts = explode (" ",$linktext,2);
	
	if (count($parts)==1){
		$linkurl = $parts[0];
		$linkname = $parts[0];
	}else{
		$linkurl = $parts[0];
		$linkname = $parts[1];
	}
	
	//get url extension
	$parts = explode ('.',$linkurl);
	$extension = $parts[count($parts)-1];
	
	//analize if it's an image
	$extensions = array (
						'image' => array ('jpg','jpeg','gif','bmp','png'),
						'flash' => array ('swf')
					);

	$type='';				
	foreach ($extensions as $typ => $ext){
		if (in_array($extension,$ext)){
			$type = $typ;
		}
	}
	
	
	switch ($type){
		case 'image':
			$res = '<img src="'.$linkurl.'" alt="'.$linkname.'">';           			
			break;
		case 'flash':
			//get size from $link name
			$parts = explode(' ',$linkname);
			
			if (count($parts)!=2){
				echo 'mal<hr>';
				$parts = array ('320','240');
			} else {
				$parts = array (trim($parts[0]),trim($parts[1]));
				if (strlen($parts[0])!=strspn($parts[0], '0123456789') && strlen($parts[1])!=strspn($parts[1], '0123456789')){
					echo 'mal2 '.strlen($parts[0]).' '.strspn($parts[0], '0123456789').'<hr>';
					$parts = array ('320','240');
				}
			}
			$res = '<object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="'.$parts[0].'" height="'.$parts[1].'">
					<param name="movie" value="'.$linkurl.'" />
					<param name="quality" value="high" />
					<embed src="'.$linkurl.'" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="'.$parts[0].'" height="'.$parts[1].'"></embed>
				</object>';
			break;
		default:
			$res = "[$linkurl]";
			break;
	}
	
	return $res;
}

function dfwiki_page_exists($name) {
	global $wikiid, $groupid;
	
	return get_field('wiki_pages_old','pagename','dfwiki',$wikiid,'groupid',$groupid);
}

function dfwiki_get_real_pagename ($name, $wikiid, $groupid=null, $ownerid=null) {
	    $select = "syn='" . addslashes($name) . "' AND dfwiki=$wikiid";
        if (isset($groupid)) {
            $select .= " AND groupid=$groupid";
        }
        if (isset($ownerid)) {
            $select .= " AND ownerid=$ownerid";
        }

	    //watch in synonymous
	    if ($synonymous = get_record_select('wiki_synonymous_old', $select)) {	
	        //if there's synonymous search for the original
	        return $synonymous->original;
	    }
		//if isn't a synonymous it will be an original or an uncreated page.
	    return $name;
}

//this function returns a linkable information of a user with it's image
function dfwiki_get_user_info ($user, $size=0){
    global $CFG,$courseid;

    $info = get_record('user', 'username', $user);
    //get user id and image
    if ($info){
        $picture = print_user_picture($info->id, $courseid, $info->picture, $size, true,true);
        $text = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$info->id.'">'.fullname($info).'</a>';

    }else{
        $text = '<u>'.$user.'</u>';
        $picture = '';
    }

    //build url
    $res = $text.' '.$picture;
    return $res;
}

function dfwiki_view_page_url($pagename, $anchor='', $anchortype=0) {
    global $content, $wikiid, $groupid, $userid;
    
    $module = get_record('modules', 'name', 'wiki');
    $coursemodule = get_record('course_modules', 'instance', $wikiid, 'module', $module->id);
    
    // support page synonyms
    $realpagename = dfwiki_get_real_pagename($pagename, $wikiid, $groupid, $userid);

    if ($realpagename != $pagename){
    	$newurl = "$pagename | $realpagename"; //[synonym | pagename]
    } else {
    	$newurl = $pagename;
    }

    return $newurl;
}

?>
