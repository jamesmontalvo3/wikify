#!/bin/bash
#
# Setup wikify on a Debian-based machine

# LibreOffice required for HTML generation, tidy for further sanitation
# http://www.techrepublic.com/blog/linux-and-open-source/how-to-convert-doc-and-odf-files-to-clean-and-lean-html/
yum install -y libreoffice libreoffice-headless tidy

# Get wikify
cd /usr/local
git clone https://github.com/jamesmontalvo3/wikify
cd wikify

# symlink wikify and make it executable
ln -s ./wikify.js /usr/local/bin/wikify
chmod +x /usr/local/wikify/wikify.js

echo "Setup complete. You can remove this install script now."
echo "Do `wikify MyWordDocument.docx` to run wikify"
