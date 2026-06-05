**During writing the code always keep in mind the following things:**
- this is Open Source product and the code is visible to anyone, we have to always show quality, readable, consistent code. In the future your code can be used for copy&paste, and be the base for other modules.
- check if your code have no unused parts, so it will not confuse other developers.
- make sure that the code is executed the most effective way, since UNA can be used for heavy loaded sites.
- don't write duplicate code, because in this case it will be difficult to change and support such code in the future.
- code must work in different server environments, such as different OS(Windows, Linux, etc) and Web-servers (Apache, Nginx), some PHP modules maybe disabled or even can't be added.

### Common mistakes:

1) Every commit must be atomic and include short description of the committed code and number of issue in the repository in the form of #123.

2) The name of the module in every file should be named without spaces, if readable module name contain spaces it can be added after the name without spaces, for example:
```
@defgroup    QuoteOfTheDay Quote of the Day
```

3) Make sure that the comment in the beginning of every file is correct. In many cases files are copied from other modules and this comment is left unchanged.

4) In the working repository module version must have DEV postfix, and for UNA modules module version number matches major UNA version, for example 9.0.0.DEV. Also compatibility in working repository must be  9.0.x. Compatibility and version is changed when module is released.

5) Make sure that the code is written using UNA code convention - https://github.com/unaio/una/wiki/Code-Convention.

6) Don't leave unnecessary commented code.

7) To get content of the URL, use `bx_file_get_contents` instead of `file_get_contents`.

8) Name `check*` function based on the checked action.

9) It's better to define module name in install.sql (`SET @sName`) in the beginning of the file.

10) Be careful with grid objects, it can be accessed directly, so it's important to set correct `visible_for_levels` field in `sys_objects_grid` table.

11) Make sure that the sum of widths of fields in `sys_grid_fields` table is 100%.

12) Write descriptive comment for every `service*` method, example of comments for `service*` methods can be seen in `BxEventsModule.php` file in Events module.

13) If you use some of **Base** classes for your module, make sure that all classes are derived from the classes of the same base module, if there is no reason to do it otherwise.

14) Don't add new classes to `default.less` file, if adding is necessary discuss with other team members.

15) Don't create indexes longer than **191** for `utf8mb4` fields, they will not work on InnoDB, as an example the following code will not work properly:
```
CREATE TABLE `tablename` (
  `name` varchar(255) character set utf8mb4 collate utf8mb4_unicode_ci NOT NULL,
   ...
  INDEX `name` (`name`)
)
```
You need to limit field length or index length:
```
  ...
  INDEX `name` (`name`(191))
  ...
```
Be especially careful with indexes for multiple fields, sum of all fields must not be greater than 191.

16) Don't use references for method's arguments in modules **Template** class.   
For example instead of such method:
```php
function getLotsPreview($iProfileId, &$aLots, $bShowTime = true)
```
Try to compose the code, that will work without the reference:
```php
function getLotsPreview($iProfileId, $aLots, $bShowTime = true)
```
