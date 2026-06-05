Grid allows to display some data as grid with ready to use features:

* paginate
* reordering
* sorting
* search
* actions

The advantages of the new system:

**Less code to write** - so you can concentrate on the main functionality.  
**Flexibility** - you can turn on/off ready features or override it for the custom behavior.

Grid is working together with [[Pagination|Dev-Pagination]] to look through the data in the grid.

## Creating the new Grid object

**1.** Create Grid object, add record to `sys_objects_grid` table:

* **object** - name of the grid object, in the format: vendor prefix, underscore, module prefix, underscore, internal identifier or nothing; for example: `bx_profiles_admin` - to display profiles in admin panel.  
* **source_type** - type of the source data:  
    * **Sql** - the source is SQL query.
    * **Array** - the source is serialized array.
* **source** - the source data, different for each `source_type`:  
    * **Sql** - the SQL query string, without `ORDER BY` and `LIMIT` clauses, these clauses are added automatically for sorting, pagination and filtering.
    * **Array** - 2 dimentional serialized array string.
* **table** - table name (if `source_type` is `Sql`), to automatically update order field and delete records.
* **field_id** - name of the ID field.
* **field_order** - name of the order field.
* **field_active** - name of the field which determines if the row is active or disabled. This field is used to display row as disabled (ususally as grayed out). The following functions can be overrided for custom behavior:  
    * `_switcherChecked2State` and `_switcherState2Checked` - override these functions if value of `field_active` field is different from `0` and `1`.
    * `_enable` - for custom behavior upon activation/deactivation.
    * `_isRowDisabled` and `_isCheckboxDisabled` - for displaying disabled rows which is not related to `field_active` field.
    * `_isSwitcherOn` - to display rows which are active/disabled by default.  
* **paginate_url** - URL of the page for the grid, set it empty for AJAX paginate, or specify the url for regular page reloading in paginate, with the following markers:
    * **{start}** - starting record to show data from.
    * **{per_page}** - number of records per one page.
* **paginate_per_page** - number of records per one page.
* **paginate_simple** - show full or simple paginate, the following values are supported:  
    * **NULL** - show full(big) paginate.
    * **''**(empty string) - show simple(small) paginate.
    * **'some url'** - show simple(small) paginate with "View All" link to the specified URL.
* **paginate_get_start** - GET variable name for `start`.
* **paginate_get_per_page** - GET variable name for `per_page`.
* **filter_fields** - comma separated list of field names to search in; if field contains language key then it is better to specify it in `filter_fields_translatable`.
* **filter_fields_translatable** - comma separated list of field names to search in its translations; enter field name here if field contains language key.
* **filter_mode** - search mode:
    * **like** - use `SQL LIKE` expression for search, if `source_type` is not `Sql` it doesn't matter.
    * **fulltext** - use `MATCH ... AGAINST` expression for search, if `source_type` is not `Sql` it doesn't matter.
    * **auto** - use `like` or `fulltext`, depending on `useLikeOperator` setting option.
* **sorting_fields** - comma separated field names, which will be allowed for sorting, if field contains language keys, specify it in `sorting_fields_translatable` field instead.
* **sorting_fields_translatable** - comma separated field names which need to be sorted by translations, enter field name here if field contains language key.
* **visibile_for_levels** - bit field with set of member level ids. To use member level id in bit field - the level id minus 1 as power of 2 is used, for example:
    * user level id = 1 -> 2^(1-1) = 1
    * user level id = 2 -> 2^(2-1) = 2
    * user level id = 3 -> 2^(3-1) = 4
    * user level id = 4 -> 2^(4-1) = 8
* **override_class_name** - user defined class name which is derived from `BxTemplGrid`.
* **override_class_file** - the location of the user defined class, leave it empty if class is located in system folders.

**2.** Specify field names (columns in the grid) in `sys_grid_fields` table:

* **object** - name of the Grid object.
* **name** - name of the field, it must refer to the SQL field name in the case of `Sql source_type` or index of the 2 dimentional array in the case of `Array source_type`.
* **title** - title of the field, the language key.
* **width** - width of the column in % or px, pt, etc.
* **translatable** - if field contains language key and it is needed to display translation for this key - set it to **1**, by default - **0**.
* **params** - searialized array of additional params:
    * **display** - display function from `BxDolFormCheckerHelper` class, for example to convert unix timestamp to the regular date/time string.
    * **attr_cell** - tag attributes for the data cell.
    * **attr_head** - tag attributes for the header cell.
* **order** - order of the field.

There are some fields which are always available, additionally to the provided set of fields:

* **order** - display column as dragable handle, it makes sense if you have data ordered by some field and it is specified in `field_order`, `field_id` and `table` fields; reordering is not correctly working with paginate, so make sure that `paginate_per_page` number is big enough to show all records; reordering is working with `Sql source_type`.
* **checkbox** - display column with checkboxes, so several records can be selected for bulk action; you need to specify `field_id` field, so every checkbox have unique row id; you need to specify **bulk** actions separately in `sys_grid_actions` table; you can override `_isCheckboxSelected` function to display checkbox as checked by default.
* **actions** - display column with single actions, displayed as buttons; you need to specify `field_id` field, so every action is provided with unique row id; you need to specify **single** actions separately in `sys_grid_actions` table.

**3.** Add actions to `sys_grid_actions` table:

* **object** - name of the Grid object.
* **type** - action type, one of the following:
    * **bulk** - bulk action, to perform on the set of records, the action is usually displaed below the grid.
    * **single** - simple action, to perform on one record, the action is usually displayed in the grid row.
    * **independent** - independent actionm which is not related to any rowm the action is usually displayed above the grid.
* **name** - action name.
* **title** - title of the action, the language key.
* **icon** - display action as icon, title need to be empty in this case.
* **confirm** - ask confirmation before performing the action, 0 or 1.
* **order** - order of the action in particular actions set by type.  

Usually you need to handle actions manually, but there are several actions which are available by default:

* **delete** - delete the record, it works automatically when `source_type` is `Sql` and `field_id`, `table` fields are specified.

## Displaying custom cell

Cell is displayed with default design. It is possible to easily customize its design by specifying custom attributes as `attr_cell` in `params` field in `sys_grid_fields` table.

If it is not enough, you can customize it even more by adding the method to your custom class with the following format: 
`_getCell[field name]`
where `[field name]` is the name of the field you want to have custom look with the capital first letter. 
For example:

```php

protected function _getCellStatus ($mixedValue, $sKey, $aField, $aRow) {        

    $sAttr = $this->_convertAttrs(
        $aField, 'attr_cell',
        false, 
        isset($aField['width']) ? 'width:' . $aField['width'] : false // add default styles
    );
    return '<td ' . $sAttr . '><span style="background-color:' . ('Active' == $mixedValue ? '#cfc' : '#fcc') . '">' . $mixedValue . '</span></td>';
} 
```

Above example is displaying user's status using different colors depending on the status value. Please note that you need to convert attributes by adding some default classes or styles if you need.

## Displaying custom column header

This is working similar to displaying custom cell. It easily customize its design by specifying custom attributes as `attr_head` in `params` field in `sys_grid_fields` table.

If it is not enough, you can customize it even more by adding the method to your custom class with the following format: 
`_getCellHeader[field name]`  
where `[field name]` is the name of the field you want to have custom look with the capital first letter. 
For example:

```php

protected function _getCellHeaderStatus ($sKey, $aField) { 
    $s = parent::_getCellHeaderDefault($sKey, $aField);
    return preg_replace ('/<th(.*?)>(.*?)<\/th>/', '<th$1><img src="' . BxDolTemplate::getInstance()->getIconUrl('user.png') . '"></th>', $s);
}

```

The above example replaces column header text with the image.

## Displaying custom action

All actions are displayed as buttons. Bulk and independent actions are displaed as big buttons and single actions are displayed as small buttons.

It is possible to completely customize it by adding the following method to your custom class:   
`_getAction[action name]`  
where `[action name]` is the action name with the capital first letter. 
For example:

```php

protected function _getActionCustom1 ($sType, $sKey, $a, $isSmall = false) {
    $sAttr = $this->_convertAttrs(
        $a, 'attr',
        'bx-btn bx-def-margin-sec-left' . ($isSmall ? ' bx-btn-small' : '') // add default classes
    );
    return '<button ' . $sAttr . ' onclick="$(this).off(); alert(\'default behaviour is overrided, so the action is not performed\');">' . $a['title'] . '</button>';
}

```

The above example disables default onclick event and just displays an alert. Please note that you need to convert attributes by adding some default classes or styles if you need.

## Add action handler

As it was mentioned earlier only several actions can be handled automatically, all other actions must be processed manually. To add action handler you need to add method to your custom class with the following format:   
`performAction[action name]`  
where `[action name]` is the action name with the capital first letter. 
For example:

```php

public function performActionApprove() {

    $iAffected = 0;
    $aIds = bx_get('ids');
    if (!$aIds || !is_array($aIds)) {
        $this->_echoResultJson(array());
        exit;
    }

    $aIdsAffected = array ();
    foreach ($aIds as $mixedId) {
        if (!$this->_approve($mixedId))
            continue;
        $aIdsAffected[] = $mixedId;
        $iAffected++;
    }

    $this->_echoResultJson(array(
        'msg' => $iAffected > 0 ? sprintf("%d profiles successfully activated", $iAffected) : "Profile(s) activation failed", 
        'grid' => $this->getCode(false),
        'blink' => $aIdsAffected,
    ));
}

protected function _approve ($mixedId) {
    $oDb = BxDolDb::getInstance();
    $sTable = $this->_aOptions['table'];
    $sFieldId = $this->_aOptions['field_id'];
    $sQuery = $oDb->prepare("UPDATE `{$sTable}` SET `Status` = 'Active' WHERE `{$sFieldId}` = ?", $mixedId);
    return $oDb->query($sQuery);
}

```

The action can be used as `single` or `bulk`, in the case of `single` action `ids` array always has one element.

As the result, action must outputs JSON array, which is done by `_echoResultJson` function. The defined indexes in the array determines behavior after action is performed, the following behaviors are supported:

* **msg** - display javascript alert message.
* **grid** - reload grid data with the provided HTML code.
* **popup** - display popup with the provided HTML code.
* **blink** - highlight(blink effect) the specified rows, by the ids.

## Example of usage

Printing the grid:

```php

bx_import('BxDolGrid');
$oGrid = BxDolGrid::getObjectInstance('sample'); // it automatically creates instance of default or custom class by object name
if ($oGrid)
    echo $oGrid->getCode(); // print grid object 

```

SQL dump of required for example database data:

```php

-- SQL dump of grid object:

INSERT INTO `sys_objects_grid` (`object`, `source_type`, `source`, `table`, `field_id`, `field_order`, `paginate_url`, `paginate_per_page`, `paginate_simple`, `paginate_get_start`, `paginate_get_per_page`, `filter_fields`, `filter_mode`, `sorting_fields`, `override_class_name`, `override_class_file`) VALUES
('sample', 'Sql', 'SELECT `ID`, `NickName`, `Email`, `City`, `Status` FROM `Profiles` WHERE `Role` != 3 ', 'Profiles', 'ID', 'Education', '', 2, 'faq.php', 'start', '', 'NickName,City,Headline,DescriptionMe,Tags', 'auto', 'ID,NickName,Email,City', 'BxGridMy', 'samples/BxGridMy.php');


-- SQL dump of grid object fields:

INSERT INTO `sys_grid_fields` (`object`, `name`, `title`, `width`, `params`, `order`) VALUES
('sample', 'order', '', '1%', '', 1),
('sample', 'checkbox', 'Select', '2%', '', 2),
('sample', 'ID', 'id', '7%', '', 3),
('sample', 'NickName', 'Username', '20%', '', 4),
('sample', 'Email', 'Email', '20%', '', 5),
('sample', 'actions', 'Actions', '20%', '', 8),
('sample', 'City', 'City', '20%', '', 6),
('sample', 'Status', 'Status', '10%', '', 7);


-- SQL dump of grid object actions:

INSERT INTO `sys_grid_actions` (`object`, `type`, `name`, `title`, `confirm`, `order`) VALUES
('sample', 'bulk', 'delete', '_Delete', 1, 1),
('sample', 'bulk', 'approve', '_Approve', 0, 2),
('sample', 'bulk', 'custom1', '_Custom1', 0, 4),
('sample', 'bulk', 'custom2', '_Custom2', 0, 5),
('sample', 'single', 'delete', '_Delete', 1, 1),
('sample', 'single', 'edit', '_Edit', 0, 2),
('sample', 'independent', 'add', '_Add record', 0, 1),
('sample', 'independent', 'settings', '_Settings', 0, 2);

```

Custom grid class:

```php

bx_import('BxTemplGrid');

class BxGridMy extends BxTemplGrid {

    public function __construct ($aOptions, $oTemplate = false) {
        parent::__construct ($aOptions, $oTemplate);
    }

    
    /**
     * add js file for AJAX form submission
     */
    protected function _addJsCss() {
        parent::_addJsCss();
        $this->_oTemplate->addJs('jquery.form.js');
    }

    
    /**
     * 'add' action handler
     */
    public function performActionAdd() {

        $sAction = 'add';

        $aForm = array(
            'form_attrs' => array(
                'id' => 'sample-add-form',    
                'action' => 'grid.php?o=' . $this->_sObject . '&a=' . $sAction, // grid.php is usiversal actions handler file, we need to pass object and action names to it at least
                'method' => 'post',
            ),
            'params' => array (
                'db' => array(
                    'table' => 'Profiles', 
                    'key' => 'ID', 
                    'submit_name' => 'do_submit',
                ),
            ),
            'inputs' => array(
                'NickName' => array(
                    'type' => 'text',
                    'name' => 'NickName',
                    'caption' => _t('Username'),
                    'required' => true,
                    'checker' => array(
                        'func' => 'length',
                        'params' => array(1, 150),
                        'error' => _t( 'Username is required' )
                    ),                    
                    'db' => array (
                        'pass' => 'Xss',  
                    ),
                ),
                'Email' => array(
                    'type' => 'text',
                    'name' => 'Email',
                    'caption' => _t('Email'),
                    'required' => true,
                    'checker' => array(
                        'func' => 'email',
                        'error' => _t( '_Incorrect Email' )
                    ),
                    'db' => array (
                        'pass' => 'Xss',  
                    ),
                ),
                'City' => array(
                    'type' => 'text',
                    'name' => 'City',
                    'caption' => _t('City'),
                    'required' => true,
                    'checker' => array(
                        'func' => 'length',
                        'params' => array(1, 150),
                        'error' => _t( 'City is required' )
                    ),
                    'db' => array (
                        'pass' => 'Xss',  
                    ),
                ),

                'submit' => array(
                    'type' => 'input_set',
                    0 => array (
                        'type' => 'submit',
                        'name' => 'do_submit',
                        'value' => _t('_Submit'),
                    ),
                    1 => array (
                        'type' => 'reset',
                        'name' => 'close',
                        'value' => _t('Close'),
                        'attrs' => array(
                            'onclick' => "$('.dolPopup:visible').dolPopupHide()",
                            'class' => 'bx-def-margin-sec-left',
                        ),
                    ),
                ),

            ),
        );

        bx_import('BxTemplFormView');
        $oForm = new BxTemplFormView($aForm);
        $oForm->initChecker();

        if ($oForm->isSubmittedAndValid()) { // if form is submitted and all fields are valid

            $iNewId = $oForm->insert (array(), true); // insert record to database
            if ($iNewId)
                $aRes = array('grid' => $this->getCode(false), 'blink' => $iNewId); // if record is successfully added, reload grid and highlight added row
            else
                $aRes = array('msg' => "Error occured"); // if record adding failed, display error message

            $this->_echoResultJson($aRes, true);

        } else { // if form is not submitted or some fields are invalid, display popup with form

            bx_import('BxTemplFunctions');
            // we need to use 'transBox' function to properly display 'popup'
            $s = BxTemplFunctions::getInstance()->transBox('
                <div class="bx-def-padding bx-def-color-bg-block">' . $oForm->getCode() . '</div>
                <script>
                    $(document).ready(function () {
                        $("#sample-add-form").ajaxForm({ 
                            dataType: "json",
                            beforeSubmit: function (formData, jqForm, options) {
                                bx_loading($("#' . $aForm['form_attrs']['id'] . '"), true);
                            },
                            success: function (data) {
                                $(".dolPopup:visible").dolPopupHide();
                                glGrids.' . $this->_sObject . '.processJson(data, "' . $sAction . '");
                            }
                        });
                    });
                </script>');

            $this->_echoResultJson(array('popup' => $s), true);

        }
    }

    
    /**
     * 'approve' action handler
     */
    public function performActionApprove() {

        $iAffected = 0;
        $aIds = bx_get('ids');
        if (!$aIds || !is_array($aIds)) {
            $this->_echoResultJson(array());
            exit;
        }

        $aIdsAffected = array ();
        foreach ($aIds as $mixedId) {
            if (!$this->_approve($mixedId))
                continue;
            $aIdsAffected[] = $mixedId;
            $iAffected++;
        }

        $this->_echoResultJson(array(
            'msg' => $iAffected > 0 ? sprintf("%d profiles successfully activated", $iAffected) : "Profile(s) activation failed", 
            'grid' => $this->getCode(false),
            'blink' => $aIdsAffected,
        ));
    }

    
    /**
     * helper funtion for 'approve' action handler
     */
    protected function _approve ($mixedId) {
        $oDb = BxDolDb::getInstance();
        $sTable = $this->_aOptions['table'];
        $sFieldId = $this->_aOptions['field_id'];
        $sQuery = $oDb->prepare("UPDATE `{$sTable}` SET `Status` = 'Active' WHERE `{$sFieldId}` = ?", $mixedId);
        return $oDb->query($sQuery);
    }

    
    /**
     * custom cell look for 'Status' field
     */
    protected function _getCellStatus ($mixedValue, $sKey, $aField, $aRow) {        

        $sAttr = $this->_convertAttrs(
            $aField, 'attr_cell',
            false, 
            isset($aField['width']) ? 'width:' . $aField['width'] : false // add default styles
        );
        return '<td ' . $sAttr . '><span style="background-color:' . ('Active' == $mixedValue ? '#cfc' : '#fcc') . '">' . $mixedValue . '</span></td>';
    }

    
    /**
     * custom column header look for 'Status' field
     */
    protected function _getCellHeaderStatus ($sKey, $aField) { 
        $s = parent::_getCellHeaderDefault($sKey, $aField);
        return preg_replace ('/<th(.*?)>(.*?)<\/th>/', '<th$1><img src="' . BxDolTemplate::getInstance()->getIconUrl('user.png') . '"></th>', $s);
    }

    
    /**
     * custom behavior for 'custom1' action
     */
    protected function _getActionCustom1 ($sType, $sKey, $a, $isSmall = false) {
        $sAttr = $this->_convertAttrs(
            $a, 'attr',
            'bx-btn bx-def-margin-sec-left' . ($isSmall ? ' bx-btn-small' : '') // add default classes
        );
        return '<button ' . $sAttr . ' onclick="$(this).off(); alert(\'default behaviour is overrided, so the action is not performed\');">' . $a['title'] . '</button>';
    }
}

```
