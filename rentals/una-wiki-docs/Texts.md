In this section you will find how you can edit texts which you would want to edit in the first place after UNA installation. Such texts include _Copyright_, _About_, _Terms of service_, _Privacy policy_ and _Homepage cover text_.

To edit these texts, you will need to use the [Polyglot](https://github.com/unaio/una/wiki/Polyglot) module.

So, to get started, let's go to **Studio** -> **Polyglot** -> **Keys**.


## About

The language key for the text on the _About_ page is called **_sys_page_lang_block_about**.
Enter this key in the empty search field next to the field with modules list (you will see _All Modules_ selected in that field). It's not necessary to press _Enter_ because the results will be populated as soon as you edit the search field.

The result will show you the key name, the module which this key belongs to (_System_), the key text and languages installed on your site (_en_ by default). Click the icon ![pencil](https://una.io/s/sys_images/fgjkcqu7hkeke4ndr5yzvtznnfsqaqjz.jpg) to edit the key text. You can use HTML tags when entering new text, for example: `<b>This site is powered by UNA.</b>`.

There is an alternative way to edit this page's text. You can do so using the [https://github.com/unaio/una/wiki/Pages-Builder](Pages) module.

Go to **Studio** -> **Pages**. The page _About_ is a system page, so you will find it under the **System** category. This page contains one block _About_:

![About](https://una.io/s/sys_images/3wkfjlanbtwz7y6dhc3phfyhvqykfz8r.jpg)

, so click it and edit the text in the field _Content_:

![Content](https://una.io/s/sys_images/zgfgv4e6n9rdu5fgk2zf6xumty6kxph2.jpg)


## Copyright

The _Copyright_ text gets displayed when you hover your mouse over the _Copyright_ icon:

![Copyright](https://una.io/s/sys_images/d2jaka2eekju37fvmabad3hsrgzdrnmb.jpg)

The key for this text is called **_copyright**. Find it in the **Polyglot** and edit the same way as before.
Make sure not to change the **{0}** part of the key. It's a special variable which will show the current year.


## Terms of service

This text can also be edited in two ways: using the **Polyglot** and using the **Pages** modules.

### Polyglot

Find the key **_sys_page_lang_block_terms** and edit it. You can use HTML tags.

### Pages

Find the page **Terms of Service** under the **System** category and edit the only block content on this page. You can use HTML tags.


## Privacy policy

The same way as above applies to _Privacy policy_ text:

### Polyglot

Find the key **_sys_page_lang_block_privacy** and edit it. You can use HTML tags.

### Pages

Find the page **Privacy Policy** under the **System** category and edit the only block content on this page. You can use HTML tags.


## Homepage cover

The only cover block with HTML content is the _Homepage cover_:

![Homepage cover](https://una.io/s/sys_images/5hpzezyhwlsijx6vjemznrhpyurbjevf.jpg)

The corresponding language key is called **_sys_txt_homepage_cover**. You can edit it using HTML tags. It already contains some tags for the _Join_ button code. Make sure that you don't edit the **{0}** variable as it will be replaced with the Join page URL on the frontend.