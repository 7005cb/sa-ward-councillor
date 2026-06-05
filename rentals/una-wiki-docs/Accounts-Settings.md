## Settings > Account

In the Settings > Account section, the following settings are available:
* Online status timeframe (minutes) - the lifetime of a session in the absence of activity.

*  Automatic account activation after creation - if it is disabled, profiles associated with this account are created inactive("Pending" status), and some of the functions from this account will be unavailable, for example, creating new messages.

*  To activate such an account, use [App Accounts Manager](https://github.com/unaio/una/wiki/Accounts).
For more accurately tweaking action list available to "Pending" accounts, you need to go to the [Permissions-Builder](https://github.com/unaio/una/wiki/Permissions-Builder) and edit the "Pending" status.

*  Enable account activation letter - if this option is enabled, after account activation, the notification letter will be sent to the user's email.

*  Account is confirmed when - the following options for account verification exist (Until the account is not confirmed, the user will see a message about account confirmation on all pages.):

* * No confirmation - no action required to confirm the account is required, the account at the registration immediately has the status of confirmed.

* * email is confirmed -  after the registration on the user's email will be sent an email with the link, clicking on which, the account will be confirmed.

* * phone is confirmed - the account is confirmed via SMS. On the phone verification page, user will be asked to enter the phone number (it must be unique - not be owned by other users). This phone will be sent SMS with the code, which user will enter in the next step.
Requires an account in twilio.com and tweaking settings in section Twilio (see below)
If it's enable password recovery by phone is avaliable.

* * email and phone are confirmed - the mode at which you need to confirm both the phone and the email, having performed the actions described in the two previous paragraphs.

* Enable 2FA -  enables the Two-factor authentication mode. In this mode, after entering login and password, the user will be asked to enter the phone number (if it was not entered before) and the SMS with validation code will be sent to this number. This will happen every time you log in to the site.

* Automatic profile creation from account name - if is enabled, the first profile linked to the account, will be created automatically. If it is disabled, immediately after registration, the user will be asked to create his first profile.

* Default profile type - select the type of profile that is created by default. For example Persons, Organizations or other.

* Limit number of profiles (0 - no limit) - the number of profiles that can be created for one account, the default is 0 - no limits.

## Settings > Twilio

SMS messages are sent when
1. During account confirmation (phone confirmation must be enabled)
2. During login (Two-factor authentication  must be enabled)
3. Password recovery (phone confirmation must be enabled)

To send messages, you need to register your account in [Twilio](https://www.twilio.com). After registration, you will need to copy ACCOUNT SID and AUTH TOKEN from the [Twilio Dashbord](https://www.twilio.com/console) to the appropriate settings fields.
Also, you need to define the phone in the [Phone Numbers section](https://www.twilio.com/console/phone-numbers/incoming) and put it in the field Default 'From' number fror SMS.

