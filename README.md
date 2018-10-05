# nDrive
Easy way to Upload directly to Google Drive using PHP. Demo: https://drive.nstudio.pw

## Requirement
- PHP 5 and above
- MySQL (To store settings and files' ID if you want to manage them)
- Google Drive SDK access
- Google Drive User Account

## Installation
- Install Drive PHP SDK: https://github.com/googleapis/google-api-php-client
- Change credentials value in settings table of Database.
- Open {host-name}/token.php and follow steps to set Token.

** Token will be refreshed automatically in runtime.
