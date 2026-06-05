Menu uses set of items and the template to display it, so it is possible to have several menus which uses the same set of items but different templates.

Menu is any set of some links or actions, for example menu can be links in site's footer or actions in profile view.

## DB Structure

![UNA Menu DB Structure](images/dev-menu-db-structure.png)

## Creating the Menu object

**1.** Add record to `sys_objects_menu` table:

* **object** - name of the menu object, in the format: vendor prefix, underscore, module prefix, underscore, internal identifier or nothing; for example: `bx_groups_actions` - actions menu in group view.
* **title** - name of the menu, displayed in the studio menu builder.
* **set_name** - name of items' set.
* **module** - the module this menu belongs to.
* **template_id** - the template to use for menu displaying, this is id from `sys_menu_templates` table.
* **deletable** - it determines if menu can be deleted from the studio menu builder.
* **active** - it is possible to disable particular menu, then it will not be displayed.
* **override_class_name** - user defined class name which is derived from `BxTemplMenu`.
* **override_class_file** - the location of the user defined class, leave it empty if class is located in system folders.

Menu templates are stored in `sys_menu_templates` table:

* **id** - template id.
* **template** - template file.
* **title** - template title to display in the studio menu builder.

All menu templates iterate through `bx_repeat:menu_items` and use the following template variables for each menu item: `__link__`, `__target__`, `__onclick__`, `__title__`, `__class_add__`.

**2.** Add an empty menu set to `sys_menu_sets` table (if you want to use new set of items for created menu):

* **set_name** - the set name.
* **module** - the module this set belongs to.
* **title** - name of the set, displayed in studio menu builder.
* **deletable** - it determines if the set can be deleted from menu builder.

**3.** Add menu items to the set by adding records to `sys_menu_items` table:

* **set_name** - the set name this item belongs to.
* **module** - the module this item belongs to.
* **name** - name of the item (not displayed to the end user), unique in the particular set.
* **title** - menu item title to display to the end user, please note that some templates can still display            menu as icons without text titles.
* **link** - menu item URL.
* **onclick** - menu item onclick event.
* **target** - menu item target.
* **icon** - menu item icon, please note that some templates can still display menu as text without icons.
* **visibile_for_levels** - bit field with set of member level ids. To use member level id in bit field - the     level id minus 1 as power of 2 is used, for example:
    * user level id = 1 -> 2^(1-1) = 1
    * user level id = 2 -> 2^(2-1) = 2
    * user level id = 3 -> 2^(3-1) = 4
    * user level id = 4 -> 2^(4-1) = 8
* **active** - it is possible to disable particular menu item, then it will not be displayed.
* **order** - menu item order in the particular set.

**4.** Display Menu.

Use the following sample code to display menu:

```php
    bx_import('BxTemplMenu');
    $oMenu = BxTemplMenu::getObjectInstance('sample_menu'); // 'sample_menu' is 'object' field from 'sys_objects_menu' table.
    if ($oMenu)
        echo $oMenu->getCode; // display menu
```

But in most cases you don't need to use above code to display menu, menu objects are integrated into [[Pages|Dev-Pages]] - there is special `menu` page block type for it.