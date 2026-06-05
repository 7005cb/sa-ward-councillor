**Polls** app allows site members to create polls for asking questions from other site members/users. The app has basic but useful functions, let's take a look at poll creation form.

[[/images/polls/001.png|alt=Poll creation form]]

By default the form has the following list of fields:
* **Category** - Predefined list of categories which can be managed via Studio -> Forms builder -> Data Lists. You may get more info about forms builder [here](https://github.com/unaio/una/wiki/Forms-Builder).
* **Question** - The field has HTML editor which allows the original poster to shape the question in any way they need.
* **Answers** - Here you may add any number of answers (_Add More_ button) or delete unnecessary.
* **Pictures** - If you want you may upload image(s) which will be attached to the poll. Images may help you to describe the poll better and make it more attractive. One of the uploaded images can be marked as _Header Image_. Header image is displayed in the main poll block near the pool question and answers.
* **Visibility** - Is a standard privacy field which allows the creation of a public poll or poll with limited visibility. Also, the field is used to post a poll in the context (event, group, space, etc). **Note**, you need to follow the context to be able to post something in it.
* **Anonymous vote** - Enabled by default. When the mode is enabled, a list of voters won't be available to anybody. When it's disabled non-voted viewers will see the notification _This voting is public, your choice will be displayed for others_ about it and a list of already voted members will be available for each answer. **Note**, that this field cannot be changed after the poll was created because such changing may break the privacy of already voted users. 
[[/images/polls/002.png|alt=Voters list in popup]]
* **Hide results until voted** - Disabled by default. When it's disabled users may check already collected results before making their own votes. The results can be seen by clicking _View Results_ link in poll's snippet or in tabs menu of main poll block on view poll page.
[[/images/polls/003.png|alt=View Results link in poll snippet]]
If the field is enabled users won't be able to see the _View Results_ link in any places until voting. This field can be changed by poll author after creation.
* **Location** - This standard field can be used if the poll should be linked to some geographic location.

Also the app provides a complete set of browsing capabilities like Latest, Popular, etc. pages, browsing by categories, search and so on. As in the other content related apps your own polls and polls of all members (for moderator and administrator profiles) can be controlled via Manage page.