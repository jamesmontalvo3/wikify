// FIXME: Check if "soffice" in path
// FIXME: all regex in wikitextPostProcess need to be made JS style

const exec = require('child_process').exec;
var cheerio = require('cheerio'),
	path = require('path'),
	fs = require('fs');


// get file name from argument
var filepath = path.join( process.cwd(), process.argv[2] );

// generate file path parts
var ext = path.extname( filepath );
var basename = path.basename( filepath, ext );
var dirname = path.dirname( filepath );
var tmpdir = path.join( dirname, "tmp" );
var html;
var htmlFilePath = path.join( tmpdir, basename + '.html' );
var $;




var Wikify = {
	init : function () {

		// check if file exists and temp directory doesn't
		Wikify.isFile(
			filepath,
			function() { // is file callback
				Wikify.isDirectory(
					tmpdir,
					function() { // temp directory exists
						console.error("Temporary directory already exists: " + tmpdir);
						process.exit();
					},
					function() { // no temp directory
						fs.mkdir( tmpdir, Wikify.convertToHTML );
					}
				);
			},
			function() { // not file callback
				console.error(filepath + " is not a file");
				process.exit();
			}
		);

	},

	convertToHTML : function () {

		// run LibreOffice "soffice" command to convert Word to HTML
		exec( "soffice --headless --convert-to html:HTML --outdir \"" + tmpdir + "\" \"" + filepath + "\"", function (error, stdout, stderr) {
			if (error) {
				console.error('exec error: ' + error);
				process.exit();
				return;
			}
			if ( stdout.trim() == "" ) {
				stdout = "<none>\n\n";
			}
			if ( stderr.trim() == "" ) {
				stderr = "<none>\n\n";
			}
			console.log('soffice stdout: ' + stdout);
			console.log('soffice stderr: ' + stderr);

			fs.readFile( htmlFilePath , function (err, data) {
				if (err) throw err;
				html = Wikify.wingdingsToUnicode( data.toString('utf8') );
				Wikify.manipulateDOM();
			});
		});

	},

	manipulateDOM : function () {

		$ = cheerio.load( html );

		// get images like: data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAXoAAAF6CAIAAACYwgTHAAAdb0lEQVR4nO2dS6smNduFo772L3RiexgorSBIO2gQRRqUxgNK0yCKCh5Q3II4ceREBHEiiEPBiYITwYk4caK0fvXufJ03u6qeqlRy5866k3UNNtu2
		$('img').each( function(i,e) {
			var image = Wikify.decodeBase64Image( $(this).attr('src') );
			var imageFileName = basename + i + "." + image.extension;
			var imageFilePath = path.join( tmpdir, imageFileName );
			$(this).attr('src', 'File:' + imageFileName );

			if ( ['png','jpg','jpeg','gif'].indexOf(image.extension) !== -1 ) {
				fs.writeFile( imageFilePath, image.data, function(err) {
					if (err) throw err;
					console.log( "File saved" );
				});
			}
			else {
				console.error('Image error: ' + image.type + ' is not a supported image type');
			}
		});

		// Strip <a> tags that don't have an href attribute (kills parsoid)
		$('a').each(function(i,e) {
			if ( ! $(this).attr('href') ) {
				$(this).replaceWith( $(this).text() );
			}
		});

		Wikify.stripTags( ['font','div','span'] );

		html = $.html(); // put cheerio back into text

		Wikify.convertToWikitext( html );

	},

	decodeBase64Image: function (dataString) {
		var matches = dataString.match(/^data:([A-Za-z-+\/]+);base64,(.+)$/),
		response = {};

		if (matches.length !== 3) {
			return new Error('Invalid input string');
		}

		response.type = matches[1];
		response.extension = response.type.replace( "image/", "" );
		response.data = new Buffer(matches[2], 'base64');

		return response;
	},

	stripTags : function ( tags ) {

		for( var i=0; i<tags.length; i++ ) {

			$( tags[i] ).each( function(i,e){
				$(this).replaceWith( $(this).text() );
			});

		}

	},

	convertToWikitext : function ( html ) {

		fs.writeFile( htmlFilePath, html, function(err) {
			if (err) throw err;
			console.log( "HTML file saved prior to wikification" );

			exec("node /etc/parsoid/tests/parse.js --html2wt --inputfile=" + htmlFilePath, function (error, stdout, stderr) {
				if (error) {
					console.error('exec error: ' + error);
					process.exit();
					return;
				}
				if ( stderr.trim() == "" ) {
					stderr = "<none>\n\n";
				}
				console.log('parsoid stderr: ' + stderr);

				wikitext = stdout;
				wikitext = Wikify.wikitextPostProcess(wikitext);
				fs.writeFile( path.join( tmpdir, basename + '.wikitext' ), wikitext, function(err) {
					if (err) throw err;
					console.log( "Wikitext file saved!" );
					process.exit();
				});

			});
		});

	},

	wikitextPostProcess : function (text) {

		replaces = [d

			// replace HTML breaks with double newlines
			{ from: "<br />",
				to: "\n\n" },

			// remove weird question marks
			{ from: "�",
				to: "" },

			// // strip trailing and leading font tags
			// { from: "/<\/font>/i",
			// 	to: "" },
			// { from: "/<font[^>]*>/i",
			// 	to: "" },

			// FIXME: how will parsoid play with this?
			{ from: "/(\[\/INTERNAL-WIKI-LINK)(\S*)(\s+)([^\]]*)(\])/",
				to: '[[\4]]' },

			// Change URL encoded spaces to regular spaces
			{ from: "/%20/",
				to: " " },

			// match /style="..."/ where "..."=anything except double quotes
			{ from: '/style="[^\"]*"/i',
				to: "" },

			// make all tables wikitables
			{ from: '/{\|/',
				to: "{| class=\"wikitable\" " },

			// remove single lines of bold text, where text less than 60 characters
			{ from: "/(?:^|\n)\s*[\<u>]*(?:'''|''\s*'''|'''\s*'')\s*([^\n^']{0,60})(?:'''|''\s*'''|'''\s*'')[<\/u>]*\s*\n/i",
				to: "\n\n" + '=== \1 ===' + "\n" }, // replace with level 3 heading

			// ?
			{ from: "/\n[^\S\r\n]+/u",
				to: "\n" },

			// More than 4 newlines reduced to 3 newlines
			{ from: "/(\r?\n){4,}/",
				to: "\n\n\n" },

			// Decode ampersands
			{ from: "/&amp;/",
				to: "&" },

			// ?
			{ from: "/[^\S\r\n]{2,}/u",
				to: ' ' },

			// Change bullets to asterisks
			{ from: "/·/u",
				to: '*' },

			// // Strip closing and leading span tags
			// { from: "/<\/span>/i",
			// 	to: "" },
			// { from: "/<span[^>]*>/i",
			// 	to: "" },

			// //  Strip closing and leading div tags
			// { from: "/<\/div>/i",
			// 	to: "" },
			// { from: "/<div[^>]*>/i",
			// 	to: "" }

		];

		for ( var i=0; i<replaces.length; i++ ){
			text = text.replace(replaces[i].from, replaces[i].to);
		}

		return text;

	},

	wingdingsToUnicode : function (text) {

		conversions = {

			// right arrow
			"&#61664;": "&#8680;",
			"&#61672;": "&#8680;",
			"&#61680;": "&#8680;",

			// left arrow
			"&#61663;": "&#8678;",
			"&#61671;": "&#8678;",
			"&#61679;": "&#8678;",

			// up arrow
			"&#61665;": "&#8679;",
			"&#61673;": "&#8679;",
			"&#61681;": "&#8679;",

			// down arrow
			"&#61666;": "&#8681;",
			"&#61674;": "&#8681;",
			"&#61682;": "&#8681;",

			// NW arrow
			"&#61667;": "&#11009;",
			"&#61685;": "&#11009;",
			"&#61675;": "&#11009;",

			// NE arrow
			"&#61668;": "&#11008;",
			"&#61676;": "&#11008;",
			"&#61686;": "&#11008;",

			// SW arrow
			"&#61669;": "&#11011;",
			"&#61677;": "&#11011;",
			"&#61687;": "&#11011;",

			// SE arrow
			"&#61670;": "&#11010;",
			"&#61678;": "&#11010;",
			"&#61688;": "&#11010;",

			// right-left arrow
			"&#61683;": "&#11012;",

			// up-down arrow
			"&#61684;": "&#8691;",

			// checkmark
			"&#61692;": "&#10003;",

			// x-mark;",
			"&#61691;": "&#10007;",

			// x-box;",
			"&#61693;": "&#9746;",

			// checked-box;",
			"&#61694;": "&#9745;",

		};

		for( var n in conversions ) {
			text = text.replace( n, conversions[n] );
		}

		return text;

	},

	isFile: function ( filepath, isFileCallback, notFileCallback ) {
		fs.lstat( filepath, function( err, stats ) {
			if (err) {
				console.error('lstat error: ' + err);
				process.exit();
				return;
			}
			if ( stats.isFile() ) {
				isFileCallback();
			}
			else {
				notFileCallback();
			}
		});
	},

	isDirectory: function ( path, isDirectoryCallback, notDirectoryCallback ) {
		fs.lstat( filepath, function( err, stats ) {
			if (err) {
				console.error('lstat error: ' + err);
				process.exit();
				return;
			}
			if ( stats.isDirectory() ) {
				isDirectoryCallback();
			}
			else {
				notDirectoryCallback();
			}
		});
	}

};

Wikify.init();
