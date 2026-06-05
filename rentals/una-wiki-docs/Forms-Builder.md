Forms Builder is the tool that lets you have some extra control over functionality without making any custom code changes. 

## Forms Builder Structure

In today's video I'm taking on a sample task of extending a "Favorite Cars" form in a demo "Cars" module. The form itself has to be present in the module, but I can alter it. This applies to all system forms as well. You may be able to grasp the idea just by watching the action sequence,  but it may look so much friendlier if you read a bit about how the builder works...

### Forms

Forms page shows "full forms" - complete sets of fields. It is important to understand that a form is not what you see on a particular page, but rather what is in the database. What you see, however, is a "Display".

### Displays

Displays are selected sets of fields from existing forms. For example the "Favorite Cars"  form may have displays like "Add a new car" and "Edit a car". You may control which fields are visible in either of those displays. Separation of Forms and Displays allows you to tailor user experience - create shorter submit forms, create forms for moderators, limit control over editable fields, etc.

### Fields

This is where it starts getting sexy (or am I spending too much time in UNA's company?). Fields are all those data bits that comprise a form - texts, dates, ranges, captchas, emails, you name it. The Fields page lets you add and edit fields in forms. Once added/edited, they are populated to all form displays, but have a "disabled" state - all you need is to "turn on" the fields that you want to be seen in any particular display. We've built a fancy fields selector and settings pup-ups for different types of fields. You even get to choose different WISIWIG editors in the "Text Area" field. 

### Data Lists

Another cool part of the Forms Builder is the support of re-usable Data Lists. In my example I created lists like "Car Makes" and "Transmission Types". Once created, lists may be used in any forms for various types of fields - multiple selects, radio sets, checkbox sets, etc. Data Lists can set a foundation of data classification on your site.

### Data Items

Data Items page lets you add and edit entries in Data Lists. Watch me changing Walksvagen to Volkswagen there. :)


## Demo

Again, this is only a quick sample form - took me about 20 minutes to build it. In the end of the video we show how the form is submitted and the entry is edited via "Edit" Display. Real-life application possibilities are virtually endless and experience will keep improving. 

https://www.youtube.com/watch/?v=95eLB5hMK88
