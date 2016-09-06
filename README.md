## OpenBryton
**OpenBryton** is a combination of a browser bookmarklet and a PHP server script to generate routes in Bryton Rider format.
Currently OpenBryton is being tested only against Bryton Rider R530, which is my GPS unit.

I decided to build this software because the only way to create a route complete of turn-by-turn notifications is to use Bryton Mobile App (https://play.google.com/store/apps/details?id=com.brytonsport.barringer&hl=it)
Unfortunately, with Bryton official App you can only plan trips specifying two points: start and destination. No waypoints allowed.

With OpenBryton bookmarklet you can use the powerful OpenRouteService (http://openrouteservice.org) that lies on OpenStreetMaps database to design tracks. 
OpenRouteService allows you to specify more than two waypoints and also to calculate precise turn indications even for mountain dirt roads.

When you finish to plan your trip on the map, just click the bookmarklet to download a zip file containing three files:
- file.smy      (header file)
- file.tinfo    (it contains turn indications info)
- file.track    (it contains coordinates of calculated points)

Afterwards all you have to do is to copy these 3 files into you Bryton "tracks" folder.

---

## Install procedure

Copy and paste this code into a new bookmark, or simply select the code and drag-drop it into your favorites bar.
```javascript
javascript: (function () { var jsCode = document.createElement('script'); jsCode.setAttribute('src', 'http://www.newtechweb.it/apps/openbryton/do.js'); document.body.appendChild(jsCode); }());
```
---

## Donate

If you liked this tool, please buy me a beer :)

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=4UMTD8RPT6HDE)

---

| Plan with openrouteservice.org | Get it on your device |
| --- | --- |
| ![Plan with openrouteservice.org](http://www.newtechweb.it/apps/openbryton/screen-1.jpg "Plan with openrouteservice.org") | ![Get it on your device](http://www.newtechweb.it/apps/openbryton/screen-2.jpg "Get it on your device") |

