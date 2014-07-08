<?php

// this was taken from my Sharepoint-to-MediaWiki repo
class PostProcess {

	static public function process ($old_text) {

		// replace HTML breaks with double newlines
		$new_text = str_replace("<br />", "\n\n", $old_text);
		
		// remove weird question marks
		$new_text = str_replace("�", "", $new_text);
		
		$patterns = array();
		$replaces = array();
		
		$patterns[0] = "/<\/font>/i";
		$replaces[0] = "";

		$patterns[1] = "/<font[^>]+>/i";
		$replaces[1] = "";

		// $patterns[2] = "/(\[/INTERNAL-WIKI-LINK)([^\s*])(\s)([^\]*])(\])/";
		// $replaces[2] = "[[\4]]";
		$patterns[2] = "/(\[\/INTERNAL-WIKI-LINK)(\S*)(\s+)([^\]]*)(\])/";
		$replaces[2] = '[[\4]]';

		$patterns[3] = "/%20/";
		$replaces[3] = " ";
		
		$patterns[4] = '/style="[^\"]*"/i'; // match /style="..."/ where "..."=anything except double quotes
		$replaces[4] = "";

		$patterns[5] = '/{\|/'; // match {|
		$replaces[5] = "{| class=\"wikitable\" ";

		// remove single lines of bold text, where text less than 60 characters
		$patterns[6] = "/(?:^|\n)\s*[\<u>]*(?:'''|''\s*'''|'''\s*'')\s*([^\n^']{0,60})(?:'''|''\s*'''|'''\s*'')[<\/u>]*\s*\n/i";
		$replaces[6] = "\n\n" . '=== \1 ===' . "\n"; // replace with level 3 heading

		$patterns[7] = "/\n[^\S\r\n]+/u"; 
		$replaces[7] = "\n";		
		
		$patterns[8] = "/(\r?\n){4,}/"; 
		$replaces[8] = "\n\n\n";

		$patterns[9] = "/&amp;/"; 
		$replaces[9] = "&";
		
		// $patterns[9] = "/[^\S\r\n]{20}/"; 
		// $replaces[9] = ' ';
		
		// $patterns[6] = "/\n{3,20}/"; 
		// $replaces[6] = "\n\n"; // replace with level 3 heading
		
		$new_text = preg_replace($patterns, $replaces, $new_text);
		
		// echo "<h1>Old Text</h1>";
		// echo "<textarea type='text' style='width:600px;height:300px;'>$old_text</textarea>";

		// echo "<h1>New Text</h1>";
		// echo "<textarea type='text' style='width:600px;height:300px;'>$new_text</textarea>";
		$patterns = array();
		$replaces = array();

		$patterns[0] = "/[^\S\r\n]{2,}/u"; 
		// $patterns[0] = "/\s{2,}/u"; 
		$replaces[0] = ' ';

		$patterns[1] = "/·/u";
		$replaces[1] = '*';
		
		$patterns[2] = "/<\/span>/i";
		$replaces[2] = "";

		$patterns[3] = "/<span[^>]+>/i";
		$replaces[3] = "";

		$new_text = preg_replace($patterns, $replaces, $new_text);

		
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