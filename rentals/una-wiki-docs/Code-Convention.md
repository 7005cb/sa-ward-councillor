**UNA** programming approaches will help everyone to write more secure and easy to read code when you developing modules and modifications.

You should write **clean**, **readable** code. It's recommended you follow the guidelines posted here. You should also keep structure in mind, and take advantage of extra white space to increase readability.

You should also comment your code so others can understand what you are trying to achieve.

In addition to the above, the following should also be kept in mind when messing with templates and other related voodoo.

You should take advantage of **UNA page system** when possible and keep your content within page blocks. You should also use the default design box when using page blocks.

You should also keep HTML and CSS in a template and not within the code itself. 

## Small programming remarks

1. Get rid of short **php tag**.

2. Get rid of closing **php tag** at the end of file.

3. Change **license text** in the beginning of php file.

4. Make sure that all classes are inherited from **````BxDol````** (there is **````no BxDolMistake class````** now).

5. Make sure that parent class is called in the constructor.

6. Remove any **````require_once````**, replace with **````bx_import````**. ___Don't use ````require_once````___ at all - the only place you can use in the files like **index.php**, **faq.php**, etc - the files which are actually displayed in the browser.

7. Use ````bx_import```` in the beginning of the file if class is used in all cases, for example in the constructor. If class is used sometimes and not often, then place ````bx_import```` inline.

8. Always set error reporting to E_ALL and get rid of any warnings and even notices.

9. Check for **````BX_DOL````** define in all **"include" files**, to prevent direct execution.

10. Replace **````RSSAggrCont````** html with **````BxTemplFunctions::getInstance()->getRssHolder````**.

11. Use **````BxTemplPaginate````** instead of **````BxDolPaginate````**.

12. Get rid of **````GLOBALS['some object']````**, remake to **````Class::getInstance````** or **````bx_instance()````** call.

13. Use **````BxDolProfileQuery````** for all SQL queries related to basic profiles functionality, if there is no suitable function there, create one, hold on here for a while.

14. Remove **````bx_import('BxDolAlerts');````** This class is imported in **header.inc.php** and available everywhere.

15. Don't use **````$_page````** and **````$_page_cont````** global variables, use template class with appropriate functions instead.

16. Use only predefined constants for **name_index(PageNameIndex)**.

17. Add more classes to templates folder, to allow to customise it in custom template.

## Class Names

All Class Names must begin with the prefix, and each word with a capital letter to avoid identical names and to make more unique classes written by the given developer. 

Example:````BxMyClassName````

## Common design element in user and studio parts

All classes which are used in user and studio parts need Template parameter in constructor to pass the right template object.

The following classes are already changed and call to this functions need to be changed, where needed. In user part template object can be detected by default, but in admin part Template object need to be passes explicitly **````BxTemplForm````**
**````BxTemplPaginate````**

more is coming... feel free to ask and add 

## Singleton for one instance classes
Implement singleton interface for the following classes (change **````new Class()````** to **````Class::getInstance````**): 
* **````BxDolSubscriptions````**
* **````BxDolPermalinks````**
* **````BxTmplMenu````**
* **````BxTmplFunctions````**
* **````BxTemplConfig````**
* **````BxDolTemplate````**
* **````BxDolTemplateAdmin````**
* **````BxDolModuleDb (or remake it BxDolService at least)````**
* **````BxDolDb````**
* **````BxDolParams````**
* **````BxDolProfileQuery````**

## Variable Declarations: Names

Given no strict data classification in PHP, all Variable Names must begin with a lower case letter of the first character in the name of a particular data type prefix.

**Data type prefixes:**

*  **i** Integer
*  **f** Float, Double
*  **s** String
*  **a** Array
*  **r** Resource
*  **b** Boolean
*  **is** Boolean


Following the first letter every word used in the **Variable Name** must begin with a capital letter.

Example:
```php						
			$sName = "Variable Value";
			$iCnt = 0; $iCnt++;
			$rMyFile = fopen ("myfile.txt", "r");
			var $isWritable;
```
## Function Declaration: Names and Formatting

All Function Names should start with a lower case letter, and each word should begin with a capital letter. Curly braces used in the list of parameters must be given on a new line instead of following the brackets.

Example:
```php
			function myFunction($iNumber, $sName)
			{ 
				//code is here
			}
```
## Language Structures Formatting
Language structures such as **if ... else**, **for**, **foreach**, **while**, etc. must be presented in the following manner:
* To provide better readability of the code, there should be a space between the name of the structure and the following parenthesis
* For the same reason, statements concluded within the brackets should be separated with a break
* A similar rule applies to ternary operator
* There is no space after the left parenthesis and before the right parenthesis
* Open curly brace must be on the same line, and closing curly brace must be on the new line 

Example:
```php
			foreach ($aNames as $sKey => $sVal) { 
				//code here
			}
```
switch construction
* The above rules must be applied for the switch construction itself.
* The inner case-statements should be indented with the tab.
* The inner case code should also be distinguished with an additional tab. 

Example:
```php
			switch ($iNumber) { 
				case 1: //code here break;
				case 2:
					//code here
					break;
				default:
					//code here
			}
```
## Database Tables

Tables used in the extensions must be named with the prefix (vendor and extension name).

Example

`````````super_blog_posts, super_blog_comments`````````

Where **super_** is vendor prefix of **"Super Programmers"** company and **blog_** is module prefix of **"Blogs"** module. 

## Database queries and data filtering

Get rid of **process_db_input**, **process_pass_data** and **BxDolDb::unescape** functions !

* to validate user input always use **bx_process_input**
* to save data to database always use db prepare statements
* to output data always use **bx_process_output**

apply above approach to any user data - **GET**, **POST**, **REQUEST**, **COOKIE**, or any other sources of untrusted data!

always use the same data type for the same data in both function **bx_process_input** and **bx_process_output**

Call **BxDolDb::prepare** function in particular db class function for your functionality. If there is no such class - create it, every piece of code should have SQL queries in separate class.

Sample db classes are:
	
	BxDolSessionQuery for BxDolSession
	BxDolVotingQuery for BxDolVoting

Don't call `````````BxDolDb::prepare````````` function in the code, use it only in db class function just before executing the query. If it is impossible or totally inconvenient to use "prepare" function (for example BxDolSessionQuery::save), then use bxDolDb::escape function.

Don't call bx_process_input and/or bx_process_output in db class function, call these function in the code. It is better to call bx_process_input function as earlier as possible, before using any user/untrusted input.

It is better to call bx_process_output function as late as possible, just before printing it out. Don't forget about other functions for output:

* bx_js_string
* bx_html_attribute

## HTML

Your HTML should be written in a readable manner. You should use proper indentation for structure, like so:
```html
	<body>
		<div id="header">
			<div id="logo">
				<img src="images/logo.png" alt="logo">
			</div>
		</div>
	</body>
```
You should take advantage of the default class attributes as defined in the uni template. You should keep the use of custom classes and id attributes to a minimum.
CSS

Your CSS should also follow a set of standards, which ensures readable code. Blocks should be organized like so:
```css
	selector {
		property: value;
	}
```
The following should be noted about the above:

* Each selector should be on its own line, with an empty line separating each.
* Each property should be on its own line, with a single indentation. 

You should also list properties alphabetically, like so:
```css
	selector {
		background-color: #fff;
		color: #333;
	}
```
Vendor-specific properties (e.g., **-moz-border-radius**) should precede their generic counterpart.

It's encouraged you check your **(X)HTML** and CSS against the **W3C's** validator services:

* **http://jigsaw.w3.org/css-validator/**
* **http://validator.w3.org/**

## Templates

* Fugue icons only, don't change original names from the set !
* Use default styles as much as possible, add them as additional class, refer to default.css for the whole list:
	* colors - use predefined styles from default.css for all colors:
        * page background
        * block background
        * form background
    * margin/padding
    * border
    * font
* Predefined styles for all looks alike elements, like thumbs, etc.
* Validate HTML/CSS for every page (in Web Developer Firefox toolbar: Tools -> Validate *)
* Write css with the following order:
      * positioning (display, float, clear, visibility, position, top, right, bottom, left, z-index, etc)
      * size (width, height, overflow, padding, margin, etc)
      * border (border, outline, etc)
      * colors (opacity, background*)
      * font (font-*, text-*, line-height, white-space, vertical-align, color, etc)
      * others (list-style, cursor, etc)
* css classes and ids must be named as following:
   * **bx-*** - system css classes and ids
   * **bx-pre-*** - module css classes and ids
   * if there are several classes for some particular functionality, add some prefix for it too, for example all comments classes should have classes and ids with the following prefix bx-cmts-*


## Files Header and Footer

Make sure that every file has the following header:
```php
	<?php defined('BX_DOL') or die('hack attempt');
	/**
	 * Copyright (c) BoonEx Pty Limited - http://www.boonex.com/
	 * CC-BY License - http://creativecommons.org/licenses/by/3.0/
	 *
	 * @defgroup    UnaCore Una Core
	 * @{
	 */
```
and the following footer (closing @defgroup definition):
```php
	/** @} */ 
```
**@defgroup** can vary depending on particular files, refer to the next section for more info.

## Grouping the files

Use the following groups and subgroups in header of every file:

* for all files in /inc/ folder

		@defgroup    UnaCore Una Core

* for all files in /templates/[base|tmpl_uni] folder

		@defgroup    UnaView Una Core Representation classes
		@ingroup     UnaCore  

* for all files in root - / folder only

		@defgroup    UnaEndUser Una Core End User Pages 
		@ingroup     UnaCore  

* for all files in /studio/ folder

		@defgroup    UnaStudio Una Studio

* for all files in /modules/ folder

		@defgroup    MyModule Full name of MyModule
		@ingroup     UnaModules  

## Lists

For lists use the following format:

My list:

	- one
	- two
	- three

so every list item must begin with dash (-)

For multi indented list use the following format:

	- first
		- a
		- b
	- second
		- c
		- d

## Code blocks

Code blocks must be enclosed in **@code** and **@endcode** tags.
For example:
```php
	@code
	echo "Hello World!"; 
	@endcode
```
## Sections

Use **@sections** keyword for different sections in the documentation block.
For example:
```php
	/**
	 * My class
	 *
	 * My class desc goes here.
	 *
	 * @section example Example of usage:
	 * @code
	 *      $o = new MyClass();
	 * @endcode
	 *
	 * @section alerts Alerts:
	 * myalert - with the following parameters
	 * - $iObjectId - my content id
	 * - $iSenderId - user who performed an action with my content
	 *
	 */
``` 

Standard sections are:

* @section alerts Alerts:
* @section acl Memberships/ACL:
* @section example Example of usage:

## Line breaks

To make line break in documentation you need to insert double line break in the code.

For example, the following code:

	My class code
	It is used for some purpose.

will be transformed to:

	My class codeIt is used for some purpose.

So, to make proper formatting you need to write it the following way:

	My class code

It is used for some purpose.

Also you can use **@n** to force new line anywhere.

## Pages

This is ready documentation pages, so you can add page comment block somewhere in the code and documentation page will be generated.

There are some predefined documentation pages where you can add content. For example "Objects" documentations page consists of references to all "Objects".

This is an example on how to add content to "Objects" documentation page:
```php
	/** 
	 * @page objects 
	 * @section comments Comments
	 * @ref BxDolCmts
	 */
```
## Functions commenting format

make sure that functions/class methods comments have the following format:
```php
	/**
	 * Description is here.
	 * @param mixed $mixedData function data to process
	 * @param int $iDataType function data type
	 * @return abra kadabra
	 */
	function sample ($mixedData, $iDataType) {
	}
```
## Classes commenting format

Class comment must have description of general class functionality and the following sections, if they have place

* Example of usage
* List of all membership/acl actions this class is using
* List of all alerts this class can raise

Refer to **BxDolCmts** class for examples.

## One string comments

Make sure that one string comments for class properties, defines and global variables are formatted like this:

```php
	define('BX_DATA_INT', 3); ///< integer date type
	define('BX_DATA_FLOAT', 4); ///< float date type
	define('BX_DATA_HTML', 5); ///< HTML date type
```

## EDITOR
* Editor must use \n symbol as new line.
* Editor must insert spaces when tab is pressed.
* Tab must be set as 4 spaces.	