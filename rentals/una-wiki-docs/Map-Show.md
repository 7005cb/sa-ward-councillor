Map Show App display information  about sign up users on the world map in real time based on  their IP addres.

![](https://raw.githubusercontent.com/wiki/unaio/una/images/mapshow/mapshow.png)

The map displays points of two sizes:
* small -  users joined before map load
* large - users joined during map is displayed.

## Install & Config
First of all you need to install Map Show App via Studio -> Apps Market. When the App was installed you need to find Map Show  in the Studio home page and open it. On the configuration page, you can see the following settings:

* Initial time frame users are shown in hours - parameter specifies the time interval in hours for which joined users will be displayed when the page is initially loaded.

* Interval for updating data for new users in seconds - frequency of updating the data on the map. It is not recommended to set too small an interval without important reason. Recommended value is 30 second.

* Default map's center lattitude coordinate, Default map's center longitude coordinate, Default map zoom - set the map on the screen (For example, you can display  North America only with a center in New York).
 
## Display on page

Go to Studio -> Pages.
Select the section, for example Homepage
Select the page, for example Homepage.
Click on the Add Blocks button.
In the list on the left, choose Map Show
In the appeared list on the right again choose "Map with last joined users"