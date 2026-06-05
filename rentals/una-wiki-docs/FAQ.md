
## How to change "Privacy Policy" and "Terms Of Use" pages ?

Go to Studio > Polyglot > Keys.

- For "Privacy Policy" search for `_sys_page_lang_block_privacy`
- For "Terms Of Use" search for `_sys_page_lang_block_terms`

Click "Edit" button and update text for all languages.

Also you can use free and paid online generators for this text, for example:  
http://privacypolicies.com  
https://termsfeed.com/terms-service/generator/  

## How to change copyright text ?

Copyright is showing when you hover mouse over copyright symbol in bottom right corner of your UNA site. You can change the text which is showing on hover in Studio > Polyglot > Keys > Search for "_copyright" then edit the found strings.

## How to generate Google Maps API Key ?

Go to https://console.developers.google.com/ and create new project, when new project is created (select it if it isn't selected automatically) go to Credentials > Create Credentials > API Key, copy created API key and insert it in UNA Studio > Settings > General > Google Maps API Key, then go to Library:  
[[/images/faq/google-maps-api-key-1.png|alt=UNA FAQ how to generate Google API key]]

Then enable the following APIs:  
- Google Maps JavaScript API
- Google Maps Geocoding API
- Google Static Maps API
- Google Places API Web Service  

Additionally, according to recent changes you also need to add Billing Account to your project:  
[[/images/faq/google-maps-api-key-2.png|alt=UNA FAQ how to add Google Billing Account]]

## How to manually reset the password ?

Run the following SQL query:
```sql
UPDATE  `sys_accounts` SET  `password` = SHA1(CONCAT(MD5('new_password'), `salt`)) WHERE `email` =  'account@email.here' LIMIT 1;
```
Replace **account@email.here** with account's email you want to change the password for, and **new_password** with new password.

## How to manually create an Operator ?

Join as new member, when joining create only account, without profile creation.  
Then run the following SQL query to make joined account an Operator:
```sql
UPDATE  `sys_accounts` SET  `role` = 3 WHERE `email` =  'just-joined@email-address.here' LIMIT 1;
```
Then continue to create a profile.  
As the result you will have an Operator account with access to the Studio and Administrator profile with admin right on the site.


## How to change cover images ?

Default cover image on all pages can be changed in:  
`Studio > Designer > Cover`  

Images on individual pages can be changes in Pages Builder:  
`Studio > Pages > select desired page > Settings > Cover`  

But some pages override any of these images, for example Profile page, if cover is uploaded by user.

## How to add custom meta-tag to HEAD section of all pages ?

It maybe needed to confirm site ownership in different services, like Google, Pinterest, etc.

Go to:  
`Studio > Designer > Injections`

Paste your meta-tag in `<HEAD> injection` field, then press "Submit"

## How to reset UNA license after domain change ?

1) Login to you una.io account
2) Go to [Dashboard > Licenses](https://una.io/page/products-licenses)
3) Reset the desired license on licenses page
4) Login to your UNA site
5) Go to your site Studio > Apps Market > Purchases > Download any app to associate your UNA license with your site

At the last step your license is associated with your site key&secret.

## Where users can update their payment information (credit card) for the subscription.

Go to Dash > Subscriptions > Click on "Cog" button for the desired subscription > Change Billing Info

## How to change UNA site URL ?

Edit `inc/header.inc.php` file, the following string:
```php
define('BX_DOL_URL_ROOT', 'http://example.com/path-to-una/'); ///< site url
```
Then clear `/cache/` and  `/cache_public/` folders (leave only .htaccess file there)

## How UNA cronjob should look like ?

UNA cronjob should run every minute and look like this:
```php
* * * * * /path/to/php /path/to/una/periodic/cron.php
```
You can see if it's running properly by going to: Studio > Dashboard > Host Tools > Server Audit  
**Last cron jobs execution** value should be within a minute from you local time.

## How to use my gmail.com email to send messages in UNA ?

You need to install 'SMTP Mailer' module first.
Use the following settings in 'Studio > SMTP Mailer':

* Enable SMTP Mailer: true
* SMTP Authentication: true
* SMTP Username: **your Gmail email**
* SMTP Password: **your Gmail password**
* SMTP Server Name: smtp.gmail.com
* SMTP Server Port: 587
* Secure Connection: TLS
* Allow self-signed certificates: false
* 'From' Name: **your site name**
* Override Default Sender: **your Gmail email**

Change values in **bold** to your own.
Please note some limits apply on how many emails can be send per certain period of time.

If you have "Sign-in attempt was blocked" error, then go to [Google security settings page](https://myaccount.google.com/security?pli=1&hl=en&nlr=1) and allow "Less secure app access" option.

## How configure Amazon S3 storage ?

By default `Local` storage is used, which means that uploaded files are stored in `storage/` folder. It's possible to use remote storage such as Amazon S3 to store files remotely, it has many advantages, such as: unlimited storage, reduced load on your web-server, ability to have multiple web-server instances which are using the same remote storage.  

To configure it you need to enter required credentials in Studio > Settings > Storage. 

**NOTE:** It's recommended to change this setting on clean UNA installation. When changing storage from `Local` to `S3` and back, it will change storage for modules which don't have any files uploaded, since if there are any files uploaded already then they need to be moved to another storage manually. If you moved files manually, then you need to change storage manually in the DB in `sys_objects_storage` table, `engine` field, for all records which were moved to another storage.

## How to use Likes instead of Reactions?

Reactions were created in addition to previously used Likes. So, if you don't want to use Reactions with a number of choices you may disable them and enable Likes. For example, if you want to do it in Posts app you need to do the following: Go to Studio -> Navigation -> Select **Posts** app -> Select **View Actions** menu, disable **Reaction** and enable **Vote (for)**.
