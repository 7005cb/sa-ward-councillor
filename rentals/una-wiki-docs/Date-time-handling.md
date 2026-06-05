UNA is using different approach for storing and displaying date and time. The main advantages of this system:
- timezones support
- user don't need to bother with timezone settings
- exact and relative time
- real-time updates for relative time
- one universal date/time format
- HTML5 time support 
- allow search engines to understand the date/time

When time is displayed it is wrapped in `<time>` HTML5 tag, then it is formatted according to the current settings using JS. As the result current user timezone (which is set in the OS) is used - no need to set timezone in UNA. Since JS is used it updates relative time in real time, so `2 minutes ago` changes to `3 minutes ago` and so on. The use of `<time>` tag allows search engines to know the exact date and time.

To take advantages of new date/time system in UNA, developers need to do the following simple things:
- store date/time values as unix timestamp only
- for displaying date/time always use `bx_time_js($iUnixTimestamp, $sFormatIdentifier = BX_FORMAT_DATE, $bForceFormat = false)` function

Date/time related settings are in **UNA Studio > Polyglot**. [Date/time format for UNA](http://momentjs.com/docs/#/parsing/string-format/) is powered by [Moment.js](http://momentjs.com)

![UNA date/time format settings](images/datetime-settings.png)





