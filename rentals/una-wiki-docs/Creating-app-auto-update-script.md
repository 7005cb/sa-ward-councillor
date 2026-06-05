Each App in UNA can be updated separately. A special update script should be created for this purpose. To be auto-applied such scripts are downloaded with Studio -> Apps Market and put in `modules/[vendor]/[app]/updates/` folder. 
Usually a main folder of the update script has "version from" and "version to" in its name, for example **update_100_110**. The update script should have 2 subfolders: **install** and **sources**. 
* **install** folder (`update_100_110/install/`) is the update installation folder, where all installation files are located, like SQL files and language files. It's a mandatory folder.
* **source** folder (`update_100_110/source/`) is a folder with files and folders which should be uploaded in app's folder. So, files and folders structure should match app's one. You may read more about app's file structure ​[here](https://github.com/unaio/una/wiki/Directories-structure#module-structure). This folder can be empty if nothing was changed in app's files.
Installation folder (**install**) may have the following files and folders:
* **config.php** file (`update_100_110/install/config.php`) is a mandatory file which guides the update installer. We will take a closer look at this later.
* **installer.php** file (`update_100_110/install/installer.php`) is another mandatory file which has an update installer class. You can add some custom installation scripts and override default behaviour here.
* **langs** folder (`update_100_110/install/langs/`) is an optional folder which should contain updates for the app's languages. Each language update must be a XML file (`update_100_110/install/langs/en.xml`) with the name of two letters of the language code. We will take a closer look at an example of such language update file later.
* **sql** folder (`update_100_110/install/sql/`) is an optional folder which should contain update scripts for app's database: **install.sql** and/or **enable.sql**. 
* **install.sql** file (`update_100_110/install/sql/install.sql`) is an optional file. It should contain update queries for the changes which were done in **install.sql** file of the app. If nothing were changed there then this file may not exist.
* **enable.sql** file (`update_100_110/install/sql/enable.sql`) is an optional file. It should contain update queries for the changes which were done in **enable.sql** file of the app. If nothing were changed there then this file may not exist.


## Configuration file.

As was mentioned before app's auto-update script has its own **config.php** file. It has similar structure as the app's config file. The main differences between app's and update's configuration files are listed below. 

Update's configuration file has the following additional parameters
1. Config file should specify the version which it can be applied to. Also it should specify the version which you'll have after the update was applied. 
```
'version_from' => '1.0.0',
'version_to' => '1.1.0',
```
2. Home directory and URI of the module which the update relates to. 
```
'module_dir' => '[vendor]/[app]/',
'module_uri' => '[app]',
```
3. A list of app's files which should be deleted during the update. Files should be specified with related file names related to the root folder of the app.
```
'delete_files' => array(
    'js/delete_me.js',
    'template/delete_me.html',
),
```

Update's configuration file doesn't have the following parameters in comparison with app's config file. 
1. Everything related to app's deactivation and uninstallation. 
```
'uninstall' => array (
	...
),
'disable' => array (
	...
),
'disable_failed' => array (
	...
),
```
2. Different configuration params related to the work of the app like the following:
```
'dependencies' => array(
	...
),
'relation_handlers' => array(
	...
),
```


## Language file.

In comparison with app's language file update's language file may have three types of tags. It's needed to let the script know which language strings should be added (**string_add**), which should be updated (**string_upd**) and which should be removed (**string_del**).
```
<?xml version="1.0" encoding="utf-8"?>
<resources name="en" flag="gb" title="English">
	<string_add name="_my_app_txt_add_me"><![CDATA[I'm a new key]]></string_add>

	<string_upd name="_my_app_txt_update_me"><![CDATA[I'll be updated]]></string_upd>
	
	<string_del name="_my_app_txt_delete_me"><![CDATA[I'll be deleted]]></string_del>
</resources>
```

## Market app

Refer to [Market App docs](https://github.com/unaio/una/wiki/Market) for the additional info on how to upload update.