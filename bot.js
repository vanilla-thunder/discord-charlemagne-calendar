const fs = require('fs'),
	Discord = require('discord.js'),
	client = new Discord.Client(),
	axios = require('axios');

var cfg = {};

const init = async () => {

	// generating or loading config
	if (process.env.discordToken && process.env.syncEndpoint && process.env.deletingEndpoint && process.env.customToken && process.env.debug) {
		cfg = {
			"debug": (process.env.debug == "true" ? true : false),
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

	const syncEvent = (data) => {
		if (cfg.debug) console.log("sending event data");
		if (cfg.debug) console.log(data);
		axios.post(cfg.syncEndpoint + '?token=' + cfg.customToken, data)
			.then((res) => { if (cfg.debug) console.log("event submitted"); })
			.catch((error) => {
				console.error(error);
				if (cfg.debug) fs.writeFileSync("./logs/syncError.json", error);
			});
	};

	const deleteEvent = (data) => {
		if (cfg.debug) console.log("deleting event " + data);
		axios.post(cfg.deletingEndpoint + '?token=' + cfg.customToken, { event: data })
			.then((res) => { if (cfg.debug) console.log("event deleted"); })
			.catch((error) => {
				console.error(error);
				if (cfg.debug) fs.writeFileSync("./logs/deleteError.json", error);
			});
	};

	client.once('ready', () => {
		console.log('bot running');
	});

	client.login(cfg.discordToken);


	const charlemagneId = "296023718839451649";
	const cancelMessage = "Successfully cancelled LFG Post: ";

	// monitor only chat messages from charlemagne
	const isCharlemagne = (id) => {
		if (id !== "296023718839451649") return false
		return true
	};

	const isEvent = (message) => {
		if (!isCharlemagne(message.author)) return false
		// event messages have no content but an embed message with 5 or 6 fields
		if (message.content !== "" || message.embeds.length < 1 || message.embeds[0].fields.length < 5) return false
		return true
	};

	// when cancelling events charlemagne posts message "Successfully cancelled LFG Post: 1234 - activity name"
	const wasCanceled = (message) => {
		if (!isCharlemagne(message.author)) return false
		if (!message.content.indexOf(cancelMessage) === 0) return false
		return true
	};

	// charlemagne always updates the event messages, therefore we only need to watch messageUpdate events for new and updated events
	client.on('messageUpdate', (oldMessage, newMessage) => {

		if (isEvent(newMessage)) return syncEvent(newMessage.embeds[0].fields);
	});

	// on deleting events charlemagne posts message "Successfully cancelled LFG Post: 1234 - activity name"
	client.on('message', (newMessage) => {

		if (wasCanceled(newMessage)) return deleteEvent(newMessage.content.substr(cancelMessage.length));
	});

	// charlemagne events are cancelled when their associated chat message is deleted
	client.on('messageDelete', (message) => {
	
		if (isEvent(message)) return deleteEvent(message.content.substr(cancelMessage.length))
	});

	process.on('SIGTERM', () => client.destroy())
	process.on('SIGINT', () => client.destroy())
}

init()
	.catch(e => {
		console.error(e)
		console.error('Failed to initialize.')
		process.exit(1)
	})
