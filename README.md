# Charlemagne Event Calendar Discord Bot (NodeJS)
can be hosted on heroku, but also everywhere else.

![web interface demo](https://raw.githubusercontent.com/vanilla-thunder/discord-charlemagne-calendar/master/screenshop.jpg)

## Installation
+ `npm install`
+ copy `_config.json.dist`, renate to `_config.json` and change params
+ invite your bot to your server

# Calendar Web Interface (PHP)
in theory can be hosted on heroku, too, but i did not try it.

## Installation
+ `composer install`
+ if you host web interface separately from the bot, make sure you have same `_config.json`
+ point your web root to the `public /` directory
+ make sure 'DB' and 'logs' are writable
 
# Configuration
+ debug: enables debugging output for discord bot (true/false)
+ discordToken": your discord api token
+ webPassword": password for accessing web interface
+ customToken": custom token bot uses to submit data to the calendar web interface
+ loadingEndpoint: URL for loading stored calendar events (your web-interface URL + "/loadEvents")
+ syncEndpoint": URL for creating/updating calendar events (your web-interface URL + "/syncEvent") 
+ deletingEndpoint: URL for deleting calendar events (your web-interface URL + "/deleteEvent")
 
# Known "Problems"
Only manually deleted events will be deleted from web interface.
Charlemagne's events have limited life time and are getting auto-deleted after a certain amount of time.
Those "finished" events stay in web interface.
I probably will add some automation for deleting old events in web interface.

# GDPR and Shit
web interface uses php session for authentication, it places one mandatory cookie.
There are no trackings or other cookies.
