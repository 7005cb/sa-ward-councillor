**If you have auto-update enabled** then your site should be updated within 24 hours.

**If you are on manual updates**, then

- Go to **Studio > Dashboard** then click/tap **Upgrade**

- After your system is updated (usually within a minute) go to **Studio > Apps Market > Updates** and apply all available modules updates. 

- After all updates are applied, try to reload the page to see if there are more than 1 update for the same module and apply it again until there are no more updates available on this page.

---

You can enable/disable automatic updates in **Studio > Settings > System > Site Settings > Enable auto-update**.  

If you have very minor modifications and you want update script to overwrite it then you can enable the following setting:   
**Studio > Settings > System > Site Settings > Force auto-update, even if some files were modified**  
it will make updates to work if less than 5% of files are modified. 

If **more than 5% of files are modified** then it's better to merge changes manually to avoid problems with the update. 

Another option if **more than 5% of files are modified** is to overwrite any changes you've made in source code, then you need to change the following line in `inc/classes/BxDolInstallerUtils.php` file:
```php
define('BX_FORCE_AUTOUPDATE_MAX_CHANGED_FILES_PERCENT', 0.05);
```
to:
```php
define('BX_FORCE_AUTOUPDATE_MAX_CHANGED_FILES_PERCENT', 1.00);
```
Then try to upgrade UNA or modules as usual.

---

There are several update channels - **stable** and **beta**. By default **stable** is used. It's possible to change in Studio > Developer > Settings > Update channel. If Developer module can't be installed then it's possible to change update channel by running the following SQL query:
```sql
UPDATE `sys_options` SET `value` = 'beta' WHERE `name` = 'sys_upgrade_channel';
```
Then clear DB cache in Studio > Dashboard > Cache block