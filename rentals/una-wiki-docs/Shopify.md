If you plan to sell goods via your site and/or allow your members to do it then the Shopify integration module will help you to do this.

Firstly if you are a seller you should register a Shopify account [here](https://shopify.com/). You need to use at least **Basic** plan to have access to products via API. All products would be stored in your Shopify account. You may manage them via Shopify Dashboard, which can be accessed using the URL like the following: https://your-store-com.myshopify.com/admin/ where 'your-store-com' is automatically generated store name (domain). You may add products via Shopify Dashboard -> Products.

Now lets configure the connection between your Shopify account and Shopify app in UNA. At the very beginning we need to generate an access token:
1. From your Shopify admin, go to 'Apps'.
2. Click 'Manage private apps', near the bottom of the page.
3. Click 'Create a new private app' button in the top right corner of the page.
4. In the Description section, enter a name for the private app and a contact email address. Shopify uses the email address to contact the developer if there is an issue with the private app, such as when an API change might break it.
5. Don't change anything in the Admin API section. 
6. You need to enable the Storefront API, to do this select 'Allow this app to access your storefront data using the Storefront API' checkbox. In the Storefront API permissions section, select 'Read products, variants, and collections' with 'Read product tags', 'Read and modify checkouts' and unselect 'Read and modify customer details' with 'Read customer tags' and 'Read content like articles, blogs, and comments' checkboxes.
7. Click Save.

Now you may see your newly created App in Apps -> Manage private apps section. Open it and find 'Storefront access token' in 'Storefront API' section. Use it to fill in the corresponding setting ('Storefront access token') in settings form which can be found in UNA -> Shopify (Shop) -> Settings. Enter automatically generated Shopify store URL (in our example it's your-store-com.myshopify.com) in 'Domain' field of settings form and save the form. Now you are ready to create products in Shopify Dashboard and UNA Shopify app.

Now we need to create/update product(s) in Shopify Dashboard and UNA Shopify app. Let's start from Shopify Dashboard. You may create new products or update existing ones but the main purpose is to make them visible to the newly created private app:
* To create a new product:
1. From Shopify admin navigate to Products.
2. Click 'Add product' and fill in the form.
3. In 'Product availability' click Manage.
4. Make sure to check the checkbox next to the name of your private app.

* To make existing product(s) available:
1. From Shopify admin navigate to Products.
2. Click the product's name to open its details.
3. In 'Product availability' click Manage.
4. Make sure to check the checkbox next to the name of your private app.

As we mentioned above the main product's info is stored in Shopify, therefore you need to get a Shopify 'product handle' to register it in the UNA Shopify app. To retrieve the product handle of a product you need to do the following:
1. From Shopify admin navigate to Products.
2. Click the product's name to open its details.
3. In 'Search engine listing preview' click 'Edit website SEO'.
4. In text box 'URL and handle' copy the second part.

Use this handle in 'Product handle' field registering the product in UNA ('Plus' menu -> Goods). Also 'Goods' form in UNA has a mandatory field 'Title'. It is recommended to use the same title which you have for this product in your Shopify Store to avoid buyers' misunderstanding. The other fields in the form are needed for goods browsing in UNA and can be set in any way you want.