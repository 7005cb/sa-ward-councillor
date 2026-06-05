This tutorial is how to make browseable categories. For example we'll add "Occupation" field to "Persons" module and make it possible to browse all people of the same occupation.

## Create a list

Go to Studio > Forms > Data Lists > click "Add New List". We'll add a list for Occupation, make sure to enter list name for all languages, you can switch between languages in the form input by clicking on a flag icon near the input.

[[/images/browseable-categories-create-list.png|alt=Create predefined list for forms in UNA]]

After list is created, fill it with the actual values. Click on "0 items" link near just created list to go to the another grid where you can add new items. Click "Add New Item" and add necessary items. We'll add items for occupation, such as tailor, builder, barber, etc.

## Create a form field with select box

Go to Studio > Forms > Fields > Select desired module (in our case "Persons") > Select desired display (in our case "Add Person") > click "Add New Field" > select "select" type > in the popup specify values for your new field, paying attention to "Values" field, you need to select just created list (in our case "Occupation").

[[/images/browseable-categories-create-form-field.png|alt=Create form field in UNA]]

After field is created you need to make it visible in other form displays, like "Edit Person", "View Person" and "View Full Person" form displays.

## Test changes

Try to create or edit profile and select some value from the created list, then go to profile view to see the changes:

[[/images/browseable-categories-test.png|alt=Category display in UNA]]

As you can see "Tailor" value isn't clickable, so you can't see all tailors on the site.

## Making select field browse-able

To make "select" field clickable you need to make some direct DB modifications. 

First, lets add category object. For example for "Occupation" field it will look like this:

```sql
INSERT INTO `sys_objects_category` SET
`object` = 'my_custom_occupation', /* you can choose any name you want */
`search_object` = 'bx_persons', /* the name of the search object from `sys_objects_search` table, the content displayed in, usually this is single search object from the module the content belong to */
`form_object` = 'bx_person', /* from object from `sys_objects_form` table where new field was added */
`list_name` = 'Occupation', /* the name of list, `key` field from `sys_form_pre_lists` table which was added in previous steps */
`table` = 'bx_persons_data', /* table name with the content */
`field` = 'occupation', /* name of the field which was added in previous steps */
`join` = 'INNER JOIN `sys_profiles` ON (`sys_profiles`.`content_id` = `bx_persons_data`.`id` AND `sys_profiles`.`type` = ''bx_persons'')', /* custom JOIN clause for the SQL query */
`where` = 'AND `sys_profiles`.`status` = ''active''', /* custom WHERE clause for the SQL query */
`override_class_name`= '', /* custom class name */
`override_class_file` = ''; /* custom class file */
```

To find particular values for this table you need to browse SQL tables mentioned near each field above.

After record is added, try to refresh page with test profile, now this field becomes a link, after clicking this field it will list all profiles with the same field value:

[[/images/browseable-categories-making-field-clickable.png|alt=Browseable category display in UNA]]

## Adding block with names and counters

To add a block like this one:

[[/images/browseable-categories-adding-block.png|alt=Block with browseable categories in UNA]]

Install Developer module (if it isn't installed yet). 
Then go to Studio > Developer > Pages > Select module you want to add block to (Persons in our case) > Select page to add block to ("New People" in our sample case) > click "Add Blocks" > Select "Skeletons" and choose "service" blocks. Click on added block, name it as you want, then insert the following in "Code"  field:

```
array (
  'module' => 'system',
  'method' => 'categories_list',
  'params' => array ('my_custom_occupation'),
  'class' => 'TemplServiceCategory',
)
```

`my_custom_occupation` in above code is categories object name we added before, so you need to replace it with yours.

You can specify some options there, for example:

```
array (
  'module' => 'system',
  'method' => 'categories_list',
  'params' => array ('my_custom_occupation', array('show_empty_categories' => true)),
  'class' => 'TemplServiceCategory',
)
```

Also `show_empty` parameter is supported, it will show "Empty" message if no one category has any content in it.

