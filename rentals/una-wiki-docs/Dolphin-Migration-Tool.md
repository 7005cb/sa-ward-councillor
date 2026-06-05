This module allows to transfer content from **Dolphin 7.2.x and upper** versions to UNA site.

### Installation & Configuration
In order to perform the migration, you need to be sure that your **Dolphin script and UNA site** are located on the same server. It is necessary to copy and past media files and to work with database.

**Note:** If you have some changes in original Dolphin's database tables (especially renamed or removed original fields) it may influence on transferred data and migration process.

### Installation:
#### 1. Download **Dolphin migration tool** App in **UNA Apps Market** and install it.
![App Market](http://aqbsoft.com/img/2018-04-12_12-29-43.png)
#### 2. On settings page you may found 3 options:
* If account with the same NickName or Email already exists, then to overwrite its info.
This option is useful if you already have account on current version with the same Nickname as in Dolphin. If you enable this option all existed on UNA accounts will be updated using profiles' info from Dolphin and don't forget that info of your current logged member may be also updated and you can be logged out.

![Set Path](http://aqbsoft.com/img/2018-11-07_22-35-32.png)

* **Use NickName instead of Profile's FullName** - this option allows to set user's **NickNames** instead of **FullNames**.

* **Transfer photo albums even if they are empty** - this option is useful in case if you like to transfer photos albums, but they have no media content, thus only albums' names, creation date and uri will me transferred.

When you set the settings specifically to your needs, go to **Data Transfer** page, where you may see textbox - **Define path to the root Dolphin's folder** and two buttons (Save and Clean), where you should
set the path to the **root of the Dolphin script**. The simplest way to find the path is to open **inc/header.php** file of your Dolphin script (which is located on the same server as UNA) and copy **``` $dir['root'] ```** value, then put it to **Define path to the root Dolphin's folder** text box and save, then you will see the list of the available for trasnfer content in table below.

#### Available content for transfer (18 items):

**Dolphin Data           -> UNA Apps**
* Profiles             -> **Persons App**
* Profile Fields     -> **Persons Fields**
* Blogs         -> **Posts App**
* Groups         -> **Groups App**
* Events         -> **Events App**
* Photos         -> **Albums App**
* Videos         -> **Albums App**
* Messages         -> **Conversation App**
* Forum         -> **Discussions App**
* Polls         -> **Polls App**
* Files         -> **Files App**
* Store          -> **Market App**
* Shoutbox         -> **Jot Messenger**
* Simple Messenger     -> **Jot Messenger**
* Ray Chat          -> **Jot Messenger**
* Timeline          -> **Timeline**
* Quotes          -> **Quote of the day**
* Membership Levels -> **Paid Levels**, **Permissions**

![Content Table](http://aqbsoft.com/img/2018-11-07_22-40-53.png)

### Table columns:
* **Data** - data for transfer.

* **Records number** - maximum records number which can be transferred to **UNA site**.

**Note:** Real transferred number may differ from **Records number**, because there are a few conditions and filters through which each record should be passed prior to transfer.

**For example:** you get differ timeline records, because modules for counted activities are no longer exist for UNA site and can not be transferred from Dolphin. If you try to transfer a member with existed **Nickname**, neither Profile nor its content will be transferred.
 
* **Status** - shows the migration status.
Possible variants: _Not started Yet_, _Already started_, _Finished_, _Error occurred_. The first two variants allow you to run migration again.
* **Actions** - When you start migration for selected Data, new _**special field**_ will be created in the main tables for transferring. This field allows to control already migrated records.

There are two buttons in **Actions column** exist:

1) **Remove additional fields for transfer** - If you click on it, _**special field**_ will be removed from the main table and you will have original table's structure. You may perform this action in case if you like to transfer the all content and would like to remove the module. So, you may clean tables first and then uninstall.

2) **Remove transferred content** - This option allows to remove all records which were transferred for selected Data. It may be useful in case if you want to test how transferred content may look or to remove content due to incorrect transferring and etc...

### How to run migration:
You may select bulk of Data items for transfer using right checkbox and then to press **Start Transfer** button at the very left bottom of the page. When the transfer is finished you will see popup alert with appropriate status. Also each button has confirmation message appeared before execution and green records are appeared as result of the transfer for each line.

**Note:** In process of Data transferring with main content also additional information may be transferred.

**For example:** With media files, albums, groups and etc, also **comments** are transferred, with Profiles records their **friends** and **avatars**, with Groups, Events **participants** and pictures also are transferred. Thus, it may take more time then you expected.

About **Membership levels module** transfer: It allows to transfer all custom membership levels and member's membership information (Date Start, Date End and Transaction ID), but membership actions will not be transferred, because **Una** and **Dolphin** have different levels actions set. It means If you have Gold membership level, it will be transferred and will be set for members who had it on Dolphin, but you need to add membership actions for Gold manually, by default there are 0 actions.

### Recommendation:
If you have many records (for instance **more then 3000 Profiles** or more then **1000 events** with a lot of images), it would be better to perform each transfer separately. It means to select only one item from Transfer list and press **Start Transfer**.
Since data transfer performs in real time and each server has its own time limits for script execution, it may stop working during the transferring process. If you don't get any **bug reports** from **UNA site** or you think transfer takes much more time then it should take, you may try to run transfer again. If the same problem appears, you may check the browser console in order to find the errors or contact us via support tickets.