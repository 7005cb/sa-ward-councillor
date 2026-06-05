To make UNA to use one master read/write DB instance and multiple read-only DB instances, you need to edit `inc/header.inc.php` file and specify servers in `BX_DATABASE_*` constants, for single DB server configuration looks like this:

```php
define('BX_DATABASE_HOST', '192.168.0.100'); ///< db host
define('BX_DATABASE_SOCK', ''); ///< db socket
define('BX_DATABASE_PORT', ''); ///< db port
define('BX_DATABASE_USER', 'root'); ///< db user
define('BX_DATABASE_PASS', 'pa55wd'); ///< db password
define('BX_DATABASE_NAME', 'una'); ///< db name
define('BX_DATABASE_ENGINE', 'MYISAM'); ///< db engine
```

so it need to be updated like this:

```php
define('BX_DATABASE_HOST', array('192.168.0.100', '192.168.0.101', '192.168.0.102')); ///< db hosts, first is always rw master
define('BX_DATABASE_SOCK', array('', '', '')); ///< db sockets, first is always rw master
define('BX_DATABASE_PORT', array('', '', '')); ///< db ports, first is always rw master
define('BX_DATABASE_USER', array('root', 'readonly-user', 'readonly-user')); ///< db users, first is always rw master
define('BX_DATABASE_PASS', array('pa55wd', 'pa55wd', 'pa55wd')); ///< db passwords, first is always rw master
define('BX_DATABASE_NAME', array('una', 'una', 'una')); ///< db names, first is always rw master
define('BX_DATABASE_ENGINE', 'MYISAM'); ///< db engine
```

So instead of one value for each constant we specify several values, 1st item is always read/write DB node, and all other nodes are considered as read-only nodes.
It's better to convert DB from MyISAM to InnoDB engine, it works better for high concurrency, then after converting the following line need to be changed as well:
```php
define('BX_DATABASE_ENGINE', 'INNODB'); ///< db engine
```