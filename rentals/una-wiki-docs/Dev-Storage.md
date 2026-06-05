The aim of storage objects in UNA is to unify files storage. As the result there are many advantages:

* files can be stored as on localhost as on remote storage, for example Amazon S3
* all files are in one place and separated from other files, so the data can be organised more easily, for example moved to dedicated disk if there is not enough storage
* simplicity of usage, there are hight level classes to handle all necessary operations, including upload and security
* quotas settings, so you always control how much space you are going to use
* persistent storage; uploaded, but not saved files appear upon page reload or future submission of the same form

## Usage

**Step 1:** Add record to `sys_objects_storage` table, like you doing this for Comments or Voting objects.

* **object** - your storage object name, usually it is in the following format - vendor prefix, underscore, module prefix; for example for BoonEx Forum module it can be bx_forum
* **engine** - storage engine, for now the following engines are supported:  
1.**Local** - local storage, by default files are stored in `/storage/` subfolder in UNA root directory  
2.**S3** - Amazon S3 storage with signature v2 authorisation, files are stored on Amazon S3 compatible storage, you need to point `Access Key`, `Secret Key`, `Bucket` and other options in the settings   
3.**S3v4** - Amazon S3 storage with signature v2 and v4 authorisation, files are stored on Amazon S3 compatible storage, you need to point `Access Key`, `Secret Key`, `Bucket` and other options in the settings
* **params** - custom storage engine params as php serialized string
* **token_life** - life of the security token in seconds for private files
* **cache_control** - control browser cache, allow browser to store files in browser's cache for this number of seconds, to disable browser cache, or let browser to decide on its own set it to 0(zero)
* **levels** - store files in subfolders, generated from filename; it is useful when there is limit of number of files/folders per directory; for example if level is 2 and file name is abc.jpg then the file will be stored in a/b/abc.jpg folder, set to to 0(zero) to disable this feature
* **table_files** - table where file info is stored, please refer to **step 2** for more details
* **ext_mode** - file extensions restriction mode:  
1.**allow-deny** - allow only file types in ext_allow field and deny all other file types, ext_deny field is ignored.  
2.**deny-allow** - allow all files except the ones specified in ext_deny field, ext_allow field is ignored.  
* **ext_allow** - allowed file extensions, comma separated, it is in effect when ext_mode is allow-deny; example - jpg,gif,png
* **ext_deny** - denied file extensions, comma separated, it is in effect when ext_mode is deny-allow; example - exe,com,bat
* **quota_size** - storage engine quota in bytes, the summary of all uploaded files can not be bigger than this number
* **current_size** - current storage engine usage, the sum of all uploaded file sizes
* **quota_number** - max number of files allowed in this storage engine
* **current_number** - current number of files in this storage engine
* **max_file_size** - max file size for this storage engine, please note that other server settings are used if they are less than this setting option
* **ts** - unix timestamp of the last file upload

**Step 2:** Create table for files.

```php

CREATE TABLE `my_sample_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `profile_id` int(10) unsigned NOT NULL,
  `remote_id` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `mime_type` varchar(128) NOT NULL,
  `ext` varchar(32) NOT NULL,
  `size` int(11) NOT NULL,
  `added` int(11) NOT NULL,
  `modified` int(11) NOT NULL,
  `private` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `remote_id` (`remote_id`)
);

```

You need to enter this table name in `table_files` field in `sys_objects_storage` table, mentioned in **step 1**. 
The files will be added to this table automatically, all you need is to save `id` from this table, so you can refer to the file by the `id`. 
It is not recommended to change this table, it is better to create another table which will be connected with this one by file `id`.

**Step 3:** Handling upload.

Sample HTML form:

```php

<form enctype="multipart/form-data" method="POST" action="store_file.php">
    Choose a file to upload: 
    <input name="file" type="file" />
    <br />
    <input type="submit" name="add" value="Upload File" />
</form>

```

Add server code in store_file.php:

```php

require_once('./inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . "languages.inc.php");
require_once(BX_DIRECTORY_PATH_INC . "params.inc.php");
require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");

bx_import('BxDolStorage');
$oStorage = BxDolStorage::getObjectInstance('my_module'); // create storage object instance, 'my_module' is value of 'object' field in 'sys_objects_storage' table

if (isset($_POST['add'])) { // if form is submitted
        $iId = $oStorage->storeFileFromForm($_FILES['file'], true, 0); // store file from submitted HTML form, 'file' is input name with field, true means store file as private, 0 is profile id 
        if ($iId) { // storeFileFromForm returns file id, not false value means operation is successful.
            // save $iId somewhere, so you can refer to the file after 
            $iCount = $oStorage->afterUploadCleanup($iId, $iProfileId); // since we saved $iId, we remove it from the orphans list, so it will not appear on the form next time (persistent storage) 
            echo "uploaded file id: " . $iId . "(deleted orphans:" . $iCount . ")";
        } else {
            // something went wrong - print error code
            echo "error uploading file: " . $oStorage->getErrorCode() 
        }
}

```

Please refer to the functions definition for more additional description of functions params.

**Step 4:** Displaying the file.

Use the following code to retrieve saved file. Remember you saved filed id somewhere in the previous step. 
Lets assume that the uploaded file is image, then we can show it using the following code:

```php

require_once('./inc/header.inc.php');
require_once(BX_DIRECTORY_PATH_INC . "languages.inc.php");
require_once(BX_DIRECTORY_PATH_INC . "params.inc.php");
require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");

bx_import('BxDolStorage');
$oStorage = BxDolStorage::getObjectInstance('my_module'); 

$iId = 1234; // since you've saved it somewhere in the previous step, you can retrieve it here 

echo "Uploaded image: <img src="' . $oStorage->getFileUrlById($iId) . '" />l;";

```

It will show the file, regardless if it is private or public. You need to control it by yourself who will view the file. The difference in viewing private files is that link to the file is expiring after N seconds, you control this period using `token_life` field in `sys_objects_storage` table.

## Conclusion

Using simple high level functions you can implement files storage with all the error checking, security and flexibility. Try to change storage engine from `Local` to `S3` in `sys_objects_storage` table and files will be stored directly on Amazon S3, no other changes are required in your code.

The process can be even more automated using [[Forms|Forms-Builder]] and [[Uploaders|Dev-Uploaders]]!