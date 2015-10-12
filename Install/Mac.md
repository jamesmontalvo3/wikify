Installing on Mac
=================

Wikify has three requirements:

1. PHP 5.4+
2. Perl (TBD version) with html2wiki installed from CPAN
3. LibreOffice

### PHP
Most Macs already have PHP installed. Try running `php --version` in your terminal. If you don't have it installed find a tutorial. There are many.

### Perl and html2wiki
Most Macs also have Perl. Try `perl --version`. If you don't have it find a tutorial.

You probably also have CPAN installed, but it may need to be configured. Type "CPAN" into your terminal. If it is installed you will be asked "Would you like to configure as much as possible automatically?". Type "yes" and hit enter. You may be asked several other questions. See TBD for more info on configuration. If you do not have CPAN installed see TBD for more info.

Once CPAN is installed it is time to install the HTML::WikiConverter module. Unfortunately at this writing (and for the last year or so) this installation fails due to some tests failing that I don't understand. This occurs on both Mac and Windows. To force the install despite the failed tests, enter CPAN (type "cpan" into terminal), then do the following commands:

```bash
force install HTML::WikiConverter
force install HTML::WikiConverter::MediaWiki
force install Module::Implementation
```

Once the install completes (hopefully it works!), type "exit" and hit enter. Then in terminal type "html2wiki" to test it out.

### LibreOffice
Install [LibreOffice](http://www.libreoffice.org/). Once installed you'll need to find the path to the "soffice" command. On mac it is "/Applications/LibreOffice.app/Contents/MacOS/soffice". Add this to your PATH variable or create a config.json file (see config.example.json).

### Install Wikify on Mac and *nix
Clone this repository into /usr/local (so the path to wikify.php will be /usr/local/wikify/wikify.php)

```bash
cd /usr/local
git clone https://github.com/jamesmontalvo3/wikify
```

Make a symbolic link from /usr/local/bin/wikify to the wikify.php script.

```bash
ln -s /usr/local/wikify/wikify.php /usr/local/bin/wikify
```

Make the wikify.php script executable:

```bash
sudo chmod +x /usr/local/wikify/wikify.php
```

Try wikifying a document

```bash
cd ~/Documents
wikify test.odt
```

If that doesn't work, do this, too:

```bash
sudo chown root wikify
```
