# Creating a new language module

The purpose of Language Apps is to translate all customizable interface text strings into a different language. Such text strings include buttons, menu items, alerts, forms, page block titles, email templates and so forth. Every installed language in UNA is under control of the [Polyglot](https://github.com/unaio/una/wiki/Polyglot) system app where you can add new language keys or edit the existing ones. By default, the English and Russian language apps are available in UNA.

## Creating a copy of the English app

You can use the Polyglot app to make small edits, but when it comes to editing hundreds of language strings, working in Polyglot will become tedious and time consuming. In this case, creating a copy of an existing language app will be a more straightforward process. Follow these steps to create a copy of the English app:

1. Use your favorite FTP manager to navigate to the English app location, which is **modules/boonex/english**. Download the whole folder to your local computer. Alternatively, you can download the zipped version of the app from the [UNA Market](https://una.io/download-product/english) (if you know the exact app version), and then unzip it.

2. Edit the file **install/config.php**. Here you will have to change some member values of the `$aConfig` array.
All the suggested values here are optional. The goal here is to use something different and unique.
   * `vendor` -> change the value to your own name, for example `John Doe`
   * `name` -> change the value to `johndoe_en`
   * `title` -> change the value to `English (Custom)`
   * `help_url` -> change the value to your own URL, for example `http://mysite.com`
   * `home_dir` -> change the value to `johndoe/english/`
   * `home_uri` -> change the value to `en1`
   * `db_prefix` -> change the value to `johndoe_eng_`
   * `class_prefix` -> change the value to `JohnDoeEng`
   * `language_category` -> change the value to `John Doe English`
   
   Save the file.

3. Edit the file **install/langs/en.xml**:
   * inside the **resources** tag change the following attribute value:
      * `title` -> change the value to `John Doe English`
   * `_bx_eng_wgt_cpt` -> change to `_johndoe_eng_wgt_cpt`
   * `_bx_eng_stg_cpt_type` -> change to `_johndoe_eng_stg_cpt_type`
   * `_bx_eng_stg_cpt_category_system` -> change to `_johndoe_eng_stg_cpt_category_system`

   In other words, you will need to replace the default db_prefix `bx_eng_` with your custom one `johndoe_eng_`.

   You may also want to change the string values inside the `![CDATA[]]` tags in this file but it is not necessary.

   Save the file.
   
4. Now you need to edit the module's utility SQL files which will be used to install, uninstall, enable and disable your module in the Studio. Edit the following files and their contents:
   * **install/sql/install.sql**
      * search for `bx_en` and replace all the occurences with `johndoe_en`
      * search for `'en'` and replace all the occurences with `'en1'`	  
      * search for `modules/boonex/english` and replace all the occurences with `modules/johndoe/english`
   * **install/sql/uninstall.sql**, **install/sql/enable.sql**, **install/sql/disable.sql**
      * search for `bx_en` and replace all the occurences with `johndoe_en`
      * search for `'en'` and replace all the occurences with `'en1'`

   Also change the module's installation file **install/installer.php**
      * replace `BxEngInstaller` with `JohnDoeEngInstaller`
	  
   Save the files.

5. Now it's time to edit the language strings themselves. Rename the file **data/langs/system/en.xml** to **en1.xml** and edit it:
   * inside the **resources** tag change the following attribute values:
      * `name` -> change the value to `en1`
      * `title` -> change the value to `John Doe English`
   * change the values for the language strings you wish to edit. Change only the values of `<![CDATA[]]>` tags. For example:

      old string: `<![CDATA[Message was successfully sent.]]>`

      new string: `<![CDATA[Hooray! Message was successfully sent.]]>`
   
   Save the file.
   
6. If you want to provide translations for modules other than system, create folders in the folder **data/langs** with the names corresponding to the modules names, for example **data/langs/bx_accounts**. Copy the English language file for the module and put it inside the newly created folder with the new name **en1**.
Module's language files are located in the folder **install/langs** inside a module's folder, for example **modules/boonex/accounts/install/langs/en.xml**.
Return to the step 5) to change the contents of the new file.

7. Now you can upload the results on the server.
   * Create the folder **johndoe** inside the *modules* folder
   * Upload the whole **english** folder from your local computer to the **johndoe** folder on the server.
   
8. Go to **Studio -> Apps Market -> Downloaded** and install your new language app from there.

## Creating a new language app

When creating a new language app, you can slightly modify the steps used for the custom English language with some additions. Follow the steps below using the French language file as an example.

1. Use your favorite FTP manager to navigate to the English app location, which is **modules/boonex/english**. Download the whole folder to your local computer. Alternatively, you can download the zipped version of the app from the [UNA Market](https://una.io/download-product/english) (if you know the exact app version), and then unzip it. Rename the folder _english_ to _french_.

2. Edit the file **install/config.php**. Here you will have to change some member values of the `$aConfig` array.
All the suggested values here are optional. The goal here is to use something different and unique
   * `vendor` -> change the value to your own name, for example `Vendor`
   * `name` -> change the value to `vendor_fr`
   * `title` -> change the value to `French`
   * `help_url` -> change the value to your own URL, for example `http://mysite.com`
   * `home_dir` -> change the value to `vendor/french/`
   * `home_uri` -> change the value to `fr`
   * `db_prefix` -> change the value to `vendor_fr_`
   * `class_prefix` -> change the value to `VendorFr`
   * `language_category` -> change the value to `French`
   
   Save the file.

3. Edit the file **install/langs/en.xml** (use some editor which supports UTF-8 without BOM, such as [Notepad++](https://notepad-plus-plus.org/downloads/)):
   * inside the **resources** tag change the following attribute value:
      * `title` -> change the value to `French`
   * `_bx_eng_wgt_cpt` -> change to `_vendor_fr_wgt_cpt`
   * `_bx_eng_stg_cpt_type` -> change to `_vendor_fr_stg_cpt_type`
   * `_bx_eng_stg_cpt_category_system` -> change to `_vendor_fr_stg_cpt_category_system`

   In other words, you will need to replace the default db_prefix `bx_eng_` with your custom one `vendor_fr_`.

   Also change the string values inside the `![CDATA[]]` tags in this file, for example:
   
   old string: `<![CDATA[French]]>`

   new string: `<![CDATA[Français]]>`

   Save the file.
   
4. Now you need to edit the module's utility SQL files which will be used to install, uninstall, enable and disable your module in the Studio. Edit the following files and their contents:
   * **install/sql/install.sql**
      * search for `bx_en` and replace all the occurences with `vendor_fr` - for 2 letters code use [ISO 639-1 standard](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes) and some abbreviation for your or your organization name
      * search for `'en'` and replace all the occurencies with `'fr'`
      * search for `modules/boonex/english` and replace all the occurences with `modules/vendor/french`
   * **install/sql/uninstall.sql**, **install/sql/enable.sql**, **install/sql/disable.sql**
      * search for `bx_en` and replace all the occurences with `vendor_fr`
      * search for `'en'` and replace all the occurences with `'fr'`

   Also change the module's installation file **install/installer.php**
      * replace `BxEngInstaller` with `VendorFrInstaller`
	  
   Save the files.

5. Now it's time to edit the language strings themselves. Rename the file **data/langs/system/en.xml** to **fr.xml** and edit it (use some editor which supports UTF-8 without BOM, such as [Notepad++](https://notepad-plus-plus.org/downloads/)):
   * inside the **resources** tag change the following attribute values:
      * `name` -> change the value to `fr`
      * `title` -> change the value to `French`
      * `flag` -> change the value to `fr`
   * change the values for the language strings you wish to edit. Change only the values of `<![CDATA[]]>` tags. For example:

      old string: `<![CDATA[Message was successfully sent.]]>`

      new string: `<![CDATA[Le message a été envoyé avec succès.]]>`
   
   Save the file.
   
6. If you want to provide translations for modules other than system, create folders in the folder **data/langs** with the names corresponding to the modules names, for example **data/langs/bx_accounts**. Copy the English language file for the module and put it inside the newly created folder with the new name **fr**.
A module's language files are usually located in the folder **install/langs**.
Return to step 5) to change the contents of the new file.

   **NOTE**: Most of these steps can be automated with a special script which copies all english xml files to one place. In your server's console, navigate to the UNA installation folder and type **phing package_all_langs** ([phing](https://www.phing.info/) binary must be installed). This way you will have all of the language files in the **packages/en** folder. Then copy files from the **packages/en** folder to the **french/data/langs/** folder. Then you will need to repeat step 6) to translate all of the files.
   
   **NOTE**: to get translations for all modules refer to the [UNA GitHub repository](https://github.com/unaio/una).
   
   **NOTE**: it's possible to rename all of the files in **data/langs** folder using the following MAC OSX command:
   
   `find . -name en.xml | while read f; do mv -v "$f" "$(echo "$f" | sed -e 's/en.xml/fr.xml/')"; done`
   
   **NOTE**: it's possible to search and replace strings in the **data/langs** folder files using the following MAC OSX commands:
   
   `find . -name fr.xml -exec sed -i '' 's/name="en"/name="fr"/g' {} \;`
   
   `find . -name fr.xml -exec sed -i '' 's/flag="gb"/flag="fr"/g' {} \;`
   
   `find . -name fr.xml -exec sed -i '' 's/title="English"/title="French"/g' {} \;`

7. Change images in the **template/images/icons** folder by using the following guide and PSD template to create a new one. Please use flag picture - [instructions on how to create module icon](https://github.com/unaio/una-vendor-test/wiki/Module-Icon).
   
8. Now you can upload the results on the server.
   * Create the folder **vendor** inside the *modules* folder
   * Upload the whole **french** folder from your local computer to the **vendor** folder on the server.
   
9. Go to **Studio -> Apps Market -> Downloaded** and install your new language app from there.

10. When a new version of UNA is released, you need to update the module and add/remove/change some language keys. The list of changed language keys can be found in the English module update files. You can also use it as a template to create own language module update. For example, here is [the list of changes from 9.0.0 to 9.0.1 for English language](https://github.com/unaio/una/blob/master/modules/boonex/english/updates/9.0.0_9.0.1/install/langs/en.xml).
For how to write an upgrade script for the module, refer to the following doc:
[Creating app auto update script](https://una.io/wiki/Creating-app-auto-update-script).

11. To package the module, run the following from your UNA root folder (it requires _build.xml_ file which is in the UNA repository):   

    `/path/to/phing package_module -Dvendor=your_vendor_folder_name -Dmodule=your_module_folder_name`

   A ZIP file for the module will appear in the **/packages/** folder in your UNA root directory.