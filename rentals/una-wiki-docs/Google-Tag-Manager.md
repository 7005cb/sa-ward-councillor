This module allows to get advantages of [Google Tag Manager](https://www.google.com/analytics/tag-manager/) service. As the result it's possible to connect different analytics (such as Google Analytics) from one place - [Google Tag Manager interface](https://tagmanager.google.com/?hl=en).

The only configuration in UNA is just "Google Tag Manager Container ID", all other configuration is done via Google interface. 

Refer to [Google Tag Manager user's guide](https://support.google.com/tagmanager/#topic=3441530) for complete setup guide.

## Basic Google Analytics tracking setup

- [Create Google Tag Manager account and Web container](https://tagmanager.google.com/?hl=en)  
    [[/images/gtm-01-create-account.png|alt=Create Google Tag Manager account and container]]
- Skip "Install Google Tag Manager" instructions and just install "Google Tag Manager" module in UNA  
    [[/images/gtm-02-install-module.png|alt=Install UNA GTM module]]
- Get your Google Tag Manager ID and insert it in your UNA Studio > Google Tag Manager > Settings  
    [[/images/gtm-03-get-id.png|alt=]]
- Follow [this guide on how to add Google Analytics to Google Tag Manager](https://support.google.com/tagmanager/answer/6107124?hl=en).

## Custom events and variables

Data layer variables which are passes on each page:
- `membership-id` - current user membership ID
- `membership-name` - current user membership name
- `profile-type` - current user profile type (such as `account`, `bx_persons`, `bx_organizations`, etc)
- `profile-status` - current user profile status (such as `active`, `pending`, `suspended`)
- `account-email-confirmed` - whether current user has email confirmed (`0` or `1`)
- `account-profiles-count` - number of profiles for the current account
- `keys-secrets-count` - number of generated key&secret pairs

The following custom events are supported:
- `register` - when new UNA account is registered
- `market-download` - when download occurs in Market module, additional data params for the event:
    - `product-id` - Market product ID
    - `product-name` - Market product name (not publicly visible)
    - `product-title` - Market product tile
    - `product-added` - unix timestamp of when product was added
    - `product-changed` - unix timestamp of when product was changed
    - `product-thumb` - product thumbnail id (can be used to check if thumb exists)
    - `product-price-single` - product price for single purchase
    - `product-price-recurring` - product price for recurring billing
    - `product-duration-recurring` - recurring billing duration cycle
    - `product-favorites` - number of times the product was added to favorites
    - `product-featured` - check if the product is featured
    - `product-comments` - number of comments(reviews) for the product
    - `vendor-display-name` - product author display name
    - `vendor-profile-id` - product author ID
- `purchase` - when something is purchased, additional data params for the event:
    - `amount` - purchase amount
    - `currency` - currency code
    - `order-id` - order ID  
    **NOTE:** `purchase` event supports **enhanced ecommerce**, which can be enabled in Google Analytics for automatic tracking  
    Also for Market module the following additional data is passed for one last product in case of several products in one order:
    - `brand`, `vendor-display-name` - product author display name
    - `vendor-profile-id` - product author ID
    - `module-id` - module ID where purchase is happening
    - `module-name` - module name where purchase is happening
    - `quantity` - quantity of the product
    - `name`, `product-name` - Market product name (not publicly visible)
    - `price` - Market product price single or recurring (exact purchase amount is in `amount` field)
    - `product-id` - Market product ID
    - `product-title` - Market product tile
    - `product-added` - unix timestamp of when product was added
    - `product-changed` - unix timestamp of when product was changed
    - `product-thumb` - product thumbnail id (can be used to check if thumb exists)
    - `product-price-single` - product price for single purchase
    - `product-price-recurring` - product price for recurring billing
    - `product-duration-recurring` - recurring billing duration cycle
    - `product-favorites` - number of times the product was added to favorites
    - `product-featured` - check if the product is featured
    - `product-comments` - number of comments(reviews) for the product