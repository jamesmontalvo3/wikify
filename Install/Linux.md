Installing on Windows
=====================

## Prerequisites

* PHP (If you don't have it, use [XAMPP](https://www.apachefriends.org/index.html))
* [Strawberry Perl](http://strawberryperl.com/) (not the version of Perl that comes with XAMPP)
* [LibreOffice](https://www.libreoffice.org/download/libreoffice-fresh/)
* [Git](https://git-scm.com/)
* [Sublime Text](http://www.sublimetext.com/3), [Notepad++](https://notepad-plus-plus.org/) or another quality text editor

## Download Wikify

Go to your desktop, right-click anywhere, and click on "git bash" in the menu. Paste the following into your command line and hit enter.

```
cd /C
git clone https://github.com/jamesmontalvo3/Wikify
```

If your command line does not allow copy/paste, click on the icon in the top-left of the command line window so a menu appears, and click "properties". In the "options" tab check "QuickEdit Mode". Click OK. You should now be able to paste by right-clicking anywhere on the command line.

## Add programs to PATH

1. Click on the Start menu
2. Right-click on "computer" and select "properties"
3. Click "advanced system settings"
4. Find the "environment variables" button
5. Find the "PATH" variable, and click "edit"
6. Copy the contents of the PATH variable into Sublime Text or Notepad++
7. Add the following as required (separate each with a semicolon, do not inlude newlines):

```
C:\xampp\php
C:\Program Files (x86)\LibreOffice 4\program
C:\Wikify
```

Paste your new PATH back into the edit dialog and save.

## Install HTML::WikiConverter

Open a command window (in Start menu type "cmd" and hit enter), then run the following commands:

```
perl -MCPAN -e 'force install HTML::WikiConverter'
perl -MCPAN -e 'force install HTML::WikiConverter::MediaWiki'
perl -MCPAN -e 'force install Module::Implementation'
```

## Test Wikify

Create a folder on your desktop and put a Word document in it. Then hold shift and right-click inside the directory. Select "open command window here". In the command window type:

```
wikify YourDocumentName.docx
```

If your document has spaces in the name, do:

```
wikify "Your document name.docx"
```

If you have Windows configure to not show you document extensions it can be difficult to see if it's a .doc or .docx. To show file extensions go to Control Panel, Appearance and Personalization, then Folder Options. Ont he View tab, under Advanced Settings, uncheck the "Hide extensions for known file types" box.
