By default UNA has Payment App with PayPal and Stripe integrations in it. This App is used to process subscriptions and single time payments via shopping cart. For example, default UNA's Permissions (via Paid Levels App) and Market App are integrated with Payments App. So, if you want to sell something in Market or sell Paid membership levels on your site you need to start from setting up the Payment App.

1. First of all you need to install Payments App via Studio -> Apps Market. 
2. When the App was installed you need to find Payments App in the Studio home page and open it. You may change the default currency for your site there. Also it has 'Site administrator' setting. It's essential to assign some Personal profile as site administratior if you plan to sell Paid membership levels. This profile will be used as seller for paid levels. You may use your Administrator account here.
3. Now you need to go to User End and configure necessary payment providers. You may do it via Account popup -> Settings -> Payments.   
**Note**. If you decided to use a separate profile for 'Site administrator' described in point #2 then you need to configure payment providers for both sellers (Market seller and Paid Levels seller) separately.
4. On the settings page you may see about six payment providers but currently only two of them are fully functional. They are PayPal and Stripe.

## Configuring PayPal:
Open the PayPal slider and take a look at the form parts:
* **Mode.** You may use PayPal in 2 modes (_live_ and _test_). If you want to test payments on your site before going live you need to select _test_ mode and enter your sandbox email address in the last field of the form (**Sandbox**). If you are ready to go live, select _live_ mode. In this mode sandbox email is not needed.
* **Business**. You need to specify your PayPal email address here.
* **Process type**. You may use one of three types here. They are _Direct_, _PDT_ and _IPN_. The easiest way is to use _Direct_ mode. In this mode you don't need to do any changes in your PayPal account. The second one is _PDT_. If you want to use it, you need to enable PDT in your PayPal account and get a token there. Then you need to put it in the **Identity token** field. This method is more secure then the _Direct_ one. The last one is _IPN_ mode. In this mode you need to configure your PayPal account too. Use URL like the following http://www.your_domain.com/[path_to_una]/m/payment/finalize_checkout/paypal/[number] as notification URL in your PayPal. You may find necessary URL in **Data return URL** field. In this mode PayPal will notify UNA script about processed payment automatically when the payment was completed. It's useful when you have a lot of payments with eCheck or pending payments which are not processed immediately.
* Check **Active** checkbox and save the form. Now you should be able to accept payments via PayPal.

**Note.** Current PayPal integration allows to accept Single Time payments only.

## Configuring PayPal API:
Open the PayPal slider and take a look at the form parts:
* **Mode.** You may use PayPal in 2 modes (_live_ and _test_). If you want to test payments on your site before going live you need to select _test_ mode and enter your test **Account**, **Client ID** and  **Secret**. If you are ready to go live, select _live_ mode and enter your live credentials in similar fields.
* **Webhooks URL** has URL auto generated for your profile (as seller). You should use it configuring your PayPal account. This is needed to allow UNA to receive notifications about payments and some other actions which where happened on PayPal end.

In your PayPal account you need to do the following:
* Get API credentials

    1. [Log in to the Developer Dashboard](https://www.paypal.com/signin?returnUri=https%3A%2F%2Fdeveloper.paypal.com%2Fdeveloper%2Fapplications) with your PayPal account.
    2. Under the DASHBOARD menu, select My Apps & Credentials.
    3. Make sure you're on the Sandbox tab to get the API credentials you'll use while you're testing payments on your site. After you test and before you go live, switch to the Live tab to get live credentials.
    4. Under the App Name column, select Default Application, which PayPal creates with a new Developer Dashboard account. Select Create App if you don't see the default app.

The Default Application (or newly created application) page displays your API credentials, including your client ID and secret. You need to copy them accordingly to UNA user end -> Account popup -> Settings -> Payments -> PayPal API form -> **Account**, **Client ID** and **Secret** fields.

* Configure Webhook URL

While you are on a newly created application's page you need to configure webhooks to notify UNA when certain events occur. To configure a webhook scroll down to Webhooks block and click 'Add Webhook'. Then you need to enter the URL, which is provided in UNA user end -> Account popup -> Settings -> Payments -> PayPal API -> **Webhooks URL**, and select 'Payment sale completed', 'Payment capture refunded' and 'Billing subscription cancelled' events. 

## Configuring Chargebee:

There are 2 Chargebee integrations:

1. Using Chargebee's hosted pages. In this case a buyer will be redirected from you site to a pregenerated payment page (on Chargebee end) where he will be able to enter Credit Card details and some other info.
2. Using Chargebee's drop-in script. In this case a buyer will be able to enter all necessary info in a special popup directly on your site. Note. Drop-in script should be enabled in you Chargebee account.

Open the Chargebee slider related to the integration you've selected and take a look at the form parts:

* **Mode.** You may use Chargebee in 2 modes (live and test). If you want to test payments on your site before going live you need to select test mode and enter your Site and API Key test values in **Site (test)** and **API Key (test)** fields accordingly. If you are ready to go live, select live mode and fill in **Site (live)** and **API Key (live)** fields. In this mode test keys aren't needed.
* Check **Enable amount checking** checkbox if you don't plan to use coupons, discounts directly via your Chargebee account, etc.
* Check **Enable SSL** checkbox if your site has SSL certificate. It's recommended to use SSL to reduce the number of rejected payments.
* Check **Active** checkbox and save the form. Now you should be able to accept payments via Chargebee.
* **Webhooks URL** has URL auto generated for your profile (as seller). You should use it configuring your Chargebee account. You need to go to Chargebee Dashboard -> Settings -> Configure Chargebee -> API Keys and Webhooks -> Webhooks and click with **Add Webhook** button. Enter a title and the Webhooks URL in the appeared form and submit the form. You don't need to change anything else in the form. This is needed to allow UNA to receive notifications about payments and some other actions which happened on Chargebee end.

**Note**. Chargebee integration allows to accept Subscription payments only.

**Note**. If you plan to sell subscriptions in Market and/or Paid Levels modules then don't forget that you need to create Plans for each subscription. It can be done in your Chargebee account -> Product Catalog -> Plans section. Creating a plan you need to use the same price, time frames and trial parameters which you've used during the creation of associated Market product or Paid level in UNA script.
**ID** field is the most essential because it's used to connect UNA product/level to Chargebee plan. It's value should be unique and taken from:

* in Market. **Name** field in product creation form.
* in Paid Levels. **Name** column in Studio -> Paid Levels -> Settings.

In both cases they are the autogenerated fields and should be used as is.

## Configuring Stripe:
Open the Stripe slider and take a look at the form parts:
* **Mode.** You may use Stripe in 2 modes (_live_ and _test_). If you want to test payments on your site before going live you need to select _test_ mode and enter your Public and Secret test keys in **Public Key (test)** and **Secret Key (test)** fields accordingly. If you are ready to go live, select _live_ mode and fill in **Public Key (live)** and **Secret Key (live)** fields. In this mode test keys are not needed.
* Check **Enable amount checking** checkbox if you don't plan to change payments, subscriptions or use coupons, discounts directly via your Stripe account.
* Check **Enable SSL** checkbox if your site has SSL certificate. It's recommended to use SSL to reduce the number of rejected payments.
* Check **Active** checkbox and save the form. Now you should be able to accept payments via Stripe.
* **Webhooks URL** has URL auto generated for your profile (as seller). You should use it configuring your Stripe account. You need to go to Stripe Dashboard -> Developers -> Webhooks and click with **Add endpoint**. Enter the **Webhooks URL** in the appeared form and submit the form. You don't need to change anything else in the form. This is needed to allow UNA to receive notifications about payments and some other actions which where happened on Stripe end.

**Note.** Stripe integration allows to accept both Single Time and Subscription payments. 

**Note.** If you plan to sell subscriptions in Market and/or Paid Levels modules then don't forget that you need to create Plans for each subscription. It can be done in your Strip account -> Plans section. Creating a plan you need to use the same price, time frames and trial parameters which you've used during the creation of associated Market product or Paid level in UNA script.  
**Plan ID** field is the most essential because it's used to connect UNA product/level to Stripe plan. It's value should be unique and taken from:
* in Market. **Name** field in product creation form.
* in Paid Levels. **Name** column in Studio -> Paid Levels -> Settings.  

In both cases they are the autogenerated fields and should be used as is.


## Configuring Stripe Connect:
Stripe Connect is used to connect platform (site author's) Stripe account with Stripe accounts of site members. It's needed if site author wants to organize Market (using, for example, the default UNA Market app) on his site and take comissions from Market sellers. In this case the system should be configured from both ends: **as site author** and **as Marker seller**.  
* **As site author**. 
    1. First of all you need to go to your Stripe account -> Account settings popup -> Connect tab and register a platform. To do this you need to click with a 'Register your platform' link in the left-bottom corner of the popup. Fill in and submit the appeared form (**Redirect URIs** field can be left empty because we'll fill it in later). 
    2. Then you need to install Payments and Stripe Connect apps via Studio -> Apps Market. 
    3. When the apps were installed you need to find Stripe Connect app in the Studio home page and open it. Now you need to configure necessary settings:
        + **Mode**. You may use Stripe Connect in 2 modes (_live_ and _test_). If you want to test payments on your site before going live you need to select _test_ mode and enter your Client ID, Public and Secret test keys in **Client ID (test)**, **Public Key (test)** and **Secret Key (test)** fields accordingly. If you are ready to go live, select _live_ mode and fill in  **Client ID (live)**, **Public Key (live)** and **Secret Key (live)** fields. In this mode test keys are not needed.  
Client ID can be found in your Stripe account -> Account settings popup -> Connect tab. Development client ID is needed for _test_ mode, Production is for _live_ one. Also you may see Redirect URLs fields just after Client ID. There you need to enter Redirect URL which can be found in Studio -> Stripe Connect -> Settings page -> Redirect URL field. **Note**. Only HTTPS URLs can be used in _live_ mode.  
Public and Secret keys for _live_ and _test_ modes can be found in Stripe account -> Account settings popup -> API Keys tab. 
        + **Scope**. This setting should be set to _Read Write_. 
        + **One-time payments processing mode** and **Subscription payments processing mode**. There are two ways of payments processing: Process the payment directly on the connected account (_Direct_) or Process the payment on the platform’s account, and then transfer necessary funds to dependent account (_Platform_). With the first approach, the connected account is responsible for the fees, refunds, and chargebacks. The payment itself will appear as a charge in the connected account. The second approach provides much more customizability but makes the platform account responsible for the fees, refunds, and chargebacks. The payment appears as a charge in the platform account, along with a transfer from the platform account to the connected account. Currently _Platform_ processing type is available for One-time payments only. 
        + **One-time fee**. It can have a fixed value in cents (use just a number) or a percentage (use a number with % sign). 
        + **Subscription fee**. It can have a percentage only (use a number with % sign). 

* **As Market seller**. 
    1. First of all you need to go to Dashboard page and find 'Stripe Connect' block. 
    2. Click with 'Connect' button. It will requre you to login into your Stripe account and confirm the connection. 
    3. Return to Dashboard page -> 'Stripe Connect' block. You should see Public and Secret keys generated for you during the connection creation. Now you need to enter them in Stripe settings (Account popup -> Orders -> Settings). You may read more about it in 'Configuring Stripe' section above.  
**Note**. If Stripe Connect is set to _test_ mode then test Public and Secret keys will be generated during the connection. Before going life you need to close all test connections, which can be done via Studio -> Stripe Connect -> Accounts page.