#!/bin/bash
#
# Setup wikify on a Debian-based machine

# update software
apt-get update

# install stuff like "make"
apt-get install build-essential

# LibreOffice required for HTML sanitization
apt-get install libreoffice

# Install PHP and a bunch of modules we probably don't need
apt-get install php5 php5-mcrypt php5-cli php5-common php5-curl php5-gd php5-json php5-readline php5-imagick php5-imap php5-intl php5-mysql php5-odbc php5-pspell php5-recode php5-sqlite php5-tidy php5-xmlrpc php5-xsl 

# perl is hopefully installed at this point. Use CPAN to install some modules.
perl -MCPAN -e 'force install HTML::WikiConverter'
perl -MCPAN -e 'force install HTML::WikiConverter::MediaWiki'
perl -MCPAN -e 'force install Module::Implementation'

# Get wikify
cd /usr/local
git clone https://github.com/jamesmontalvo3/wikify
cd wikify

# symlink wikify and make it executable
ln -s ./wikify.php /usr/local/bin/wikify
chmod +x /usr/local/wikify/wikify.php

echo "Setup complete. You can remove this install script now."
echo "Do `wikify MyWordDocument.docx` to run wikify"
