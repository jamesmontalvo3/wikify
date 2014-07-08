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
			
			"/<font[^>]+>/i" => "",

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

			"/<span[^>]+>/i" => "",

		);
				
		$new_text = preg_replace(array_keys($replaces), array_values($replaces), $new_text);
				
		return $new_text;
		
	}

}


$soffice = "C:/Program Files (x86)/LibreOffice 4/program/soffice.exe";

$file = trim( $argv[1] );

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
$initial_wiki_file = $tmp . '/' . $filebase . '.wiki';

shell_exec("html2wiki --dialect MediaWiki \"$html_file\" > \"$initial_wiki_file\"");

$final_file = $dir . '/' . $filebase . '.wiki';

file_put_contents(
	$final_file,
	PostProcess::process(file_get_contents($initial_wiki_file))
);

unlink($html_file);
unlink($initial_wiki_file);
rmdir($tmp);