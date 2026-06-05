## UNA directories structure

`/cache/` - cache files which should be unaccessible from the web (DB queries cache, language files cache, etc)  

`/cache_public/` - cache files accessible from the web (CSS and JS cache files)  

`/inc/` - system core code  

`/inc/header.inc.php` - main config file which is created during installation (contains DB access credentials)  

`/inc/classes/` - system core classes  

`/inc/js/` - system core JS code

`/install/` - files to perform installation (should be deleted after install)

`/install/sql/system.sql` - main DB installation file with DB structure and some necessary data

`/install/sql/addon.sql` - after installation DB file which adds some data entered during install to the DB (admin account, site title, version, email, etc)

`/install/cmd.php` - php script to perform installation from command line (type `/path/to/php cmd.php -h` for available options)

`/logs/` - logs folder, modules should log to this folder as well

`/modules/` - modules folder (language files and templates as well), particular module is located in the folder `/modules/vendor_name/module_name`

`/periodic/cron.php` - cron jobs PHP script to run in 1 minute interval

`/plugins/` - 3rd-party libraries which should be unaccessible from the web (usually PHP libraries)

`/plugins_public/` - 3rd-party libraries accessible from the web (usually JS libraries)

`/storage/` - storage folder, all modules must store any files in this folder using Storage objects (usually user uploaded files)

`/studio/` - site control panel files

`/template/` - base template 

`/tmp/` - temporary files

### Main differences in directories structure from Dolphin 7

1. Template folder contains only base files, particular template files (which can override base files) are located in particular template module, the same is for template files in module
2. Language file is now located in particular language module, generated language cache file is in `/cache/` folder
3. Modules may not store files directly in own folder structure, instead they must use system `/logs/`, `/tmp/` folders and **Storage** object
4. No more `/administration/` folder, instead there is `/studio/`, which contains functionality related to site configuration only, other administration functionality is moved to the user side controlled by membership levels, there are build-in **Moderator** and **Administrator** ACL levels

### Module structure

`/classes/` - module classes

`/install/` - module installation files

`/install/langs/` - modules language files, english language file must be present at least

`/install/sql/` - module SQL files: install.sql, uninstall.sql, enable.sql, disable.sql

`/install/config.php` - module config to perform installation

`/install/installer.php` - class with additional installation instructions

`/template/` - module base template files

Additionally:

- when module is template then there is `/data/template/` folder with template for `system` and `studio` (see `uni` module as an example)
- when module is language then there is `/data/langs/` folder with language XML files (see `english` module as an example)

Actually module structure is very similar to the module structure in Dolphin 7, with the following main differences:

1. Module may not store any files in own folders, instead module should use system folders `/tmp/`, `/logs/`, `/cache/`, `/cache_public/`, `/storage/`; please note that `/storage/` folder may not be used directly, it should be used using **Storage** object class only
2. Template and language are now modules as well
3. There are base modules: `/modules/base/profile` - for profile based modules, `/modules/base/text` - for text based modules, `/modules/base/general` - general classes for other base modules or any module
4. There are 4 SQL files in each module for install/uninstall and activation/deactivation, upon module deactivation/activation - module content is preserved, but all other settings and data is reset
5. Module template contains only base template files, so folder was renamed from `/templates/` to `/template/` and no `/base/` folder inside
6. Languages are now in XML format



