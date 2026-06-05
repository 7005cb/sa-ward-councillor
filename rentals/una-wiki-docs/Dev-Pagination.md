It was noticed that many members want to remove total count from paginate. Also we noticed that counting all the results every time considerable slows down the site. Additionally investigation shows that most of the sites don't show total number of pages and/or it is not possible to jump throught several pages.

As the result we removed total count from paginate and now it is possible to go to the next or previous page only.

For the developers the difference is that you need to pass current number of items on the page, **plus one**, instead of passing total number of items. Plus one is needed to correctly determine the last page.

Two types of paginate presentation is supported:

* **getPaginate()** - default paginate, it is better to use it on the whole page.
* **getSimplePaginate()** - to get limited paginate, it is better to use in some boxes, where available space is limited or for ajax paginate.

The main paginate options are the following:

* **start** - position of the first item.
* **num** - number of available items on the page, it should be number of items per page + 1 (+1 is needed to correctly determine last page). It is possible to set this value automatically using `setNumFromDataArray` function.
* **per_page** - number of items displayed on the page.
* **page_url** - page URL to go through pages, special markers are automatically replaced.
* **on_change_page** - JavaScript code to be called on page change, special markers are automatically replaced.
* **info** - display info about the data, currently showed.
* **view_all_url** - URL for 'view all' page. This url is not showed by default. It is convinient to use it in conjunction with `getSimplePaginate` function.
* **view_all_caption** - optional caption for 'view all' link.

Available markers to replace in `page_url` and `on_change_page` parameters:

* **{per_page}** - current number of items to display per page.
* **{start}** - the number to display items starting from.

## Example of usage

```php
    // create paginate object
    bx_import('BxTemplPaginate');
    $oPaginate = new BxTemplPaginate(array(
        'page_url' => BX_DOL_URL_ROOT . 'test.php?start={start}&per_page={per_page}',
        'start' => (int)bx_get('start'),
        'per_page' => (int)bx_get('per_page'),
    ));

    // query data from database
    $oDb = BxDolDb::getInstance();
    $sQuery = $oDb->prepare('SELECT `ID`, `Subject` FROM `sys_email_templates` LIMIT ?, ?', $oPaginate->getStart(), $oPaginate->getPerPage() + 1); // we are trying to retrive +1 result more than we show on the page
    $aAll = $oDb->getAll($sQuery);

    // set current number of results, this functions automatically pops last row from data array, since it is needed to determine last page only
    $oPaginate->setNumFromDataArray($aAll);     

    // display data
    foreach ($aAll as $r) {
        echo bx_process_output($r['Subject']) . '<br />';
    }

    // display paginate
    echo $oPaginate->getPaginate();     
```
