Files can be attached to any form with ease and flexibility. 
Because any attached file can have some associated information, we had to add nested forms to implement this.

## Usage

To add file to any form, use the following form field array:

```php
'attachment' => array(
    'type' => 'files', // this is new form type, which enable upholders automatically
    'storage_object' => 'sample', // the storage object, where uploaded files are going to be saved
    'images_transcoder' => 'sample2', // images transcoder object to use for images preview
    'uploaders' => array ('sys_simple', 'sys_html5'), // the set of uploaders to use to upload files 
    'multiple' => true, // allow to upload multiple files per one upload
    'ghost_template' => $mixedGhostTemplate, // template for nested form
    'name' => 'attachment', // name of file form field, resulted file id is assigned to this field name
    'caption' => _t('Attachments'), // form field caption 
), 
```

The only uploaders available for now are:

* **sys_simple** - upload files using standard HTML forms
* **sys_html5** - upload files using AJAX uploader with multiple files selection support (without flash), it works in Firefox and WebKit(Safari, Chrome) browsers only, but has fallback for other browsers (IE, Opera).

The following uploaders are going to be added:

* **URL** - upload file from some URL
* **Camera** - take picture from camera and upload it
* **Import** - import files from other storage engines

Nested form is not used by default, so if you don't pass anything in `$mixedGhostTemplate` variable, then only file id is passed upon form submission. If you need custom nested form you can do it using the following ways:

1. **Pass template as string** - just plain string with HTML, for example:          

        <div id="bx-uploader-file-{file_id}" class="bx-uploader-ghost">
            <div style="border:2px dotted green; padding:10px; margin-bottom:10px;">
                <input type="hidden" name="f[]" value="{file_id}" />
                {file_name} <br />
                <a href="javascript:void(0);" onclick="{js_instance_name}.deleteGhost('{file_id}')">delete</a>
            </div>
        </div>

2. **Pass form array** - regular form array, but with `inputs` array only, for example:  

        $mixedGhostTemplate = array(
                'inputs' => array(
                    'file_name' => array(
                        'type' => 'text',
                        'name' => 'file_name[]',
                        'value' => '{file_title}',
                        'caption' => _t('Caption'),
                    ),
                    'file_desc' => array(
                        'type' => 'textarea',
                        'name' => 'file_desc[]',
                        'caption' => _t('Description'),
                    ),
                ),
        );  
Array is automatically modified to add necessary form attributes to work as nested form, file id field is added automatically as hidden input as well.

3. **Pass instance of BxDolFormNested class** - use `BxDolFormNested` class or its custom subclass; to create instance use the same form array as in the previous variant, for example:  

        bx_import('BxDolFormNested');
        $oFormNested = new BxDolFormNested('attachment', $aFormNested, 'do_submit');
    * `'attachment'` is the name of file form field from main form.  
    * `$aFormNested` is form array from previous example.  
    * `'do_submit'` is main form submit_name; field name of submit form input to determine if form is submitted or not.

All 3 variants can have the following replace markers to substitute with real values:        

* `{file_id}` - uploaded file id
* `{file_name}` - uploaded file name with extension
* `{file_title}` - uploaded file name without extension
* `{file_icon}` - URL to file icon automatically determined by file extension
* `{js_instance_name}` - instance of BxDolUploader javascript class

Finally, the whole example may look like this:

```php
    require_once('./inc/header.inc.php');
    require_once(BX_DIRECTORY_PATH_INC . "languages.inc.php");
    require_once(BX_DIRECTORY_PATH_INC . "params.inc.php");
    require_once(BX_DIRECTORY_PATH_INC . "design.inc.php");

    $aFormNested = array(
        'inputs' => array(
            'file_name' => array(
                'type' => 'text',
                'name' => 'file_name[]',
                'value' => '{file_title}',
                'caption' => _t('Caption'),
            ),
            'file_desc' => array(
                'type' => 'textarea',
                'name' => 'file_desc[]',
                'caption' => _t('Description'),
            ),
        ),
    );

    bx_import('BxDolFormNested');
    $oFormNested = new BxDolFormNested('attachment', $aFormNested, 'do_submit');

    $aForm = array(
        'form_attrs' => array(
            'id' => 'my_form',
            'method' => 'post',
        ),
        'params' => array (
            'db' => array(
                'submit_name' => 'do_submit',
            ),
        ),
        'inputs' => array(
            'name' => array(
                'type' => 'text',
                'name' => 'name',
                'caption' => _t('Name'),
                'required' => true,
                'checker' => array(
                    'func' => 'length',
                    'params' => array(1, 150),
                    'error' => _t( 'Name is required' )
                ),
            ),
            'text' => array(
                'type' => 'textarea',
                'name' => 'body',
                'caption' => _t('Text'),
            ),     
            'attachment' => array(
                'type' => 'files',
                'storage_object' => 'sample',
                'uploaders' => array ('sys_simple', 'sys_html5'),
                'multiple' => true,
                'ghost_template' => $oFormNested,
                'name' => 'attachment',
                'caption' => _t('Attachments'),
            ),
            'submit' => array(
                'type' => 'submit',
                'name' => 'do_submit',
                'value' => _t('_Submit'),
            ),
        ),
    );

    bx_import('BxTemplFormView');
    $oForm = new BxTemplFormView($aForm);
    $oForm->initChecker();
    if ( $oForm->isSubmittedAndValid() ) {
        // handle form saving here
    }

    $oTemplate = BxDolTemplate::getInstance();
    $oTemplate->setPageNameIndex (BX_PAGE_DEFAULT);
    $oTemplate->setPageHeader (_t("_CONTACT_H"));
    $oTemplate->setPageContent ('page_main_code', $oForm->getCode());
    PageCode();
```
