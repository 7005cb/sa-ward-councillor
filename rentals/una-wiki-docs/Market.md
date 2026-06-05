**Market** app is needed if you want to organize sales of software products on your site. The app allows any site member, which has necessary permissions, to sell products. Permissions can be checked in Studio -> [Permissions app](https://github.com/unaio/una/wiki/Permissions-Builder). In this case the seller is allowed to describe the product, upload screenshots and most importantly - the package which will be available to a buyer after purchase. From another side the buyer can purchase the necessary product, receiving a license and an access to downloadable package and updates if they are available. Also, the buyer can leave his feedback by posting a review and/or giving a vote using 5 star based voting system. The buyer has access to a list of licenses for all previously purchased products.

### So, how to start selling something in Market app?
First of all you need to install **Payments** and **Market** apps via Studio -> Apps Market. The default Payments app is needed to process payments during products purchasing in Market app. You may read more about payments [here](https://github.com/unaio/una/wiki/Payments).

When the apps were installed, let's try to create the first product. To do so, you need to go to user end -> Plus (+) menu -> Product. When a page was loaded you may see something like this 

[[/images/market/001.png|alt=No active payment provider notification]]

It means that Payments app was installed but any of payment providers were not activated. You need to follow the link provided in the notification message and activate one or more payment providers. Doing so, you may return to product creation process. 

Now you may see Product Creation form. 

[[/images/market/002.png|alt=Product creation form]]

The first part of the form contains main info about the product itself: **Title**, **Name**, **Category**, **Description**, **Pictures** (including thumbnail and cover), **Files** (main package(s) and updates). 
Pay attention to:
1. **Name**. If you plan to sell some UNA app in UNA.IO Market then this field is very important. In this case the field should have exactly the same value which you've used for your app (module) during creation ('name' parameter in [app_folder]/install/config.php file). If you are creating a market on your own site then this field isn't needed and can be hidden via Studio -> Forms builder.
2. **Files**. As we already mentioned this field is needed to attach main package and updates. So, the uploaded file can have one of 2 types: **version** or **update**. If you want to upload a version then you need to select version in _Type_ field, specify the version number in _Version_ field, for example 1.0.0, and mark or not this version as **main** using _Use as Main_ checkbox. Only one version file can be marked as main and it will be offered for download by default.
**Note**. If you want to upload a new version at the same time with the update from previous version to this one, then you need to upload the version and submit the whole product form. Then you need to edit the product again and upload the update file. This is needed to allow the app to register the newly uploaded version, that it becomes visible in update uploading form.

[[/images/market/003.png|alt=Upload version file]]

If you want to upload an update file, you should have at least two versions already uploaded, for example 1.0.0 and 2.0.0. In this case if you select **update** type in _Type_ field the file uploading form will be updated and you'll see _Version from_ and _Version to_ dropdown fields which allow you to select necessary versions. Update file doesn't have _Use as Main_ checkbox because updates depend on the versions and cannot act as main downloadable files.

[[/images/market/004.png|alt=Upload update file]]

**Note**. If you are selling some UNA app in UNA.IO Market and wants to release a new version with automatic update script then you need to use exactly the same version numbers in _Version_, _Version from_ and _Version to_ fields as you've used in 'version' parameter from app's config file ([app_folder]/install/config.php) and in 'version_from' and 'version_to' parameters from update's config file ([update_folder]/install/config.php).

Further more, you may see payment related fields. They are divided into two groups: **One-time payment** and **Recurring payment**. The availability of these fields depends on the payment providers you have activated. For example, if you activated PayPal only then you'll be able to set up one-time payments for your product only. It appears because current integration of PayPal doesn't support recurring payments. If you want to sell your products using subscriptions then you need to activate  Stripe and/or Chargebee payment provides. In this case, you will be able to set up recurring pricing options for your product.

[[/images/market/005.png|alt=Payment options for a product]]

_Notes_ section allows you to add some notes for your product. The first one from _Notes_ field will be displayed on product's info page. The second one from _After purchase notes_ field will be attached to a message which is automatically sent to the buyer just after the payment is processed successfully.

If your profile has necessary permissions you may see _Subproducts_ field in _Other_ fields section. By default this field is available to Moderator and Administrator profiles only. The field allows you to create a bundle of products inside one single product. It may be useful if you want to provide some discount for bundle purchases. In this case you don't need to upload all the products's files and their updates in one but only need to link the necessary products, which were previously posted, using this field. When buyer purchases such product his license allows to use (download, install and receive updates) one instance of each product included in the bundle.

When the product creation form was submitted you may see a product view page.

[[/images/market/006.png|alt=Product view page]]

By default this page has product's description block including title, text, header image, list of screenshots, actions block, comments block and the others. As any other page in UNA script it can be customized via pages builder (Studio -> Pages). Actions block has a necessary set of actions which depends on the viewer. Some of them are:
1. **Download** - permission to see a list of downloadable files if the product is free or viewer previously purchased it.
2. **Delete** - permission to completely delete the product. This action button is available for free and paid products, which weren't purchased yet. If the paid product was purchased at least one time then it cannot be removed.
3. **Hide**\**Publish** - permission to hide a product from  market or show it back. It can be useful for paid products which cannot be removed but already outdated for some reason.

Also Market app provides different browsing capabilities like Latest, Featured, Popular, Updated, etc pages, browsing by categories, search and so on. Your own products and all products in market (for moderator and administrator profiles) can be controlled via Manage page.