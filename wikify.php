<?php

// this was taken from my Sharepoint-to-MediaWiki repo
class PostProcess {

	static public function process ($old_text) {

		// replace HTML breaks with double newlines
		$new_text = str_replace("<br />", "\n\n", $old_text);
		
		// remove weird question marks
		$new_text = str_replace("�", "", $new_text);
				
		$replaces = array(
			"/<\/font>/i" => "",
			
			"/<font[^>]*>/i" => "",

			"/(\[\/INTERNAL-WIKI-LINK)(\S*)(\s+)([^\]]*)(\])/" => '[[\4]]',

			"/%20/" => " ",
			
			// match /style="..."/ where "..."=anything except double quotes
			'/style="[^\"]*"/i' => "",

			// match {|
			'/{\|/' => "{| class=\"wikitable\" ",

			// remove single lines of bold text, where text less than 60 characters
			"/(?:^|\n)\s*[\<u>]*(?:'''|''\s*'''|'''\s*'')\s*([^\n^']{0,60})(?:'''|''\s*'''|'''\s*'')[<\/u>]*\s*\n/i" =>
			"\n\n" . '=== \1 ===' . "\n", // replace with level 3 heading

			"/\n[^\S\r\n]+/u" => "\n",		
			
			"/(\r?\n){4,}/" => "\n\n\n",

			"/&amp;/" => "&",
			
			"/[^\S\r\n]{2,}/u" => ' ',

			"/·/u" => '*',
			
			"/<\/span>/i" => "",

			"/<span[^>]*>/i" => "",

			"/<\/div>/i" => "",

			"/<div[^>]*>/i" => "",

		);
				
		$new_text = preg_replace(array_keys($replaces), array_values($replaces), $new_text);
				
		return $new_text;
		
	}

	static public function wingdings_to_unicode ($text) {

		$conversions = array(

			// right arrow
			"&#61664;" => "&#8680;",
			"&#61672;" => "&#8680;",
			"&#61680;" => "&#8680;",

			// left arrow
			"&#61663;" => "&#8678;",
			"&#61671;" => "&#8678;",
			"&#61679;" => "&#8678;",

			// up arrow
			"&#61665;" => "&#8679;",
			"&#61673;" => "&#8679;",
			"&#61681;" => "&#8679;",

			// down arrow
			"&#61666;" => "&#8681;",
			"&#61674;" => "&#8681;",
			"&#61682;" => "&#8681;",

			// NW arrow
			"&#61667;" => "&#11009;",
			"&#61685;" => "&#11009;",
			"&#61675;" => "&#11009;",

			// NE arrow
			"&#61668;" => "&#11008;",
			"&#61676;" => "&#11008;",
			"&#61686;" => "&#11008;",

			// SW arrow
			"&#61669;" => "&#11011;",
			"&#61677;" => "&#11011;",
			"&#61687;" => "&#11011;",

			// SE arrow
			"&#61670;" => "&#11010;",
			"&#61678;" => "&#11010;",
			"&#61688;" => "&#11010;",

			// right-left arrow
			"&#61683;" => "&#11012;",

			// up-down arrow
			"&#61684;" => "&#8691;",

			// checkmark
			"&#61692;" => "&#10003;",

			// x-mark;",
			"&#61691;" => "&#10007;",

			// x-box;",
			"&#61693;" => "&#9746;",

			// checked-box;",
			"&#61694;" => "&#9745;",
			
		);

		foreach($conversions as $wingding => $unicode) {
			$text = str_replace($wingding, $unicode, $text);
		}
		
		return $text;
		
	}
	
}

// defaults:
$keepfiles = false;



$soffice = "C:/Program Files (x86)/LibreOffice 4/program/soffice.exe";

if ( count($argv) < 2 ) {
	die("please specify filename");
}
$file_arg_index = count($argv) - 1;

// don't start with zero (is just script name)
// don't go to final arg, since that's the file to be wikified
for($i=1; $i<$file_arg_index; $i++) {
	switch(trim($argv[$i])) {
		case "--keepfiles":
			$keepfiles = true;
			break;
		default:
			echo "\nOption " . trim($argv[$i]) . " is not valid";
	}
}

$file = trim( $argv[$file_arg_index] );

// if file doesn't start with a letter and a colon (i.e. a full path with drive letter) 
if ( preg_match('/[^\s]\:/', $file) == 0 ) {
	$file = getcwd() . '/' . $file;
}

$filename = basename($file);
$filebase = explode('.', $filename)[0];

$dir = dirname($file);
$tmp = $dir . '/tmp';

if ( ! file_exists($file) ) {
	die('no such file');
}

if (is_dir($tmp)) {
	die('./tmp already exists');
}

mkdir($tmp);

shell_exec("\"$soffice\" --headless --convert-to html:HTML -outdir \"$tmp\" \"$file\"");

$html_file = $tmp . '/' . $filebase . '.html';
$new_html_file = $tmp . '/' . $filebase . '.unicode.html';

file_put_contents($new_html_file, PostProcess::wingdings_to_unicode(file_get_contents($html_file)) );

$initial_wiki_file = $tmp . '/' . $filebase . '.wiki';

shell_exec("html2wiki --dialect MediaWiki \"$new_html_file\" > \"$initial_wiki_file\"");

$final_file = $dir . '/' . $filebase . '.wiki';

file_put_contents(
	$final_file,
	PostProcess::process(file_get_contents($initial_wiki_file))
);

if ( ! $keepfiles ) {
	unlink($html_file);
	unlink($new_html_file);
	unlink($initial_wiki_file);
	rmdir($tmp);
}

if ( file_exists($tmp) ) {
	$tmp_time = $tmp . '_' . $filebase . '_' . time();
	rename($tmp, $tmp_time);
	echo "\"$tmp\" could not be removed. Renamed to \"$tmp_time\"";
}
