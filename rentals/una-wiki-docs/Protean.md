The UNA Template app is a special type of UNA app (module). It allows you to customize the appearance of default UNA features or add new unique ones. An UNA template can be created with or without a **styles customizer**. Template apps which have the styles customizer are Protean, Decorous and Lucid. An example of the template app without the styles customizer is Ocean. 

**Protean** template app is a default template app which comes in UNA package. The template has styles customizer which allows you to change CSS styles directly in Studio without the necessity to modify files manually. The customizer is available on **Styles** page of the template app (Studio -> Protean -> Styles). Usually customizer allows you to change styles like _Border_ (color, size, rounded corners), _Background_ (color, image, image repeat, image size, image attachment, etc), _Font_ (stack, size, weight, color, etc), _Shadow_, _Margins_, etc. Styles in the customizer are devided into groups:

[[/images/protean/01.png|alt=Grouped styles in customizer]]

1. **General** - basic styles which can be applied all over the site.
2. **Header** and **Footer** - styles for header and footer sections which is available on all pages. Header section includes styles for the logo.
3. **Body** - styles for page's body including page width.
5. **Cover** - styles for page's cover area including cover minimum height.
6. **Blocks** - styles for page's blocks, like background color/image, border color/width, title font, etc.
7. **Cards** - styles for cards. Cards are subblocks without titles. For example, you may see cards in Outline block (from Timeline app) on the home page.
8. **Popups** - styles for standard UNA popups.
9. **Main Menu** - styles for the main menu which is usually located at the very top of each page.
10. **Account Menu** - styles for account popup menu which is usually available for logged in users. The popup with menu can be seen after clicking the profile icon in the top right corner of each page.
11. **Page Menu** - styles for page submenu which is usually available under the page cover. The content of page submenu depends on the selected menu item in the main menu.
12. **Slide Menus** - styles for all slide down/up menus which contains blocks' tabs. The menu can be seen by clicking with 'chevron' icon in the top right corner of a block.
13. **Forms** - styles for form elements (inputs, text areas, etc).
14. **Large Buttons** - styles for large buttons all over the site.
15. **Small Buttons** - styles for small buttons all over the site.
16. **Fonts** - styles for fonts including H1, H2, H3 HTML tags.
17. **Custom Styles** - this section allows to add some custom CSS styles for specific layout elements which aren't covered with the previous sections.
18. **Tablet** and **Mobile Viewports** - these sections allow to change scaling for Tablet and Mobile viewports.

Also the styles customizer supports **Mixes**. Mix is a set of styles which can be created/edited/deleted and exported/imported in the customizer. By default, Protean template has **System** and **Neat** mixes. Default mixes cannot be customized, but if you want to make some minor changes in an existed mix, you may create a new mix which will copy the one you want to modify. To do so, you need to click with **Add New Mix** button in the top right corner of the Styles page and fill in the form in an appeared popup. In **Duplicate from** field you need to select the mix you want to modify. Doing so, you'll get a full copy of the mix which is available for modifying. 

[[/images/protean/02.png|alt=Add New Mix popup]]

To export styles you need to click with **Export Mix** button in the top right corner of the Styles page. To import styles you need to use **Import Mix** button located in the same place. To perform these actions styles are saved in JSON file, like _my-mix.json_

**Note**. If CSS cache is enabled on your site, you need to clear cache anytime you're making changes in styles customizer, otherwise you won't see them on the front end. You may check whether the cache enabled or not in Studio -> Settings App -> Cache accordion. You may clear cache in Studio -> Dashboard app -> Cache block.