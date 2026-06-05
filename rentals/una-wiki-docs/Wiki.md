Wiki are special types of pages and blocks which can be changed from user side (if permissions allow this). Think of UNA wiki like a regular custom pages and blocks which can be added/changed via Studio > Pages, but with some basic functionality available in user side. 

## General wiki functionality

The simple Wiki functionality is available by default (without installing Wiki module) - it's wiki blocks. You can add wiki block via Studio > Pages, and then change this block from user side - only by operator - so if you have access to Studio, then you can change such wiki blocks too.

## Wiki block actions

Wiki blocks are special types of blocks which save all changes history and support [Markdown syntax](https://github.com/adam-p/markdown-here/wiki/Markdown-Cheatsheet), replacement markers, [macros](https://github.com/unaio/una/wiki/Macros), translations. 
After wiki block is added you can perform the following actions with the block if permissions allow:

![Wiki block actions](images/wiki/block-actions.png)

**Edit action**

![Wiki block edit](images/wiki/block-edit.png)

Edit main language and translations. Main language - is language which was added first, it's always marked with the star. The date near translation shows latest update date, if it's red then translation is considered outdated - main language content update time is newer than translation update time. To edit translation - click on language radio button to switch to this language and edit it.

**Delete version action**

Delete some revision from the history. Please note it shows revisions for current language only, to delete revision for some other language, you need to switch site language.

**Delete block action**

Delete block with all translations and revisions, it can not be undone!

**Translate action**

![Wiki block translate](images/wiki/block-translate.png)

Edit all languages except main language. It similar to edit, but main language editing isn't possible, also main language is shown at the top as read-only field for easier translation. 

**History action**

Show changes history for current language, to see history for other language you need to switch site language.

## Permissions

There are different permissions available. These permissions are different for the system and for Wiki module.
- `Add Page` - ability to add new wiki pages from user side (isn't available for system)
- `Add Block` - ability to add new block (isn't available for system)
- `Edit Block` - ability to edit block content and translations
- `Translate Block` - ability to translate block content, i.e. edit translation for all languages except main language
- `Delete Block` - ability to delete block with all translations and revisions
- `Delete Version` - ability to delete revisions
- `History` - ability to view block content changes history 
- `Unsafe HTML` - ability to use unsafe (`style` and `script`) HTML in block content

By default - system wiki blocks actions are enabled for Operators only, in Wiki module wiki block actions are enabled for Admins and Moderators only. 

## Wiki module

To get more extended wiki functionality - install Wiki module, then more functionality will be available in user side, particularly adding new pages and new wiki blocks. 

To add new page just type new page in the the URL, for example if your site is on `http://example.com/`, then to add new Wiki page type in your browser address string the following URL `http://example.com/wiki/new-page`, where `new-page` is unique identifier of new page. If permissions to add page is enabled for you then it will ask you to create new wiki page.  
![Wiki block translate](images/wiki/add-new-page-1.png)  
Please note that `new-page` must be unique identifier systemwide, including regular pages. For example if `http://example.com/page/about` page already exists then you can't add `http://example.com/wiki/about`.   
If you decide to add page, then you need to enter page title in several languages, which you can switch by clicking on language name with flag.  
![Wiki block translate](images/wiki/add-new-page-2.png)  
When page is added it uses default layout with one cell, changing wiki page layout is possible in Studio > Pages only for now.

Wiki module also allows to add new blocks from user side, if you are on wiki page and `Add Block` permission is enabled for you then you can add wiki blocks to each cell on the page.   
![Wiki block translate](images/wiki/add-new-block.png)  
When block is added it's empty and can be changed by editing it. Added block uses default block's design box - without title, borders and background, but it can be changed in Studio > Wiki > Settings. After block is added block's design box can be changed in Studio > Pages only for now. 



