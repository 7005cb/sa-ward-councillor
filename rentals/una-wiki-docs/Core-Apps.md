Core Apps cover most common use-cases in a community site. They serve as prototypes for various Premium and Tailored Apps. 

## People
_Members, Customers, Profiles._

Profile App. Individual profiles, working as an on-site identification, member information page and aggregator of the user content. Only one session can be established for a Person profile at any given time (one logged-in user). People can establish social connections with other profiles, groups or content (become friends, subscribe, join, sign up, buy, etc). People can be given membership levels and assigned permissions for various actions.

## Orgs
_Companies, Brands, Celebrities._

Profile App. Individual profiles, working as an on-site identification, information page and aggregator of the user content. Multiple sessions can be established for an Org profile at any given time (many logged-in users acting as one Organisation). Orgs can establish social connections with other profiles, groups or content (become friends, subscribe, join, sign up, buy, etc). Orgs can be given membership levels and assigned permissions for various actions. Publicly all logged-in users are presented as a single entity and they share same login credentials.

## Groups
_Teams, Clubs, Departments._

Groups work as content aggregators and communication channels for Groups members. Profiles like People or Orgs can join Groups. Groups can be public, private or secret. In public Groups, content is visible to everyone. In private Groups content is visible to Group members only. Secret groups are private and can not be seen in browsing pages. Invitation from Groups admin is required to see and join a secret group. Groups don't work as site profiles (IDs), but they can be used by different apps to address or reference groups of profiles. Groups may have open registration or may require admin approval of registration requests.

## Events
_Milestones, Projects._

Events are information and communication aggregators linked to a specific period of time, scheduled, recurring, one-time or periodic. Events can be public, private or secret. In public Events, content is visible to everyone. In private Events content is visible to Event participants only. Secret Events are private and can not be seen in browsing pages. Invitation from Event organiser is required to see and join a secret Event. Events don't work as site profiles (IDs), but they can be used by different apps to address or reference groups of profiles. Events may have open registration or may require admin approval of registration requests. Events Calendar shows events timeline for public and private Events.

## Posts
_Blogs, News, Articles._

Single page rich-text publications with a purpose of publishing the main body of text, mixed with images. Comments can be posted, but are not essential. Browsing pages sort Posts by date of publishing, latest first. Categories are pre-defined by Operator. Hashtags are used for ad-hoc labelling. 

## Discussions
_Forums, Answers._

Asynchronous public communication channels. Original postings define topics of discussions. Replies are essential. Browsing pages sort Discussions by date of the last reply, showing recently updated first. Categories are pre-defined by Operator. Hashtags are used for ad-hoc labelling. 

## Albums
_Pictures, Photos, Video, Music, Files._

Postings with attached media files. Each attached file has its own page. Pages within an Album can have their own comments and can be browsed in succession via on-page paginate or Gallery plugin. Album page may have its own comments and other blocks. Browsing pages show albums by date of publishing or by date of latest file upload, most recent first. Categories are pre-defined by Operator. Hashtags are used for ad-hoc labelling.

## Conversations
_Messages, Mail._

Private communication between two or more participants. Conversations can only be visible to participants defined by conversation starter, or new participants invited by existing participants. Conversations are sorted by date of last reply, latest first. 

## Timeline
_Feed, Wall._

Timeline is a content posting, sharing and aggregating app, associated with a Person or Org profile. Timeline can be displayed for one specific profile, as a feed for updates from connected profiles (friends, subscriptions, joined groups, etc) or as a site-wide feed of all public updates from different profiles. 


## Notifications

This application allows the users to get instant updates about their profiles, content and activity of the connected profiles. There are 3 types of notifications: on site (information via UNA navigation menus), Email and Push (requires the setup tools like One Signal). The actions of notifications can be regulated on the admin and user sides.

## Invitations

The admin(s) may close the registration on site with this app and leave it for the special guests only. It's possible to set the different options (number of invitations per one user, timeframe of the invitation key, emails to send invitation requests, and others) and manage the sent requests and taken invites to join).

## Contact

Contact provides the way to write a direct email to the person who is responsible for the site. There is available the possibility to use the specific email if the site email isn't good for a similar purpose.

## Developer

The place where the admin may operate with the Settings, Polyglot, Forms, Navigation and Pages in "advanced mode".

In the _Settings_, it's possible to change the time of Last Cron Execution, Installation date, switch the upgrade channel ("stable" for releases and "beta" if you want to test new features), set the default player and default media quality, change the available HTML editor or re-assign the order of buttons in the default one, enable / disable image GD processing and some other options.

_Polyglot_ area provides the recompilation steps of the language file for all content or for a single module only. Also, there is available the "Restore all" button which generates a lang file from the installation XML files.

In the _Forms_ section admin may edit important form settings like Action, Submit Name, Table (for the saving records), Key Field (usually - autoincremented Id), URI and URI Source fields. With Forms Attrs field it's possible to set enctype and specific CSS class and in Params - control the form's checker. Class Name and Class File fields are responsible for the form's processing / displaying code (see more details in the [Dev Forms manual](https://github.com/unaio/una/wiki/Dev-Forms)).

_Navigation_ block gives an opportunity to set the specific class (and file with this class) for the menu and correct the options like Submenu Options, Addon and Custom Menu visibility for the menu items (more details [about it](https://github.com/unaio/una/wiki/Menus)).

_Pages_ area adds the class options for the pages and the different parameters in the Code field for every block there like module, method and given method's arguments (explained more in [Dev Pages](https://github.com/unaio/una/wiki/Dev-Pages) guide).

The _Forms_, _Navigation_ and _Pages_ sections contain the export buttons which provide the ready MySQL code to insert / delete the object to the UNA database.

## Antispam

This tool allows to limit the unbidden visitors and their content on the site. There is possible to work with the visitors' IPs (manual in _IP tables_ section and automatic in _DNSBL_ area), regulate the spam URLs in the content, replace the forbidden words via _Profanity Filter_. Also, there were integrated _Akismet_ and _StopForumSpam_ automatic spam content blocking services (the API keys are required). The various measures for the intruders: block, limited permissions, reports to the admin, save records in the log.

## SMTP Mailer

The powerful module to connect an external email infrastructure service such as Mandrill or Sendgrid to ensure that emails from your site don't get blocked by Spam Filters. It has options like SMTP username, password, port, secure connections types and the possibility to change "from" name and emails.