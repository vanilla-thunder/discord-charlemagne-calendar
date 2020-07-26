const fs = require('fs'),
	Discord = require('discord.js'),
	client = new Discord.Client(),
	axios = require('axios');

var cfg = {};

const loadConfig = () => {
	// generating or loading config
	if (process.env.discordToken && process.env.syncEndpoint && process.env.deletingEndpoint && process.env.customToken && process.env.debug) {
		cfg = {
			"debug": (process.env.debug == "true"),
			"discordToken": process.env.discordToken,
			"customToken": process.env.customToken,
			"syncEndpoint": process.env.syncEndpoint,
			"deletingEndpoint": process.env.deletingEndpoint
		};
		console.log("loading config from env vars");
	}
	else if (fs.existsSync('./_config.json')) {
		cfg = require('./_config.json');
		console.log("loading config from file");
	}
	else {
		console.log("");
		console.error("\x1b[31m", "config file does not exist, please rename _config.json.dist to _config.json and change the configuration");
		console.log("\x1b[0m");
		process.exit(1);
	}
}

const myId = "630842224849977387",
	reloadConfigMessage = "!rc reload",
	charlemagneId = "296023718839451649",
	cancelMessage = "Successfully cancelled LFG Post: ";

const isMe = (id) => { return (id == myId) }; // use == because i dont know if id is an int or a string
const isCharlemagne = (id) => { return (id == charlemagneId) }; // use == because i dont know if id is an int or a string

const isConfigReloadMessage = (message) => {
	// message to make bot reload config, e.g. for changing endpoints, token or enable/disable debug output
	return (isMe(message.author.id) && message.content === reloadConfigMessage)
};

const isEventMessage = (message) => {
	// event messages have no content but an embed message with 5 or 6 fields
	//console.log("is charlemagne?", isCharlemagne(message.author.id));
	//console.log("no content?", (message.content === ""));
	//console.log("message embeds length", message.embeds.length);
	//if(message.embeds.length > 0) console.log("message embeds[0] fields length", message.embeds[0].fields.length);
	return (isCharlemagne(message.author.id) && message.content === "" && message.embeds.length > 0 && message.embeds[0].fields.length > 4);
};

// when cancelling events charlemagne posts message "Successfully cancelled LFG Post: 1234 - activity name"
const wasCancelled = (message) => { return (isCharlemagne(message.author.id) && message.content.indexOf(cancelMessage) === 0) };

// functrions for transferring event data
const syncEvent = (data) => {
	if (cfg.debug) console.log("sending event data");
	if (cfg.debug) console.log(data);
	axios.post(cfg.syncEndpoint + '?token=' + cfg.customToken, data)
		 .then((res) => { if (cfg.debug) console.log("event submitted"); })
		 .catch((error) => {
			 console.error(error);
			 //if (cfg.debug) fs.writeFileSync("./logs/syncError.json", error);
		 });
};

const deleteEvent = (data) => {
	if (cfg.debug) console.log("deleting event " + data);
	axios.post(cfg.deletingEndpoint + '?token=' + cfg.customToken, { event: data })
		 .then((res) => { if (cfg.debug) console.log("event deleted"); })
		 .catch((error) => {
			 console.error(error);
			 //if (cfg.debug) fs.writeFileSync("./logs/deleteError.json", error);
		 });
};

// initialize discord bot
const init = async () => {
	loadConfig();

	client.once('ready', () => {
		console.log('bot running');
	});

	client.login(cfg.discordToken);

	client.on('messageUpdate', (oldMessage, newMessage) => {
		// charlemagne always updates the event messages, therefore we only need to watch messageUpdate events for new and updated events
		if (isEventMessage(newMessage)) return syncEvent(newMessage.embeds[0].fields);
	});

	// on deleting events charlemagne posts message "Successfully cancelled LFG Post: 1234 - activity name"
	client.on('message', (newMessage) => {
		// charlemagne always updates the event messages, therefore we only need to watch messageUpdate events for new and updated events
		if (isConfigReloadMessage(newMessage)) return loadConfig();
		else if (isEventMessage(newMessage)) return syncEvent(newMessage.embeds[0].fields);
		else if (wasCancelled(newMessage)) return deleteEvent(newMessage.content.substr(cancelMessage.length));
	});

	// charlemagne events are cancelled when their associated chat message is deleted
	client.on('messageDelete', (message) => {
		if (isEventMessage(message)) return deleteEvent(message.content.substr(cancelMessage.length))
	});

	process.on('SIGTERM', () => client.destroy())
	process.on('SIGINT', () => client.destroy())
}

init()
	.catch(e => {
		console.error('Failed to initialize.')
		console.error(e)
		process.exit(1)
	})
