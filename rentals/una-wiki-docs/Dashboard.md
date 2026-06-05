Widget Dashboard consist of some parts and provides some useful actions

## Version

Section provides information about :
* 	UNA version
* 	Installation date
* 	Availability of new versions.
If new versions is founded, it’s provides  ability to update to them.

## Space Usage

Section provides information about HDD space usage by installed UNA apps.

![](https://raw.githubusercontent.com/wiki/unaio/una/images/dashboard/001.png)

Mouse over any of their will give detail information.

## Host Tools

Section provides information about Web Server, PHP module and MYSQL status.
When you click on the check mark on the right, a menu is available:

![](https://raw.githubusercontent.com/wiki/unaio/una/images/dashboard/002.png)

### Server audit 

Give thorough audit of the web server's titles is performed and checking them for compatibility with UNA. All settings can be in 3 states:

* OK - the value is satisfactory for UNA

> Example: version = 7.1.3 – OK`

* WARNING - it is desirable to correct the value for the recommended one.  Near listed some tips for changing the state to the recommended value.

> Example: PHP accelerator = - WARNING (The script can be much faster if you install some PHP accelerator)`

* FAIL - parameter value does not satisfy the optimal functioning of the UNA and can seriously slow down the system, or even become a reason for its fail. Near listed some tips for changing the state to the recommended value.

> Example: query_cache_size = 1048576 - FAIL (must be> = 16777216)`

### Files and folders permissions

Shows the status of files and folders access permissions which is important for correct UNA usage. If any of the parameters do not meet the requirements, it will be written in red. On the right showed recommended value.

> Example: cache_public	Not exists	Writable

![](https://raw.githubusercontent.com/wiki/unaio/una/images/dashboard/003.png)

## Cache

Section provides information on the status of UNA cache  (the size of MB, presents)  and provides  any actions for managing it.

When you click on the check mark on the right, a menu is available:

![](https://raw.githubusercontent.com/wiki/unaio/una/images/dashboard/004.png)
 
* Clear all caches - There is a complete cleaning of all cache types
* Clear DB cache - The cache data stored in the DB is cleared
* Clear Template cache - The data in the Template in cache is being cleared.
* Clear CSS cache - Cascading Style Sheets (CSS) is being cleared.
* Clear JavaScript cache - You are clearing the JavaScript cached data (JS).
