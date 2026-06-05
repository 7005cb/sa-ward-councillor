# Installing UNA on a apache2 server
##Tested on aws ubuntu 18.04LTS
## The server is setup using PHP 7.2 The installed components are different from UNA specified [requirement](https://github.com/unaio/una/wiki/Requirements)
###UPDATE AND UPGRADE ALL PACKAGES

### Run these to update and upgrade all packages
`sudo apt-get update`

`sudo apt-get upgrade`

### Installing PHP and PHP packages necessary for UNA
`sudo apt-get install php php-curl php-gd php-mbstring php-json php-fileinfo php-zip php-openssl php-exif php-mysql`

### Installing mariadb sql and setting up database
`sudo apt-get install mariadb-server`

After installing, the commands below can be used to stop, start and enable MariaDB service to always start up when the server boots.

`sudo systemctl stop mariadb.service`

`sudo systemctl start mariadb.service`

`sudo systemctl enable mariadb.service`

After that, run the commands below to secure MariaDB server by creating a root password and disallowing remote root access.
sudo mysql_secure_installation

When prompted, answer the questions below by following the guide.

* Enter current password for root (enter for none): Just press the Enter
* Set root password? [Y/n]: Y
* New password: Enter password
* Re-enter new password: Repeat password
* Remove anonymous users? [Y/n]: Y
* Disallow root login remotely? [Y/n]: Y
* Remove test database and access to it? [Y/n]:  Y
* Reload privilege tables now? [Y/n]:  Y
* Restart MariaDB server

`sudo systemctl restart mariadb.service`

### Create database
To logon to MariaDB database server, run the commands below.

`sudo mysql -u root -p`

Run these to create the database , create a user and give that user access to database


	CREATE DATABASE database_name;

	CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';

	GRANT ALL ON csdb.* TO 'username'@'localhost' IDENTIFIED BY 'password' WITH GRANT OPTION;

	FLUSH PRIVILEGES;

	EXIT;


### creating virtual host

Creating a directory for your UNA site
`sudo mkdir /var/www/your_site_url`

`sudo chown -R $USER:$USER /var/www/your_site_url`

`sudo chmod -R 755 /var/www/your_site_url`

`sudo nano /etc/apache2/sites-available/your_site_url.conf`
Copy paste the below lines into the conf file

    <VirtualHost *:80>

       ServerAdmin webmaster@localhost

       ServerName your_site_url

       DocumentRoot /var/www/your_site_url

       ErrorLog ${APACHE_LOG_DIR}/error.log

       CustomLog ${APACHE_LOG_DIR}/access.log combined
       
       <Directory /var/www/your_site_url>

        Options Indexes FollowSymLinks MultiViews

        AllowOverride All

        Order allow,deny

        allow from all

                Require all granted

       </Directory>

    </VirtualHost>


`sudo a2ensite your_site_url.conf`
`sudo a2dissite 000-default.conf`
`sudo apache2ctl configtest`
`sudo systemctl restart apache2`

### installing lets encrypt ssl

`sudo add-apt-repository ppa:certbot/certbot`
`sudo apt install python-certbot-apache`
`sudo nano /etc/apache2/sites-available/your_site_url.conf`
`sudo apache2ctl configtest`
`sudo systemctl reload apache2`

`sudo certbot --apache -d your_site_url`

This will install a ssl certificate to your system

### installing site
Transfer the .zip archive downloaded from UNA to the system

Extract it to the folder /var/www/your_site_url

### Make the necessary permission changes to your directory [follow this for what permission to set](https://github.com/unaio/una/wiki/Installation#permissions)

use sudo chmode to grant permission to the directory

### In .htaccess mod_php5.c is used need to change it to mod_php.c to use default php 

`sudo nano /var/www/your_site_url/.htaccess`

change mod_php5.c to mod_php.c

Check your site by going into your_site_url 
Complete the server audit and if error exists troubleshoot it and fix it

### cronjob
Run the code and paste the cronjob from the browser to the crontab

`crontab -e`

