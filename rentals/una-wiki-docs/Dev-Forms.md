Forms API allows to display forms from data stored in DB tables, before it was possible to display forms from PHP arrays only.

The new Form Objects have the following main advantages:

* Minimal coding is needed to create different forms
* Easy forms alterations
* Multiple representations of the same form
* Automated data inserting into database
* Automated data updating
* Automated data checking
* Automatic security checking
* Automatic spam filter

Updated API uses several definitions:

* **Form** or **Form Object** - record in `sys_objects_form` table, or instance of Form class.
* **Form Display** - set of some form inputs in particular order, displayed for some purpose; one form can have several displays, for example **add** and **edit** displays.
* **Form Input** or **Form Field** - form input field, like textarea, checkbox or set of radio buttons.

## Creating the Form Object

**1.** Create Form Object, add record to `sys_objects_form` table:

* **object** - name of the Form Object, in the format: vendor prefix, underscore, module prefix, underscore, internal identifier or nothing; for example: `bx_group` - for group data processing, like group adding or editing.
* **title** - Form Object title to display in studio forms builder.
* **action** - url to submit form to, if url is not full and not empty, then site url is added automatically.
* **form_attrs** - serialized array of additional form attributes.
* **submit_name** - name of form field with submit button, it is used to determine if form is submitted.
* **table** - DB table name (for automatic saving/updating).
* **key** - DB table field with unique ID (for automatic updating).
* **uri** - DB table field with URI (for automatic URI generation, along with **uri_title**).
* **uri_title** - DB table field with data title (for automatic URI generation, along with **uri**).
* **params** - serialized array of additional form parameters:
    * **checker_helper** - name of custom `BxDolFormCheckerHelper` class.
    * **csrf** - array of Cross-site request forgery attack prevention parameters, for now only one boolean parameter is supported - **disabled**, so it can be disabled for particular form.
* **active** - **1** or **0**, if form is inactive then it can not be used anywhere.
* **override_class_name** - user defined class name which is derived from `BxTemplFormView`.
* **override_class_file** - the location of the user defined class, leave it empty if class is located in system folders.

**2.** Create Form Displays, add records to `sys_form_displays` table:

* **display_name** - name of the Form Display, in the format: form object name, underscore, internal identifier or nothing; for example: `bx_group_add` - for displaying group adding form, `bx_group_edit` - for displaying group editing form
* **module** - module name this display belongs to, it must be associated with `name` field in `sys_modules` table.
* **object** - form object name from `sys_objects_form` table this Form Display belongs to.
* **title** - Form Display title to display in studio forms builder.
* **view_mode** - display form as read-only.

**3.** Create Form Fields, add records to `sys_form_inputs` table:

* **object** - form object name from `sys_objects_form` table this Form Field belongs to.
* **module** - module name this field belongs to.
* **name** - unique Form Field name in particular From Object.
* **value** - default value, or empty if there is no default value.
* **values** - possible values, for certain form field types.
* **checked** - **0** or **1**, it determines if form field is checked, for certain form field types.
* **type** - form field type, for now the following types are supported (detailed description of every type will be described later):
    * **text** - text input field.
    * **password** - password input field.
    * **textarea** - multiline input field.
    * **number** - number input field.
    * **select** - select one from all available values.
    * **select_multiple** - select one, multiple or all items from all available values.
    * **switcher** - on/off switcher.
    * **checkbox** - one checkbox.
    * **checkbox_set** - set of checkboxes.
    * **radio_set** - set of radio buttons.
    * **slider** - select some numeric value within the range using slider control.
    * **doublerange** - select range values within the range using slider control.
    * **datepicker** - date selection control.
    * **datetime** - date/time selection control.
    * **captcha** - image captcha.
    * **hidden** - hidden input field.
    * **file** - file upload input.
    * **button** - button control.
    * **image** - form image button.
    * **reset** - form reset button.
    * **submit** - form submit button.
    * **value** - just displaying value without any crontol.
    * **block_header** - start group of field.
    * **custom** - custom control.
    * **input_set** - set of other form controls.
*  **caption** - input title.
*  **info** - some info to help user to input data into the field, it's better to specify format and limits here.
*  **required** - indicate that the input is required by displaying asterisk near the field, __please note__
 that this field don't perform any checking automatically, since you mark field as required you need to specify checker function which will check entered value.
*  **collapsed** - display section as collapsed by default, for `block_header` field type only.
*  **html** - display visual editor of certain type, for `textarea` field type only.
    * **0** - no visual editor, leave `textarea` field as it is.
    * **1** - standard(default) visual editor.
    * **2** - full visual editor.
    * **3** - mini visual editor.
* **attrs** - serialized array of additional input attributes.
* **attrs_tr** - serialized array of additional attributes for the whole input row.
* **attrs_wrapper** - serialized array of additional attributes for input wrapper.
* **checker_func** - checker function, if you marked field as required in `textarea` field you need to point one of the following checker functions (you can inherit `BxDolFormCheckerHelper` class and add own checker functions, you will need to point your custom class in Form Object `params` array):
    * **Length** - check value length, additional params must contain `min` and/or `max` values for checking.
    * **Date** - check if date is entered correctly.
    * **DateTime** - check if datetime is entered correctly.
    * **Preg** - check value with provided regular expression in `checker_params` field.
    * **Avail** - just check if value isn't 0 or empty string, additional function parameters are not used.
    * **Email** - check if value is written in valid email format.
    * **Captcha** - check if captcha is entered correctly, for `captcha` field type only.

* **checker_params** - serialized array of `checker_func` parameters.
* **checker_error** - error message to show in case of checking function returns false.
* **db_pass** - function to pass value through before saving to database and after restoring from database (for example when date need to be converted from/to timestamp value), available values are the following (you can inherit `BxDolFormCheckerHelper` class and add own **pass** functions, you will need to point your custom class in Form Object `params` array):
    * **Int** - convert value to integer.
    * **Float** - convert value to floating point number.
    * **Date** - convert value to timestamp value before saving to database, and convert from timespamp value after restoring from database.
    * **DateTime** - convert value to timestamp value before saving to database, and convert from timespamp value after restoring from database.
    * **Xss** - it warns you that this text can contain XSS vulnerabilities and you need to be extra careful with this, and always use Forms engine to output string to the browser or use `bx_process_output` if going to output text manually.
    * **XssHtml** - this text cam have HTML tags, so perform XSS vulnerabilies cleaning before saving to database.
    * **All** - do not perform any conversion and pass text as it is, be careful with this, use it only when no other function can be used, and make all necessary security checking by yourself.
    * **Preg** - perform regular expression on the text before saving data to database, regular expression can be provided in db_params field.
    * **Tags** - tags are validated and correctly joined using delimiter symbol.
    * **Categories** - categories are validated and correctly joined using delimiter symbol.
    * **Boolean** - this is used for checkboxes with 'on' value which need to be converted into boolean value.
    * **Set** - convert set of values into bit integer before saving to database, and restore bit integer into array of values upon restoration from database, it can be used for `select_multiple` and `checkbox_set` field types.  
**Please note** that values for this field must be 1,2,4,8,... (values of power of 2); the max number of values are 31 for 32bit hardware and 63 for 64bit hardware.

* **db_params** - serialized array of `db_pass` parameters.
* **editable** - allow to edit this field from admin forms builder.
* **deletable** - allow to delete this field from admin forms builder.

**4.** Add Form Fields and Form Displays associations, add records to `sys_form_display_inputs` table:

* **display_name** - name of the Form Display from `sys_form_displays` table.
* **input_name** - name of the Form Field from `sys_form_inputs` table.
* **visible_for_levels** - bit field with set of member level ids. To use member level id in bit field the level id minus 1 is used as power of 2, for example:
    * user level id = 1 -> 2^(1-1) = 1
    * user level id = 2 -> 2^(2-1) = 2
    * user level id = 3 -> 2^(3-1) = 4
    * user level id = 4 -> 2^(4-1) = 8
* **active** - **1** - form field displayed on form, or **0** - isn't displayed.
* **order** - fields are displayed in this order.

## Form Field Types

Detailed description of Form Field Types.

Almost all fields have the following common parameters:

* **object**
* **name**
* **type**
* **caption**
* **info**
* **required**
* **attrs**
* **attrs_tr**
* **attrs_wrapper**

We will not describe above list of parameters in every type, since they work the same way for all types.

The list below are field types with their unique parameters, which are designed especially for this field, or some parameters which work differently for the specified field type.

* **text** - text input field. It is displayed as regular single line text input.
Parameters:
    * **value** - default value, or empty if there is no default value.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
checker_func
Can be used here: `Length, Preg, Avail, Email` 
Make no sense to use it here: `Date, DateTime, Captcha`
    * **db_pass**
Can be used here: `Int, Float, Xss, All, Preg, Tags, Categories` 
Make no sense to use it here: `Date, DateTime, XssHtml, Boolean, Set`
* **password** - password input field. It is displayed as HTML input element with invisible input.
Parameters:
    * **value** - default value, or empty if there is no default value.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**  
Can be used here: `Length, Preg, Avail`.  
Make no sense to use it here: `Date, DateTime, Captcha, Email`.
    * **db_pass**  
Can be used here: `Xss, All`.  
Make no sense to use it here: `Int, Float, Date, DateTime, XssHtml, Boolean, Set, Preg, Tags, Categories`.
* **textarea** - multiline input field. It can be displayed as regular textarea field or as visual HTML editor.
Parameters:
    * **value** - default value, or empty if there is no default value.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - use visual editor or not.
* **0** - no visual editor, leave `textarea` field as it is.
* **1** - standard(default) visual editor.
* **2** - full visual editor.
* **3** - mini visual editor.
    * **checker_func**  
Can be used here: `Length, Preg, Avail`  
Make no sense to use it here: `Email, Date, DateTime, Captcha`
    * **db_pass**  
Can be used here: `Int, Float, Xss, XssHtml, All, Preg, Tags, Categories`  
Make no sense to use it here: `Date, DateTime, Boolean, Set`  
* **number** - number input field. It is displayed as HTL text input, but with limited width. Also some browsers can add additional controls to this field.  
Parameters:
    * **value** - default value, or empty if there is no default value.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
checker_func  
Can be used here: `Length, Preg, Avail`  
Make no sense to use it here: `Email, Date, DateTime, Captcha`  
* **db_pass**  
Can be used here: `Int, Float`   
Make no sense to use it here: `Xss, XssHtml, All, Preg, Tags, Categories, Date, DateTime, Boolean, Set`  
* **select** - select one from all available values. It is displayed as HTML combo-box.  
Parameters:
    * **value** - default value (array index of selected item from **values** array), or empty - if there is no default value.
    * **values** - serialized array of available values, or reference to predefined set of values.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.  
    * **checker_func**  
Can be used here: `Length, Preg, Avail`   
Make no sense to use it here: `Email, Date, DateTime, Captcha`  
    * **db_pass**  
Can be used here: `Int, Float, Xss, All, Preg`   
Make no sense to use it here: `Date, DateTime, Tags, Categories, XssHtml, Boolean, Set`    
* **select_multiple** - select one, multiple or all items from all available values. It is displayed as HTML multiple selection input.  
Parameters:  
    * **value** - default value (bit integer of array indexes of selected items from **values** array), or empty - if there is no default value.
    * **values** - serialized array of available values, or reference to predefined set of values. Array index must be power of 2. Max number of values is 31 on 32bit hardware or 63 on 64bit hardware.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**  
Can be used here: `Length, Preg, Avail`  
Make no sense to use it here: `Email, Date, DateTime, Captcha`  
    * **db_pass**  
Can be used here: `Int`  
Make no sense to use it here: `Float, Xss, All, Preg, Date, DateTime, Tags, Categories, XssHtml, Boolean, Set`  
* **switcher** - on/off switcher. It is displayed as custom HTML element with own styles, but on background it works as regular HTML checkbox element.   
Parameters:  
    * **value** - the value which will be submitted if switcher is on, if switcher is off - nothing is submitted.
    * **values** - not applicable here.
    * **checked** - if set to **1** then switcher is on by default, **0** - it is off by default.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**  
Can be used here: `Length, Preg, Avail`   
Make no sense to use it here: `Email, Date, DateTime, Captcha`  
    * **db_pass**  
Can be used here: `Int, Float, Xss, All, Preg, Boolean`   
Make no sense to use it here: `Date, DateTime, Tags, Categories, XssHtml, Set`  
* **checkbox** - one checkbox. Displayed as HTML checkbox input element.  
Parameters:  
    * **value** - the value which will be submitted if checkbox is checked, if checkbox isn't checked - nothing is submitted.
    * **values** - not applicable here.
    * **checked** - if set to **1** then checkbox is checked by default, **0** - it is unchecked by default.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**  
Can be used here: `Length, Preg, Avail`   
Make no sense to use it here: `Email, Date, DateTime, Captcha`  
    * **db_pass**  
Can be used here: `Int, Float, Xss, All, Preg, Boolean`   
Make no sense to use it here: `Date, DateTime, Tags, Categories, XssHtml, Set`  
* **checkbox_set** - set of checkboxes. It is displayed as set of checkboxes. It is displayed in one row if number of items is equal or less than 3 or every item is displayed on new line if there is more than 3 items in the set.  
Parameters:    
    * **value** - default value (bit integer of array indexes of selected items from **values** array), or empty - if there is no default value.
    * **values** - serialized array of available values, or reference to predefined set of values. Array index must be power of 2. Max number of values is 31 on 32bit hardware or 63 on 64bit hardware.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**  
Can be used here: `Length, Preg, Avail`   
Make no sense to use it here: `Email, Date, DateTime, Captcha`  
    * **db_pass**  
Can be used here: `Int`   
Make no sense to use it here: `Float, Xss, All, Preg, Date, DateTime, Tags, Categories, XssHtml, Boolean, Set`  
* **radio_set** - set of radio buttons. It is displayed as set of radio buttons. It is displayed in one row if number of items is equal or less than 3 or every item is displayed on new line if there is more than 3 items in the set.  
Parameters:  
    * **value** - default value (array index of selected radio button from **values** array), or empty - if there is no default value.
    * **values** - serialized array of available values, or reference to predefined set of values.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**  
Can be used here: `Length, Preg, Avail`   
Make no sense to use it here: `Email, Date, DateTime, Captcha`  
    * **db_pass**  
Can be used here: `Int, Float, Xss, All, Preg`     
Make no sense to use it here: `Date, DateTime, Tags, Categories, XssHtml, Boolean, Set`  
* **slider** - select some numeric value within the range using slider control. It is displayed as jQuery UI HTML control, but on background it works as regular HTML text input element.  
Parameters:  
    * **value** - default value in the format, or empty if there is no default value.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **attrs** - the following additional attributes can be used here:  
* **min** - minimal value, default is **0**.
* **max** - maximal value, default is **100**.
* **step** - value can be changed by this step only, default is **1**.  
    * **checker_func**  
Can be used here: `Length, Avail`   
Make no sense to use it here: `Preg, Email, Date, DateTime, Captcha`  
    * **db_pass**  
Can be used here: `Int, Float`  
Make no sense to use it here: `Xss, XssHtml, All, Preg, Tags, Categories, Date, DateTime, Boolean, Set`  
* **doublerange** - select range values within the range using slider control.  
Parameters:  
    * **value** - default value in the format **[min value]-[max value]**, for example **16-99**, or empty if there is no default value.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **attrs** - the following additional attributes can be used here:  
* **min** - minimal value, default is **0**.
* **max** - maximal value, default is **100**.
* **step** - value can be changed by this step only, default is **1**.  
    * **checker_func**  
Can be used here: `Length, Avail`     
Make no sense to use it here: `Preg, Email, Date, DateTime, Captcha`  
    * **db_pass**  
Can be used here: `Xss, All, Preg`   
Make no sense to use it here: `Int, Float, XssHtml, Tags, Categories, Date, DateTime, Boolean, Set`  
* **datepicker** - date selection control. It is displayed as HTML text input control, when clicking on this input then popup with date selector control is appeared.  
Parameters:  
    * **value** - default value, in the format **YYYY-MMM-DD**, or empty if there is no default value.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**   
Can be used here: `Date`   
Make no sense to use it here: `Length, Preg, Avail, Email, DateTime, Captcha`  
    * **db_pass**  
Can be used here: `Date`   
Make no sense to use it here: `Int, Float, Xss, All, Preg, Tags, Categories, DateTime, XssHtml, Boolean, Set`  
* **datetime** - date/time selection control.  
Parameters:  
    * **value** - default value, in the format **YYYY-MMM-DD HH:MM:SS**, or empty if there is no default value.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**  
Can be used here: `DateTime`   
Make no sense to use it here: `Length, Preg, Avail, Email, Date, Captcha`  
    * **db_pass**  
Can be used here: `DateTime`   
Make no sense to use it here: `Int, Float, Xss, All, Preg, Tags, Categories, Date, XssHtml, Boolean, Set`  
* **captcha** - image captcha. Displayed as image with some text along with HTML text input for entering displayed on the image text.  
Parameters:  
    * **value** - not applicable here.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**  
Can be used here: `Captcha`   
Make no sense to use it here: `Length, Preg, Avail, Email, Date, DateTime`    
    * **db_pass**  
Can be used here: `Xss, All, Preg`   
Make no sense to use it here: `Int, Float, Tags, Categories, Date, DateTime, XssHtml, Boolean, Set`  
* **hidden** - hidden input field. Displayed as hidden HTML input.  
Parameters:  
    * **value** - hidden input value.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**  
Can be used here: `Length, Preg, Avail, Email, Date, DateTime`   
Make no sense to use it here: `Captcha`  
    * **db_pass**  
Can be used here: `Int, Float, Xss, All, Preg, Tags, Categories, Date, DateTime, XssHtml, Boolean`   
Make no sense to use it here: `Set`  
* **file** - file upload input. Displayed as file upload HTML input.  
Parameters:  
    * **value** - not applicable here.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func**  
File name is passed for checking.   
Can be used here: `Avail, Length, Preg`   
Make no sense to use it here: `Email, Date, DateTime, Captcha`  
    * **db_pass**  
File can't be stored in the database, so this field isn't applicable here.  
* **files** - files upload input. Displayed as complex uploading HTML control.   
This control is too complex to describe it using default set of database fields, you need to use custom class to display this control.  
* **button** - button control. Displayes as HTML button element.  
Parameters:  
    * **value** - translatable button caption.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func** - not applicable here.
    * **db_pass** - not applicable here.  
* **image** - form image button. It is displayed as HTML form image input element.  
Parameters:  
    * **value** - not applicable here.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **attrs** - the following mandatory attribute must be specified here:  
* **src** - image URL.  
    * **checker_func** - not applicable here.
    * **db_pass** - not applicable here.  
* **reset** - form reset button. Displayed as HTML form reset input button. By clicking on this button the form is reset to its default state.  
Parameters:  
    * **value** - translatable button caption.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func** - not applicable here.
    * **db_pass** - not applicable here.  
* **submit** - form submit button. Displayed as HTML form submit input button. This button have the primary button style to distinguish it from other buttons. By clicking on this button the form is submitted.    
Parameters:  
    * **value** - translatable button caption.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func** - not applicable here.
    * **db_pass** - not applicable here.  
* **value** - just displaying value without any control.   
Parameters:  
    * **value** - the value to display.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func** - not applicable here.
    * **db_pass** - not applicable here.  
* **block_header** - start group of fields. Displayed as form fields divider with caption - then it can be collapsible or without caption - then it is just divider without any functionality.  
Parameters:  
    * **value** - not applicable here.
    * **values** - not applicable here.
    * **checked** - not applicable here.
    * **collapsed** - display group of field collapsed by default, **1** - the group is collapsed, **0** - expanded (default value).
    * **html** - not applicable here.
    * **checker_func** - not applicable here.
    * **db_pass** - not applicable here.  
* **custom** - custom control. You need custom class to display this control, so the exact used values are determined by particular realisation.
* **input_set** - set of other form controls.  
Parameters:  
    * **value** - not applicable here.
    * **values** - comma separated list of field names (by **name** field) of fields to display here.
    * **checked** - not applicable here.
    * **collapsed** - not applicable here.
    * **html** - not applicable here.
    * **checker_func** - not applicable here.
    * **db_pass** - not applicable here.  

## Using own class for custom behavior

It is possible to provide own class for displaying and processing the form. To do this you need to point it in `override_class_name` and `override_class_file` fields in `sys_objects_form` table. Your custom class must be inherited from `BxTemplFormView` class or its descendants.

## Displaying custom field control

It is possible to leave form field with default caption and override only the form field control.

To override some field you need to define the following function: 
`protected function genCustomInput[field name] ($aInput)`. 
Where [**field name**] is form field name. 
For example:

```php
    protected function genCustomInputCustom ($aInput) {
        return 
        'r: <input type="text" size="2" value="'.(isset($aInput['value'][0]) ? $aInput['value'][0] : '').'" name="'.$aInput['name'].'[]" />' .
        'g: <input type="text" size="2" value="'.(isset($aInput['value'][1]) ? $aInput['value'][1] : '').'" name="'.$aInput['name'].'[]" />' .
        'b: <input type="text" size="2" value="'.(isset($aInput['value'][2]) ? $aInput['value'][2] : '').'" name="'.$aInput['name'].'[]" />';
    }
```

**Please note:** it is not recommended to override form field with default types, the control should be overriden if it has `custom` field type.  

## Displaying custom field row

Form row consists of caption and control, by default it is displayed with default design and functionality. If you need to display some field with custom header and control you need to declare the following function: 
`protected function genCustomRow[field name] ($aInput)`. 
Where [**field name**] is form field name.

## Example of usage

Printing the form for adding new record to the database:

```php
bx_import('BxDolForm');
$oForm = BxDolForm::getObjectInstance('sample_form_objects', 'sample_form_objects_add'); // get form instance for specified form object and display
if (!$oForm)
    die('"sample_form_objects_add" form object or "sample_form_objects_add" display is not defined');
$oForm->initChecker(); // init form checker witout any data - adding new record
if ($oForm->isSubmittedAndValid())
    echo 'inserted id: ' . $oForm->insert (); // add new record to the database 
echo $oForm->getCode(); // display form 
```

Printing the form for editing existing record in the database:

```php
// $iEditId - ID of edited row, for example from _GET parameter
$oDb = BxDolDb::getInstance();
$sQuery = $oDb->prepare("SELECT * FROM `sample_input_types` WHERE id = ?", $iEditId);
$aRecord = $oDb->getRow();
if (!$aRecord)
    die("$iEditId record wasn't found.");

bx_import('BxDolForm');
$oForm = BxDolForm::getObjectInstance('sample_form_objects', 'sample_form_objects_edit'); // get form instance for specified form object and display
if (!$oForm)
    die('"sample_form_objects_edit" form object or "sample_form_objects_edit" display is not defined');
$oForm->initChecker($aRecord); // init form checker with edited data
if ($oForm->isSubmittedAndValid())
    echo 'updated: ' . $oForm->update ($iEditId); // update database
echo $oForm->getCode(); // display form 
```

SQL dump of required database data for above examples:

```sql
-- SQL dump of table with sample data:

CREATE TABLE IF NOT EXISTS `sample_input_types` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `text` varchar(255) NOT NULL,
  `date` int(11) NOT NULL,
  `datetime` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `checkbox` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `slider` int(11) NOT NULL,
  `doublerange` varchar(255) NOT NULL,
  `switcher` varchar(255) NOT NULL,
  `textarea` text NOT NULL,
  `select` int(11) NOT NULL,
  `select_multiple` varchar(255) NOT NULL,
  `checkbox_set` varchar(255) NOT NULL,
  `radio_set` int(11) NOT NULL,
  `custom` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- SQL dump of form object:

INSERT INTO `sys_objects_form` (`object`, `title`, `action`, `form_attrs`, `submit_name`, `table`, `key`, `uri`, `uri_title`, `params`, `deleteable`, `active`, `override_class_name`, `override_class_file`) VALUES
('sample_form_objects', 'Sample Form Object', 'samples/form_objects.php', '', 'do_submit', 'sample_input_types', 'id', '', '', 'a:1:{s:14:"checker_helper";s:25:"BxSampleFormCheckerHelper";}', 1, 1, 'BxSampleForm', 'samples/BxSampleForm.php');

-- SQL dump of form displays:

INSERT INTO `sys_form_displays` (`display_name`, `module`, `object`, `title`) VALUES
('sample_form_objects_add', 'sample', 'sample_form_objects', 'Add'),
('sample_form_objects_edit', 'sample', 'sample_form_objects', 'Edit');

-- SQL dump of form inputs:

INSERT INTO `sys_form_inputs` (`object`, `module`, `name`, `value`, `values`, `checked`, `type`, `caption`, `info`, `required`, `collapsed`, `html`, `attrs`, `attrs_tr`, `attrs_wrapper`, `checker_func`, `checker_params`, `checker_error`, `db_pass`, `db_params`, `editable`, `deletable`) VALUES
('sample_form_objects', 'custom', 'id', '', '', 0, 'hidden', 'ID', '', 0, 0, 0, '', '', '', '', '', '', '', '', 0, 0),
('sample_form_objects', 'custom',  'header_contact', '', '', 0, 'block_header', 'All possible form input types', '', 0, 0, 0, '', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'text', '', '', 0, 'text', 'Text', '', 1, 0, 0, '', '', '', 'avail', '', 'Text is required', 'Xss', '', 1, 1),
('sample_form_objects', 'custom',  'date', '', '', 0, 'datepicker', 'Date', '', 1, 0, 0, '', '', '', 'avail', '', 'Date is required', 'Date', '', 1, 1),
('sample_form_objects', 'custom',  'datetime', '', '', 0, 'datetime', 'Datetime', '', 0, 0, 0, '', '', '', '', '', '', 'DateTime', '', 1, 1),
('sample_form_objects', 'custom',  'number', '42', '', 0, 'number', 'Number', '', 0, 0, 0, '', '', '', '', '', '', 'Int', '', 1, 1),
('sample_form_objects', 'custom',  'checkbox', '1', '', 0, 'checkbox', 'Checkbox', '', 0, 0, 0, '', '', '', '', '', '', 'Xss', '', 1, 1),
('sample_form_objects', 'custom',  'file', '', '', 0, 'file', 'File', '', 0, 0, 0, '', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'image', '', '', 0, 'image', 'Image', '', 0, 0, 0, 'a:1:{s:3:"src";s:85:"http://demo.boonex.com/m/photos/get_image/browse/d7bd9bb01ce45617d709dfd47826d4a3.jpg";}', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'password', '', '', 0, 'password', 'Password', '', 0, 0, 0, '', '', '', '', '', '', 'Xss', '', 1, 1),
('sample_form_objects', 'custom',  'slider', '21', '', 0, 'slider', 'Slider', '', 0, 0, 0, 'a:2:{s:3:"min";i:16;s:3:"max";i:99;}', '', '', '', '', '', 'Int', '', 1, 1),
('sample_form_objects', 'custom',  'doublerange', '20-35', '', 0, 'doublerange', 'Doublerange', '', 0, 0, 0, 'a:2:{s:3:"min";i:16;s:3:"max";i:99;}', '', '', '', '', '', 'Xss', '', 1, 1),
('sample_form_objects', 'custom',  'hidden', '', '', 0, 'hidden', 'Hidden', '', 0, 0, 0, '', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'switcher', '1', '', 0, 'switcher', 'Switcher', '', 0, 0, 0, '', '', '', '', '', '', 'Xss', '', 1, 1),
('sample_form_objects', 'custom',  'button', '_Befriend', '', 0, 'button', 'Button', '', 0, 0, 0, '', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'reset', '_Befriend', '', 0, 'reset', 'Reset', '', 0, 0, 0, '', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'submit', '_Befriend', '', 0, 'submit', 'Submit', '', 0, 0, 0, 'a:1:{s:5:"class";s:23:"bx-def-margin-sec-right";}', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'textarea', '', '', 0, 'textarea', 'Textarea', '', 0, 0, 0, '', '', '', '', '', '', 'XssHtml', '', 1, 1),
('sample_form_objects', 'custom',  'select', '', 'a:3:{i:1;s:3:"one";i:2;s:3:"two";i:3;s:5:"three";}', 0, 'select', 'Select', '', 0, 0, 0, '', '', '', '', '', '', 'int', '', 1, 1),
('sample_form_objects', 'custom',  'select_multiple', '', 'a:3:{i:1;s:3:"one";i:2;s:3:"two";i:4;s:5:"three";}', 0, 'select_multiple', 'Select Multiple', '', 0, 0, 0, '', '', '', '', '', '', 'Set', '', 1, 1),
('sample_form_objects', 'custom',  'checkbox_set', '', 'a:3:{i:1;s:3:"one";i:2;s:3:"two";i:4;s:5:"three";}', 0, 'checkbox_set', 'Checkbox Set', '', 0, 0, 0, '', '', '', '', '', '', 'Set', '', 1, 1),
('sample_form_objects', 'custom',  'radio_set', '', 'a:3:{i:1;s:3:"one";i:2;s:3:"two";i:3;s:5:"three";}', 0, 'radio_set', 'Radio Set', '', 0, 0, 0, '', '', '', '', '', '', 'Int', '', 1, 1),
('sample_form_objects', 'custom',  'input_set', '', 'submit,reset', 0, 'input_set', 'Input Set', '', 0, 0, 0, '', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'custom', '', '', 0, 'custom', 'Custom', '', 0, 0, 0, '', '', '', '', '', '', 'Rgb', '', 1, 1),
('sample_form_objects', 'custom',  'value', 'вот...', '', 0, 'value', 'Value', '', 0, 0, 0, '', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'captcha', '', '', 0, 'captcha', 'Captcha', '', 0, 0, 0, '', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'header_submit', '', '', 0, 'block_header', '', '', 0, 0, 0, '', '', '', '', '', '', '', '', 1, 1),
('sample_form_objects', 'custom',  'do_submit', '_Submit', '', 0, 'submit', '', '', 0, 0, 0, '', '', '', '', '', '', '', '', 1, 1);

-- SQL dump of form inputs association with form display:

INSERT INTO `sys_form_display_inputs` (`display_name`, `input_name`, `visible_for_levels`, `active`, `order`) VALUES
('sample_form_objects_add', 'header_contact', 2147483647, 1, 10),
('sample_form_objects_add', 'text', 2147483647, 1, 20),
('sample_form_objects_add', 'date', 2147483647, 1, 30),
('sample_form_objects_add', 'datetime', 2147483647, 1, 40),
('sample_form_objects_add', 'number', 2147483647, 1, 50),
('sample_form_objects_add', 'checkbox', 2147483647, 1, 60),
('sample_form_objects_add', 'file', 2147483647, 1, 70),
('sample_form_objects_add', 'image', 2147483647, 1, 80),
('sample_form_objects_add', 'password', 2147483647, 1, 90),
('sample_form_objects_add', 'slider', 2147483647, 1, 100),
('sample_form_objects_add', 'doublerange', 2147483647, 1, 110),
('sample_form_objects_add', 'hidden', 2147483647, 1, 120),
('sample_form_objects_add', 'switcher', 2147483647, 1, 130),
('sample_form_objects_add', 'button', 2147483647, 1, 140),
('sample_form_objects_add', 'reset', 2147483647, 1, 150),
('sample_form_objects_add', 'submit', 2147483647, 1, 160),
('sample_form_objects_add', 'textarea', 2147483647, 1, 170),
('sample_form_objects_add', 'select', 2147483647, 1, 180),
('sample_form_objects_add', 'select_multiple', 2147483647, 1, 190),
('sample_form_objects_add', 'checkbox_set', 2147483647, 1, 200),
('sample_form_objects_add', 'radio_set', 2147483647, 1, 210),
('sample_form_objects_add', 'input_set', 2147483647, 1, 220),
('sample_form_objects_add', 'custom', 2147483647, 1, 230),
('sample_form_objects_add', 'captcha', 2147483647, 1, 240),
('sample_form_objects_add', 'value', 2147483647, 1, 250),
('sample_form_objects_add', 'header_submit', 2147483647, 1, 1000),
('sample_form_objects_add', 'do_submit', 2147483647, 1, 1001),
('sample_form_objects_edit', 'id', 2147483647, 1, 10),
('sample_form_objects_edit', 'text', 2147483647, 0, 20),
('sample_form_objects_edit', 'date', 2147483647, 1, 30),
('sample_form_objects_edit', 'datetime', 2147483647, 1, 40),
('sample_form_objects_edit', 'number', 2147483647, 1, 50),
('sample_form_objects_edit', 'checkbox', 2147483647, 1, 60),
('sample_form_objects_edit', 'password', 2147483647, 1, 70),
('sample_form_objects_edit', 'slider', 2147483647, 1, 80),
('sample_form_objects_edit', 'doublerange', 2147483647, 1, 90),
('sample_form_objects_edit', 'switcher', 2147483647, 1, 100),
('sample_form_objects_edit', 'textarea', 2147483647, 1, 110),
('sample_form_objects_edit', 'select', 2147483647, 1, 120),
('sample_form_objects_edit', 'select_multiple', 2147483647, 1, 130),
('sample_form_objects_edit', 'checkbox_set', 2147483647, 1, 140),
('sample_form_objects_edit', 'radio_set', 2147483647, 1, 150),
('sample_form_objects_edit', 'custom', 2147483647, 1, 160),
('sample_form_objects_edit', 'do_submit', 2147483647, 1, 1000);
```

Custom Form class:

```php
bx_import('BxTemplFormView');

class BxSampleForm extends BxTemplFormView {

    public function __construct ($aInfo, $oTemplate = false) {
        parent::__construct ($aInfo, $oTemplate);
    }
    
    /**
     * display input with 'custom' name 
     */
    protected function genCustomInputCustom ($aInput) {
        return 
        'r: <input type="text" size="2" value="'.(isset($aInput['value'][0]) ? $aInput['value'][0] : '').'" name="'.$aInput['name'].'[]" />' .
        'g: <input type="text" size="2" value="'.(isset($aInput['value'][1]) ? $aInput['value'][1] : '').'" name="'.$aInput['name'].'[]" />' .
        'b: <input type="text" size="2" value="'.(isset($aInput['value'][2]) ? $aInput['value'][2] : '').'" name="'.$aInput['name'].'[]" />';
    }

}

class BxSampleFormCheckerHelper extends BxDolFormCheckerHelper {
    
    var $_sDiv = ',';
    
    /**
     * prepare RBG values to save to the DB
     */
    function passRgb ($s) {
        if (!is_array($s))
            return false;

        $sRet = '';
        foreach ($s as $k => $v)
            $sRet .= (int)trim($v) . $this->_sDiv;

        return trim($sRet, $this->_sDiv);
    }
    
    /**
     * prepare RGB values to output to the screen
     */
    function displayRgb ($s) {
        return explode($this->_sDiv, $s);
    }
}
```

