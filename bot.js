const fs = require('fs'),
	Discord = require('discord.js'),
	client = new Discord.Client(),
	axios = require('axios');

var cfg = {};

// generating or loading config
if(process.env.discordToken && process.env.syncEndpoint && process.env.deletingEndpoint && process.env.customToken && process.env.debug )
{
	cfg = {
		"debug" : ( process.env.debug == "true" ? true : false),
		"discordToken": process.env.discordToken,
		"customToken": process.env.customToken,
		"syncEndpoint": process.env.syncEndpoint, 
		"deletingEndpoint": process.env.deletingEndpoint
	};
	console.log("loading config from env vars");
}
else if (fs.existsSync('./_config.json')) 
{
	cfg = require('./_config.json');	
	console.log("loading config from file");
}
else 
{
	console.log("");
	console.error("\x1b[31m", "config file does not exist, please rename _config.json.dist to _config.json and change the configuration");
	console.log("\x1b[0m");
	process.exit(1);
}


var syncEvent = (data) => {
	if(cfg.debug) console.log("sending event data");
	if(cfg.debug) console.log(data);
	axios.post(cfg.syncEndpoint + '?token=' + cfg.customToken, data)
		.then((res) => { if(cfg.debug) console.log("event submitted"); })
		.catch((error) => { 
			console.error(error) ;
			if(cfg.debug) fs.writeFileSync("./logs/syncError.json", error);
		});
};

var deleteEvent = (data) => {
	if(cfg.debug) console.log("deleting event " + data);
	axios.post(cfg.deletingEndpoint + '?token=' + cfg.customToken, {event: data})
		.then((res) => { if(cfg.debug) console.log("event deleted"); })
		.catch((error) => { 
			console.error(error);
			if(cfg.debug)fs.writeFileSync("./logs/deleteError.json", error);
		});
};

client.once('ready', () => {
	console.log('bot running');
});

client.login(cfg.discordToken);

// charlemagne always updates the event messages, therefore we only need to watch messageUpdate events for new and updated events
client.on('messageUpdate', (oldMessage, newMessage) => {
	
	// we only want to monitor chat messages from charlemagne
	$charlemagne = (newMessage.author.id === "296023718839451649" && newMessage.author.bot);
	
	// event messages have no content but an embed message with 5 or 6 fields
	$eventMessage = (newMessage.content === "" && newMessage.embeds.length > 0 && newMessage.embeds[0].fields.length >= 5);
	
	if ( $charlemagne && $eventMessage) syncEvent(newMessage.embeds[0].fields);
});

// on deleting events charlemagne posts message "Successfully cancelled LFG Post: 1234 - activity name"
client.on('message', (newMessage) => {
	
	// we only want to monitor chat messages from charlemagne
	$charlemagne = (newMessage.author.id === "296023718839451649" && newMessage.author.bot);
	
	// on deleting events charlemagne posts message "Successfully cancelled LFG Post: 1234 - activity name"
	$deleteMessage = (newMessage.content.indexOf('Successfully cancelled LFG Post') === 0);
	
	if ( $charlemagne && $deleteMessage ) deleteEvent(newMessage.content.substr(33));
});