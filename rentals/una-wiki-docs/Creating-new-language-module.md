By default UNA supports English and Russian languages. If you want to translate to to another language then the best way is to create new language module. The easiest way to do this is to clone Russian module. 

1. Copy Russian module from `/modules/boonex/russian/` to your own directory, for example `/module/vendor/french/`, where vendor is your or organization name. 

2. Edit `modules/vendor/french/install/config.php` file and change all possible values, particularly:

    - `'name' => 'vend_fr'` - for 2 letters code use [ISO 639-1 standard](https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes) and some abbreviation for your or your organization name
    - `'title' => 'French'`
    - `'version' => '1.0.0'`
    - `'vendor' => 'Vendor'`
    - `'compatible_with' => array('9.0.0')` - specify particular version here
    - `'home_dir' => 'vendor/french/'`
    - `'home_uri' => 'fr'`
    - `'db_prefix' => 'bx_fr_'` - database tables prefix, it's not used but needed anyway
    - `'class_prefix' => 'VendFr'`
    - `'language_category' => 'Vendor French'`
    - comments at the top of the page

3. Edit `modules/vendor/french/install/installer.php` file by changing the comment at the top of the page and class name, use `class_prefix` specified in point 2:

    `class VendFrInstaller extends BxDolStudioInstaller`

4. Edit `modules/vendor/french/install/langs/en.xml` file, by changing **only the following**:
    - change all language key prefixes to your own, for example from `_bx_rsn_` to `_vend_fr_`
    - change translations from `Russian` to `French`

5. Edit SQL files in `modules/vendor/french/install/sql/` folder by changing all occurrences of the following:
    - `bx_ru` to `vend_fr` - the `name` value from the point 2
    - `boonex/russian/` to `vendor/french/` - the `home_dir` value from the point 2
    - `Russian` to `French` - the `title` value from the point 2
    - `'ru'` to `'fr'` - the `home_uri` value from point 2
    - `_bx_rsn_` to `_vend_fr_` - like in point 4

6. Change images in `modules/vendor/french/template/images/icons/` folder, use the following guide and PSD template to create new one, please use flag picture - [instructions on how to create module icon](https://github.com/unaio/una-vendor-test/wiki/Module-Icon)

7. It's recommended to translate from English, to do it go to `modules/vendor/french/data/langs/` folder, then:   
    **a)** in `system` folder: remove 'ru.xml' file    
    **b)** in `system` folder: copy `modules/boonex/english/data/langs/system/en.xml` file from English    
    **c)** in `system` folder: then rename it from `en.xml` to `fr.xml`    
    **d)** in `system` folder: edit `fr.xml` by changing the header:    

        `<resources name="fr" flag="fr" title="French">`

         and translate the values:

        `<string name="_lang_key"><![CDATA[Translate only here]]></string>`

    **e)** repeat the same for each module folder, but copy original English translation from `install/langs` folder in the module. For example for **Posts** module copy and rename `modules/boonex/posts/install/langs/en.xml` file to `bx_posts/fr.xml`   

    **NOTE:** Most of these steps can be automated with the special script which copies all english xml files to one place, type in UNA root folder - `phing package_all_langs` ([phing](https://www.phing.info/) binary must be installed). This way you will have all language files in `packages/en` folder. Then copy files from `packages/en` folder to `modules/vendor/french/data/langs/` folder. Then you will need to perform c), d), e) steps from above list.

    **NOTE:** to get translations for all modules refer to the [UNA GitHub repository](https://github.com/unaio/una).

    **NOTE:** it's possible to rename all files in `data/langs` folder using the following MAC OSX command: `find . -name en.xml | while read f; do mv -v "$f" "$(echo "$f" | sed -e 's/en.xml/fr.xml/')"; done`

    **NOTE:** it's possible to search and replace strings in `data/langs` folder files using the following MAC OSX commands:   
    `find . -name it.xml -exec sed -i '' 's/name="en"/name="fr"/g' {} \;`    
    `find . -name it.xml -exec sed -i '' 's/flag="gb"/flag="fr"/g' {} \;`   
    `find . -name it.xml -exec sed -i '' 's/title="English"/title="French"/g' {} \;`

8. When new version of UNA is released you need to update module and add/remove/change some language keys. The list of changed language keys can be found in English module update files, also you can also use it as a template to create own language module update. For example the here is [the list of changes from 9.0.0 to 9.0.1 for English language](https://github.com/unaio/una/blob/master/modules/boonex/english/updates/9.0.0_9.0.1/install/langs/en.xml).   
How to write upgrade script for the module refer to the following doc:   
https://github.com/unaio/una/wiki/Creating-app-auto-update-script

9. To package the module, run the following from your UNA root folder (it requires build.xml file which is in the UNA repository):
`/path/to/phing package_module -Dvendor=your_vendor_folder_name -Dmodule=your_module_folder_name`   
ZIP file for the module will appear in `/packages/` folder in your UNA root directory.