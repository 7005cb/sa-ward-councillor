There is [UNA Cloud](https://una.io/page/hosting) which allow to start UNA in a few minutes, or you you can proceed with the manual installation on your own hosting:

## STEP 1 - Download
 
[Download the latest UNA package “.zip” archive](https://una.io/page/download-product?id=86) and unzip the package, unless your computer does that automatically.
 
## STEP 2 - Upload to Hosting Server
 
Upload the downloaded and unpacked UNA folder to your hosting server. You would need to use an “ftp client” software (like, say, Transmit, Cyberduck, WinSCP or FileZilla) to upload files via FTP. If you don’t know your hosting server address, username and password, ask your hosting provider customer support. 
 
* Connect to your remote server via FTP.
* Open “public_html” folder.
* Copy contents of the UNA folder you previously downloaded to the “public_html” folder. 
 
NOTE: _If you want to install your site to a subfolder (i.e. mysite.com/community), create a subfolder in “public_html” and copy files to it._
 
NOTE: _Don’t just copy the UNA folder itself. Open it on your computer and copy the folder contents - all the files and folders inside of it to your hosting server._
 
NOTE: _Pay attention to .htaccess file, it maybe hidden on *nix systems and in some FTP clients._

NOTE: _[Rewrite rules for Nginx web-server](https://una.io/page/view-discussion?id=133)._
 
## STEP 3 - Install 
 
Open URL of your site (with sub-folder, if you created one) in your web-browser. Installation page will open automatically. From the installation page, you may either start installation process or perform automatic “server audit".
 
NOTE: _Server audit attempts to check your hosting server for any potential inconsistencies, software incompatibilities or incorrect settings. Should it report any problems, we suggest contacting your hosting provider with a request to resolve those. Just copy the server audit report to them and they would know what to do._
 
### Permissions
 
Installation script checks if your files have correct access permissions. It would show you what is the current status of the files and folders and what it needs to be for UNA to work properly. 
 
To change permissions of files and folders, open your FTP client again and go to the files you previously uploaded, open “info” of the folders and files with incorrect permissions, find “permissions” settings and change them to “writable” or “777”. 
 
NOTE: _Generally you would  need to change permissions of “ffmpeg.exe” to “executable” (or 655). This file is located inside /plugin/ffmpeg/ folder. _
 
NOTE: _Do not change permissions for any files or folders except for those listed in Permissions list of the installation script._
 
Go back to your web-browser with Installation page and click “Refresh” button to re-check permissions. If everything is correct, click “Continue”.
 
### Site Configuration
 
**SITE PATHS**. Generally, you don’t have to change those, unless you know the alternative location of “ImageMagick” software on your server, or unless so advised by your hosting provider.
 
**DB CONFIG**. Create an SQL database, first. Create a new SQL database, or just ask your hosting provider to create it for you. Once created, you would need to know the database name, username, and password.
 
**SITE INFO**. Choose your preferred site name, “no-reply” email address and admin details. Make sure to remember admin password and make it difficult to guess.
 
**KEY AND SECRET**. These numbers are required for UNA to check software version updates, buy and download Apps and check licenses. Just click [get UNA Key And Secret](https://una.io/page/kands-manage) link to get the numbers via your registered UNA account. You can create an account at https://una.io website.

**MODULES**. Finally, some modules, such as Templates and Languages ask which language and template would you like to install by default. You can add more and change them later as well.
 
Click “Submit” button to finalise installation. 
 
## STEP 4 - Finish
 
1. After installation, you will be asked to setup a **cron jobs** command. This is required for your site to be able to do some periodic actions. Copy the command and add it to the cron jobs. You can send the code from the final step of installation to your hosting provider and request it to be added to your site’s cron jobs.
 
2. Delete “install” folder. Using your FTP client, find the “install” folder among the files you previously uploaded and delete it. **This critical for your site security!**
 
3. Go to UNA Studio (yoursite.com/studio), log-in with your admin username and password (the ones you created on Installation stage) and start adding apps, adjusting site settings, setting membership levels and changing permissions. 


## UNA on ubuntu aws  installation
[Click here](https://github.com/unaio/una/wiki/INSTALLATION-ON-UBUNTU-DEBIAN)