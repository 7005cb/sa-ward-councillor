**Timeline** app is very important because it collects all activity on the site in one single place. The app automatically registers events from different profile and content related apps and also allows to do direct posts via _Post to Feed_ form. 

[[/images/timeline/001.png|alt=Post to Feed form]]

As you may see direct post may include text with emojis, sheared links, photos and videos. The post can be public or has limited visibility which can be done using standard _Visibility_ field, also it can be posted anonymously using _Post as anonymous_ switcher. 'Publish at' field allows to create delayed posts, but by default this feature is available to moderator and administrator profiles only.

Timeline app allows to represent all collected data in two ways:
* **Timeline** - this representation displays all events (items) on a timeline, where the newest event comes first. So, in this representation a viewer may easily see a time sequence of events which were happened on the site or with the profile which timeline is currently viewed.
[[/images/timeline/002.png|alt=Timeline representation]]

* **Outline** - this representation displays all events (items) as combined set of blocks. It doesn't allow to see the time sequence between displayed events, but it gives general understanding about the last activity on the site.
[[/images/timeline/003.png|alt=Outline representation]]

By default timeline blocks can be seen on different pages: _Home_, _Dashboard_ and _Profiles_. They look similar but display different events:
1. Timeline from home page is called **Public Feed**. It displays only public events from the whole site. Some of these events can be seen on profile timelines. 
2. Timeline from Profile page is called **Profile Feed**. It displayes events related to the viewed profile only. Events in this timeline are filtered using default Privacy engine. It means that a Viewer №1 may see one set of events while a Viewer №2 will see another one.
3. Timeline from Dashboard page is called **Feed**. It's a special timeline which combines events related to currently logged in member and all profiles he follow. It means that this timeline is very useful for a user to quickly receive information about the other site members he iterested in.
4. **Hot Feed** is a timeline which isn't displayed anywhere by default but is available in Studio -> Pages builder. This timeline allows to show the most recent, commented and voted events in a separate timeline block. It can be shown on any page you want. **Note**. To use the feature you need to activate hot list aggregation in Studio -> Timeline -> Settings and put **Hot Feed (Timeline)** or **Hot Feed (Outline)** on necessary page.

Almost similar situation we can see with _Post to Feed_ blocks. 
1. **Post to Public Feed** creates public posts on **Public Feed**. This post form doesn't allow to use _Visibility_ field.
2. **Post to Feed** from **Profile** page creates a post on the **Profile Feed** which the form is displayed on. Profile viewer can create only public posts while the profile owner may limit the visibility for his post using _Visibility_ field.
3. **Post to Feed** from **Dashboard** page creates a post on the profile timeline of currently logged in member. But also it allows to create posts on profile, group, space, etc. timelines of members, groups, spaces, etc. followed by currently logged in member. This possibility is also realized via _Visibility_ field.

[[/images/timeline/004.png|alt=Visibility selector from Post to Feed form on Dashboard page]]

Now let's take a look at actions available for logged in users in each timeline event. First of all it's standard actions like comment, vote, share, report. If event relates to some post, photo, video, event, group, etc. from appropriate app then the information about comments, votes, etc will be taken from the app. For direct posts in timeline the information is collecting inside Timeline app itself.

[[/images/timeline/005.png|alt=Event actions]]

Some actions from _More_ (...) menu are available for advanced (moderators and administrators by default) members only:
1. **Promote** allows to promote some event. Promoted event has special title which separates it from the others visually.
2. **Pin for all** allows to pin the event at the very top of **Public Feed**. It's useful for site owner to show some important event(s) at the very beginning for some time. Almost similar feature **Pin here** is available in **Profile Feed** for profile owner. It allows profile owner to pin some event(s) on his own timeline.
[[/images/timeline/006.png|alt=Pinned and promoted event on Public Feed]]
3. **Hide from feeds** allows to remove the event from timeline. **Note**, if the event relates to some content from content based apps then this action doesn't affect the content in associated app.

Timeline administration in Studio has different settings which alloews to enable/disable built in features. Some of them are disabled by default.

[[/images/timeline/007.png|alt=Timeline settings in Studio]]

* _Show events_ checkboxes list allows to enable/disable events. Disabled events won't be shown in any timelines.
* _Videos autoplay_ is _Disabled_ by default, the other choices are _Enabled without sound_ and _Enabled with sound_. This feature is availble in Timeline representation only. When enabled, the video is automatically started to play when it's displayed during page scrolling.
* _Number of preloaded comments_ is disabled (0) by default, maximum value is 7 items. When some number greater then 0 is used timeline will preload selected number of latest comments for each event.
* _Enable Hot list aggregation_ and _Interval of time (in hours) to get Hot events_ are needed to aggrigate hot events for **Hot Feed** described earlier. 

### Dynamic Browsing
In UNA 13 **Timeline** app has got a new feature, so called **Dynamic Browsing**. It allows to browse different Timeline feeds on one page.
To activate it you need to go to Studio -> Pages, select some existent page or create a new one. Then you need to add **Dynamic Browsing: Menu** and **Dynamic Browsing: Views** Timeline blocks. So, the page will look like the following.
![изображение](https://user-images.githubusercontent.com/3894360/160370945-32bfa04c-0c58-430a-9d93-92cfa8cbdefb.png)
1. **Dynamic Browsing: Menu** block in the left has a list of all default feeds, like Feed, Public and Hot. Also you may find feeds from all contexts (Persons, Organizations, Events, Groups, etc) followed by you. So, it allows to check on the same page what's new in contexts you are interesting.
2. **Dynamic Browsing: Views** block dynamically loads the feed selected in **Dynamic Browsing: Menu** block. Also it allows to use Filters. Filters is also a new feature in **Timeline** app. For example, you are viewing some Feed and want to see only Videos (posts related to **Videos** app) from this feed. Filters will allow you to do this.
![изображение](https://user-images.githubusercontent.com/3894360/160373263-03034fd3-2cf5-4c7b-80f9-9e049a0465cb.png)
