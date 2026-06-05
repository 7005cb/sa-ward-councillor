UNA uses FontAwesome icons.  
As of **9.0.0-RC9** FontAwesome 5 is supported.  
In general the same as in FontAwesome rules of icons naming are applied in UNA.   
It's possible to use icons with `fa-` prefix and without it, by default `fa-` prefix isn't used.  


UNA uses the following classes to separate icons into several categories:
- `fas` - solid icons, this is default icons style and this class can be omitted
- `far` - regular icons
- `fab` - brands icons
- `fal` - light icons, only available when [FontAwesome Pro](https://una.io/page/view-product?id=128) module is installed
- `fad` - duotone icons, only available when [FontAwesome Pro](https://una.io/page/view-product?id=128) module is installed

### Icon colors

UNA has some predefined classes for colours so it's possible to change icon colour just by specifying colour class, for example:
```
globe-asia col-green-3
``` 
[List of available colour classes](https://una.io/samples/palette.php)

### Using icons in Studio

Icons can be used in different places, most notable in Studio > Navigation to set icons for different menus.   
To set icon just specify icon name (colour name can be added as well) in icon field for the menu item:
```
far globe-asia 
```
or 
```
far fa-globe-asia
```

### Using icons in HTML

If you want to use icons in raw HTML then use `sys-icon` class, like this:
```html
<i class="sys-icon far globe-asia col-blue3">
```

### Using light or duotone icons everywhere

FontAwesome Pro module has setting to change all icons on the site to **light** or **duotone** style.   
Just go to FontAwesome Pro module setting then change one setting, in some cases cache clearing is required (via Studio > Dashboard > Cache > Clear CSS cache)

### Changing colors for duotone icons

To change colors for duotone icons system wide add something like this to your mix custom styles field:
```css
:root {
  --fa-primary-color: green;
  --fa-primary-opacity: 1.0;
  --fa-secondary-color: red;
  --fa-secondary-opacity: 1.0;
}
```