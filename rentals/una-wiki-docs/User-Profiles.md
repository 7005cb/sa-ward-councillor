User profiles in UNA are very different from UNA Classic. One member account can have many profiles of different types, for example one person profile (John Doe) and one organization profile (John & Co). Member can switch between them depending on desired actions on the site. All content posted on the site is posted under current profile of the member. 

By default there are two profile based modules (Persons and Organizations), but actually there is build in Account Profile, this Account Profile has no separate page and associated image, so we disabled some function for it, for example it is impossible to switch to this profile in profile switcher page. 

Module developers should aware that one account can have different profiles and always use current profile id (for example to assign as author of the content), there is build-in function to get it:

```php
bx_get_logged_profile_id ()
```

Usually there is no need to get account id (which can be associated with multiple profiles), unless some very custom behaviour, so please avoid using `getLoggedId()` function.


### Profiles DB structure

Here is profiles related tables diagram:

[[/images/profiles-db-diagram.png|alt=UNA profiles related tables diagram]]


`sys_profiles` table fields:

- `id`: it is main profile identification, `bx_get_logged_profile_id ()` function returns it for currently logged-in profile
- `account_id`: account ID the profile belongs to
- `type`: profile type, actually module name, or `system` for hidden system account profile
- `content_id`: ID of profile from module table with profile data
- `status`: profile status - active, pending or suspended


`sys_account` table fields:

- `id`: it is account identification, `getLoggedId ()` function returns it for currently logged-in account
- `profile_id`: current profile ID for the account, when you switch profile in user interface - this field is changed
- `name`, `email`, etc: account data fields needed for authentication and some basic info

`bx_persons_data` and `bx_organizations_data` are tables with actual profile info.


### Profile class

There is `BxDolProfile` class to help with some operations with profiles. There are different `getInstance*` static methods to get profile class instance, most common one is `getInstance($iProfileId)` to get it by profile ID. 

To get general info about profile there are the following functions:

```php
    public function id();
    public function getDisplayName();
    public function getUrl();
    public function getUnit();
    public function getAvatar();
    public function getThumb();
    public function getIcon();
    public function getEditUrl();
    public function isActive();
```

These functions make service call (if necessary) to the profile based module to get the data. Since profile modules can be different then representation of profile unit can be different as well. So you don't need to worry of what is profile type and module it is referring to, just use this class to get basic profile info. 

If `getInstance*` method returns `null`, then most probably profile doesn't exists, you can check result for `null` in the code and perform some custom behaviour or you can use singleton instance of `BxDolProfileUndefined` class to use as regular profile class instance - it will return some 'anonymous' or 'undefined' values for the requested data.

### Example

The following example get profile id from query string and prints link to the profile associated with this id.

```php
bx_import('BxDolProfile');
$oProfile = BxDolProfile::getInstance(bx_process_input(bx_get('id'), BX_DATA_INT));
if (!$oProfile) {
    bx_import('BxDolProfileUndefined');
    $oProfile = BxDolProfileUndefined::getInstance();
}

echo '<a href="' . $oProfile->getUrl() . '">' . $oProfile->getDisplayName() . '</a>';

```