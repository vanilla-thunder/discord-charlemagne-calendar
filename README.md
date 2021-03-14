# Charlemagne Event Calendar Discord Bot (NodeJS)
can be hosted on heroku, but also everywhere else.

![web interface demo](https://raw.githubusercontent.com/vanilla-thunder/discord-charlemagne-calendar/master/screenshop.jpg)

## Installation
+ `npm install`
+ copy `_config.json.dist`, rename to `_config.json` and change params
+ invite your bot to your server

# Calendar Web Interface (PHP)
in theory can be hosted on heroku, too, but i did not try it.

## Installation
+ `composer install`
+ if you host web interface separately from the bot, make sure you have same `_config.json`
+ point your web root to the `public /` directory
+ make sure 'DB' and 'logs' are writable
 
# Configuration
+ **debug**: enables debugging output for discord bot (true/false)
+ **discordToken**: your discord api token
+ **webPassword**: password for accessing web interface
+ **customToken**: custom token bot uses to submit data to the calendar web interface
+ **loadingEndpoint**: URL for loading stored calendar events (your web-interface URL + "/loadEvents")
+ **syncEndpoint**: URL for creating/updating calendar events (your web-interface URL + "/syncEvent") 
+ **deletingEndpoint**: URL for deleting calendar events (your web-interface URL + "/deleteEvent")
 
# Known "Problems"
1) finished events remain visible.  
Charlemagne's events have limited life time and are getting auto-deleted after a certain amount of time.  
But this bot can only track manually deleted events. I probably will add some automation for deleting old events in web interface.

2) Time Zones and DST are messed up.  
Charlemagne seems to use some non-standard time zones format "06:00 PM PT 25/07" (default), which could not be processed by php.
We use central european time and 24h format on our server, which could be processed, but not the PT (pacific time) time zone.  
Also charlemagne seems to ignore DST, since our events were stored with CET instead of CEST time zone.  
Currently the web itnerface does not support handling multiple time zones, e.g. if you live in a country with multiple time zones like USA, the interface displays the time for the server's time zone.

# How does it work?
The Discord Bot listens to Charlemagne's messages in chat and submits the embed event dara to the web interface.  
Event data will be processed there and saved as json file in `DB/events` directory.  
The web Interface picks all json Files and displays them in calendar view. 

# GDPR and Shit
Web interface uses php session for authentication, it places one mandatory cookie.
Also, the 3rd party calendar script "ToastUI-Calendar" uses Google Analytics, you can find more details [here](https://github.com/nhn/tui.calendar#collect-statistics-on-the-use-of-open-source).

# Used 3rd Party Software
+ discord.js for discord bot
+ axios for transmitting event data to web interface
+ flightphp for web interface backend
+ jamesmoss/flywheel for storing events as json files
+ angularjs, materializecss and ToastUI Calendar for web interface frontend
