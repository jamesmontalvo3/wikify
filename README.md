Wikify
======

Convert Office to MediaWiki files. And perhaps in the future non-office files (namely PDFs).

This script is way-way-way alpha. Don't use it. It relies heavily on LibreOffice and the html2wiki Perl package.

## Installation
Wikify has three requirements:
1. PHP 5.4+
2. Perl (TBD version) with html2wiki installed from CPAN
3. LibreOffice

### PHP
If you're trying to wikify documents for MediaWiki you probably already have PHP installed. Sorry, I won't go into details on that at this point.

### Perl and html2wiki
If you are on Linux or Mac you probably already have Perl installed. Confirm by running the following in your terminal:

```bash
perl --version
```

If you do not have Perl installed see TBD for more info.

You probably also have CPAN installed, but it may need to be configured. Type "CPAN" into your terminal. If it is installed you will be asked "Would you like to configure as much as possible automatically?". Type "yes" and hit enter. You may be asked several other questions. See TBD for more info on configuration. If you do not have CPAN installed see TBD for more info.

Once CPAN is installed it is time to install the HTML::WikiConverter module. Unfortunately at this writing (and for the last year or so) this installation fails due to some tests failing that I don't understand. This occurs on both Mac and Windows. To force the install despite the failed tests, enter CPAN (type "cpan" into terminal), then do the following:

```bash
force install HTML::WikiConverter
```

Once the install completes (hopefully it works!), type "exit" and hit enter. Then in terminal type "html2wiki" to test it out.

### LibreOffice
Install [LibreOffice](http://www.libreoffice.org/). Once installed you'll need to find the path to the "soffice" command. On mac it is "/Applications/LibreOffice.app/Contents/MacOS/soffice". Add this to your PATH variable or create a config.json file (see config.example.json).

## Usage
```bash
php /path/to/wikify.php /path/of/file.doc
```