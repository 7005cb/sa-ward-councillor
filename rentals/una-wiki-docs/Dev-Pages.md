Pages API allows to display pages which are built from studio. The main difference is that old system had column based pages, but the new one has layout based pages.

The new system has the following main features:

* **Layouts** - page can have any structure, not just columns.
* **SEO** - page can have own SEO options, like meta tags and meta keywords, as well as instructions for search bots.
* **Cache** - page can be cached on the server.
* **Access control** - page access can be controlled using member levels.

### **DB Structure**

![UNA Pages DB Structure](images/dev-pages-db-structure.png)

### **Creating the Page object**

**1.** Add record to `sys_objects_page` table:

* **object** - name of the page object, in the format: vendor prefix, underscore, module prefix, underscore, internal identifier or nothing; for example: `bx_profiles_view` - profile view page.
* **title** - name to display as page title.
* **module** - the module this page belongs to.
* **cover** - cover visibility.
* **cover_image** - cover image ID.
* **type_id** - page type, this is ID of the record from `sys_pages_types` table.
* **layout_id** - page layout to use, this is ID of the record from `sys_pages_layouts` table.
* **submenu** - submenu object to show as page menu.
* **visibile_for_levels** - bit field with set of member level ids. To use member level id in bit field - the level id minus 1 as power of 2 is used, for example:

    * user level id = 1 -> 2^(1-1) = 1
    * user level id = 2 -> 2^(2-1) = 2
    * user level id = 3 -> 2^(3-1) = 4
    * user level id = 4 -> 2^(4-1) = 8
* **visibile_for_levels_editable** - it determines if `visibile_for_levels` field is editable from page builder, visibility options can be overriden by custom class and shouldn't be editable in this case.
* **url** - the page url, if it is static page.
* **meta_description** - meta description of the page.
* **meta_keywords** - meta keywords of the page.
* **meta_robots** - instructions for search bots.
* **cache_lifetime** - number of seconds to store cache for.
* **cache_editable** - it determines if cache can be edited from page builder.
* **deletable** - it determines if page can be deleted from page builder.
* **override_class_name** - user defined class name which is derived from `BxTemplPage`.
* **override_class_file** - the location of the user defined class, leave it empty if class is located in system folders.

Page can select appropriate menu automatically if `module` and `object` fields in `sys_objects_page` table are matched with `module` and `name` fields in `sys_menu_items` table.

**2.** Add page blocks to `sys_pages_blocks` table:

* **object** - page object name this block belongs to.
* **cell_id** - cell number in page layout to place block to.
* **module** - module name this block belongs to.
* **title** - block title.
* **designbox_id** - design box to use to diplay page block, it is id of the record from `sys_pages_design_boxes` table.
* **visibile_for_levels** - bit field with set of member level ids. To use member level id in bit field - the level id minus 1 as power of 2 is used, for example:

    * user level id = 1 -> 2^(1-1) = 1
    * user level id = 2 -> 2^(2-1) = 2
    * user level id = 3 -> 2^(3-1) = 4
    * user level id = 4 -> 2^(4-1) = 8

* **type** - block type:
    * **raw** - HTML block, displayed in page builder as HTML textarea.
    * **html** - HTML block, displayed in page builder as visual editor, like TinyMCE.
    * **lang** - translatable language string, displayed in page builder as editable language key.
    * **image** - just an image, displayed in page builder as HTML upload form.
    * **rss** - RSS block, displayed in page builder as editable URL to RSS resource, along with number of displayed items.
    * **menu** - menu block, displayed as menu selector.
    * **service** - to display block content, the provided service method is used.
* **content** - depending on `type` field:
    * **raw** - HTML string.
    * **html** - HTML string.
    * **lang** - language key.
    * **image** - image id in the storage and alignment (left, center, right) for example: `36#center`
    * **rss** - URL to RSS with number of displayed items, for example: `http://www.example.com/rss#4`
    * **menu** - menu object name.
    * **service** - serialized array of service call parameters: `module` - module name, `method` - service method name, `params` - array of parameters.
* **text** - text to index for search functionality.
* **text_updated** - unix timestamp when text was updated.
* **deletable** - is block deletable from page builder.
* **copyable** - is block can be copied to any other page from page builder.
* **order** - block order in particular cell.

Block design boxes are stored in `sys_pages_design_boxes` table:

* **id** - consistent id, there are the following defines can be used in the code for each system block style:
    * **0 - BX_DB_CONTENT_ONLY** - design box with content only - no borders, no background, no caption.
    * **1 - BX_DB_DEF** - default design box with content, borders and caption.
    * **2 - BX_DB_EMPTY** - just empty design box, without anything.
    * **3 - BX_DB_NO_CAPTION** - design box with content, like `BX_DB_DEF` but without caption.
    * **10 - BX_DB_PADDING_CONTENT_ONLY** - design box with content only wrapped with default padding - no borders, no background, no caption; it can be used to just wrap content with default padding.
    * **11 - BX_DB_PADDING_DEF** - default design box with content wrapped with default padding, borders and caption.
    * **13 - BX_DB_PADDING_NO_CAPTION** - design box with content wrapped with default padding, like `BX_DB_DEF` but without caption.
* **title** - block name which is displayed in studio, describes block styles.
* **template** - template name to use to display page block.

**3.** Display the Page.

Use the following sample code to display page:

```php
    bx_import('BxDolPage');
    $oPage = BxDolPage::getObjectInstance('sample'); // 'sample' is 'object' field from 'sys_objects_page' table, it automatically creates instance of default or custom class by object name
    if ($oPage)
        echo $oPage->getCode(); // print page
```