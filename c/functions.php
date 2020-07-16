<?php

function formatText($text, $date, $maxChar = 0) {

	$text = strip_tags($text);
	$text = html_entity_decode($text);

	$shortcodes = array('%%year%%' => $date->format('Y'),
					    '%%month%%' => $date->format('n'),
					    '%%day%%' => $date->format('j')
				        );

	foreach($shortcodes as $code => $replace){
		$text = str_replace($code, $replace, $text);
	}

	if($maxChar > 0){
	    if(mb_strlen($text) > $maxChar) {
		    $text = substr($text, 0, $maxChar);
		    $text = substr($text, 0, strrpos($text ,' '));
	    }
    }
    return $text;
}