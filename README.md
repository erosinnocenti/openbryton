## OpenBryton
**OpenBryton** is a combination of a browser bookmarlet and a PHP server script for generating routes in Bryton Rider format.
Currently OpenBryton is tested only with Bryton Rider R530, which is my GPS unit.

I decided to build this software because the only way to create a route complete of turn-by-turn notifications is to use Bryton Mobile App (https://play.google.com/store/apps/details?id=com.brytonsport.barringer&hl=it)

Unfortunately, in BMA (Bryton Mobile App) it's possible to plan trips specifying only two points: start and destination.

With OpenBryton bookmarklet you can use the powerful OpenRouteService (http://openrouteservice.org) that lies on OpenStreetMaps database to create tracks.
OpenRouteService allows you to specify more than two waypoints and also to calculate turn indications.

When you finish to plan your trip on the map, just click the bookmarklet to download a zip file containing three files:
- file.tinfo    (contains turn indications info)
- file.smy      (header file)
- file.track    (contains coordinates of calculated points)

Afterwards all you have to do is to copy these 3 files into you Bryton "tracks" folder.

Just a note:
- In OpenRouteService make sure to select pedestrian routing

---

## Install instructions

Copy and paste this code into a new bookmark, or simply select the code and drag-drop it into your favorites bar.
```javascript
javascript: (function () { var jsCode = document.createElement('script'); jsCode.setAttribute('src', 'http://www.newtechweb.it/apps/openbryton/do.js'); document.body.appendChild(jsCode); }());
```
---

## Donate

If you liked this tool, please buy me a beer :)

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4UMTD8RPT6HDE)
