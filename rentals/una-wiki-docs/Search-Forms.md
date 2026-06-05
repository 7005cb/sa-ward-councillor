UNA provides the easy way to create custom search forms for any installed content module. In the following example, we will add new search form for "Persons".

## First steps

First of all, install Developer module (if it isn't installed yet).
Then go to Studio > Developer > Forms > Search Forms > Select module where you want to add new search form (Persons in our case) and press button "Add New Search".

In appeared popup you will see the list of the next fields:

* **Name** - unique name of the search form. Let's call it bx_persons_search_adv.
* **Content Info object** - the "source" for searched info (Persons in our case).
* **Module** - module where the search will be performed (Persons too).
* **Title** - the language key for the caption of the search form (_bx_persons_search_adv).
The mentioned 4 fields are required, it's impossible to create any search form with missing any from this list.

Fields **"Class Name"** and **"Class File"** are necessary for advanced customization of search form (see details below).

## Import fields

After pressing the "Save" button a new form will appear in the list of the search forms of this module with the info "0 fields" under "Fields" column. After click by this link operator will be redirected to the "Search Fields" area of this form. Button "Reset" launches import from the "Add Items" of the current module (in our case it will be "Add person"). Every field has "Edit" icon which calls popup with the settings of the choosen field (see details below).

## Locate of a new search form

In the same "Developer" module go to Pages > Persons area. Choose page where you need to see the new search form then press "Add blocks" button. In the appeared popup find the "Persons" module in the left column and click it. Then in the right column find the blocks "Search form" and "Search results" (in the case when you need to have search results from this form too) and check both. Then press "Add to page" button. After the appearances of the new blocks in the page click on "Search form" item. In the popup with the settings of new form find the field "Code". It will contain the following content:

```php
array (
  'module' => 'system',
  'method' => 'get_form',
  'params' => 
  array (
    0 => 
    array (
      'object' => 'bx_persons',
    ),
  ),
  'class' => 'TemplSearchExtendedServices',
)
```

Notice the line _'object' => 'bx_persons',_ - it is responsible for calling the defined search form. So replace "bx_persons" to "bx_persons_search_adv" and press "Save" button. Your new search form will appear in chosen place. The simplest example is completed.


Also there is more descriptive tutorial from the community:
https://una.io/page/view-discussion?id=1461