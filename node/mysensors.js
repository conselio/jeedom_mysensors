/*
#
#  ______                             _  _                                
# / _____)                           | |(_)                               
#| /        ___   ____    ___   ____ | | _   ___       ____   ___   ____  
#| |       / _ \ |  _ \  /___) / _  )| || | / _ \     / ___) / _ \ |    \ 
#| \_____ | |_| || | | ||___ |( (/ / | || || |_| | _ ( (___ | |_| || | | |
# \______) \___/ |_| |_|(___/  \____)|_||_| \___/ (_) \____) \___/ |_|_|_|
#
*/

var net = require('net');
var fs = require('fs');
var request = require('request');


const gwType = 'Serial';

var urlJeedom = '';
var gwPort = '';

//const gwType = 'Ethernet';
const gwAddress = '';
//const gwPort

// print process.argv
process.argv.forEach(function(val, index, array) {
  
	switch ( index ) {
		case 2 : urlJeedom = val; break;
		case 3 : gwPort = val; break;
		case 4 : gwType = val; break;
		case 5 : gwAddress = val; break;
	}
  
});



const gwBaud = 115200;

const fwHexFiles 					= [  ];
const fwDefaultType 				= 0xFFFF; // index of hex file from array above (0xFFFF

const FIRMWARE_BLOCK_SIZE			= 16;
const BROADCAST_ADDRESS				= 255;
const NODE_SENSOR_ID				= 255;

const C_PRESENTATION				= 0;
const C_SET							= 1;
const C_REQ							= 2;
const C_INTERNAL					= 3;
const C_STREAM						= 4;


const I_BATTERY_LEVEL				= 0;
const I_TIME						= 1;
const I_VERSION						= 2;
const I_ID_REQUEST					= 3;
const I_ID_RESPONSE					= 4;
const I_INCLUSION_MODE				= 5;
const I_CONFIG						= 6;
const I_PING						= 7;
const I_PING_ACK					= 8;
const I_LOG_MESSAGE					= 9;
const I_CHILDREN					= 10;
const I_SKETCH_NAME					= 11;
const I_SKETCH_VERSION				= 12;
const I_REBOOT						= 13;



const ST_FIRMWARE_CONFIG_REQUEST	= 0;
const ST_FIRMWARE_CONFIG_RESPONSE	= 1;
const ST_FIRMWARE_REQUEST			= 2;
const ST_FIRMWARE_RESPONSE			= 3;
const ST_SOUND						= 4;
const ST_IMAGE						= 5;

const P_STRING						= 0;
const P_BYTE						= 1;
const P_INT16						= 2;
const P_UINT16						= 3;
const P_LONG32						= 4;
const P_ULONG32						= 5;
const P_CUSTOM						= 6;

var fs = require('fs');
var appendedString="";

function LogDate(Type, Message) {
 
   var ceJour = new Date();
       var ceJourJeedom = ceJour.getDate() + "/" + ceJour.getMonth() + "/" + ceJour.getFullYear() + " " + ceJour.getHours() + "" + ceJour.getMinutes() + "" + ceJour.getSeconds();
       console.log(ceJourJeedom + "|" + Type + "|" + Message);
}

function crcUpdate(old, value) {
	var c = old ^ value;
	for (var i = 0; i < 8; ++i) {
		if ((c & 1) > 0)
			c = ((c >> 1) ^ 0xA001);
		else
			c = (c >> 1);
	}
	return c;
}

function pullWord(arr, pos) {
	return arr[pos] + 256 * arr[pos + 1];
}
 
function pushWord(arr, val) {
	arr.push(val & 0x00FF);
	arr.push((val  >> 8) & 0x00FF);
}

function pushDWord(arr, val) {
	arr.push(val & 0x000000FF);
	arr.push((val  >> 8) & 0x000000FF);
	arr.push((val  >> 16) & 0x000000FF);
	arr.push((val  >> 24) & 0x000000FF);
}

function loadFirmware(fwtype, fwversion, sketch, db) {
	var filename = path.basename(sketch);
        console.log("compiling firmware: " + filename);
        var req = {
                files: [{
                        filename: filename,
                        content: fs.readFileSync(sketch).toString()
                }],
                format: "hex",
                version: "105",
                build: {
                        mcu: "atmega328p",
                        f_cpu: "16000000L",
                        core: "arduino",
                        variant: "standard"
                }
        };
        requestify.post('https://codebender.cc/utilities/compile/', req).then(function(res) {
                var body = JSON.parse(res.getBody());
                if (body.success) {
			console.log("loading firmware: " + filename);
			fwdata = [];
			var start = 0;
			var end = 0;
			var pos = 0;
			var hex = body.output.split("\n");
			for(l in hex) {
				line = hex[l].trim();
				if (line.length > 0) {
					while (line.substring(0, 1) != ":")
						line = line.substring(1);
					var reclen = parseInt(line.substring(1, 3), 16);
					var offset = parseInt(line.substring(3, 7), 16);
					var rectype = parseInt(line.substring(7, 9), 16);
					var data = line.substring(9, 9 + 2 * reclen);
					var chksum = parseInt(line.substring(9 + (2 * reclen), 9 + (2 * reclen) + 2), 16);
					if (rectype == 0) {
						if ((start == 0) && (end == 0)) {
							if (offset % 128 > 0)
								throw new Error("error loading hex file - offset can't be devided by 128");
							start = offset;
							end = offset;
						}
						if (offset < end)
							throw new Error("error loading hex file - offset lower than end");
						while (offset > end) {
							fwdata.push(255);
							pos++;
							end++;
						}
						for (var i = 0; i < reclen; i++) {
							fwdata.push(parseInt(data.substring(i * 2, (i * 2) + 2), 16));
							pos++;
						}
						end += reclen;
					}
				}
			}	
			var pad = end % 128; // ATMega328 has 64 words per page / 128 bytes per page
			for (var i = 0; i < 128 - pad; i++) {
				fwdata.push(255);
				pos++;
				end++;
			}
			var blocks = (end - start) / FIRMWARE_BLOCK_SIZE;
			var crc = 0xFFFF;
			for (var i = 0; i < blocks * FIRMWARE_BLOCK_SIZE; ++i) {
				var v = crc;
				crc = crcUpdate(crc, fwdata[i]);
			}
			db.collection('firmware', function(err, c) {
				c.update({
					'type': fwtype,
					'version': fwversion
				}, {
					$set: {
						'filename': filename,
						'blocks': blocks,
						'crc': crc,
						'data': fwdata
					}
				}, {
					upsert: true
				}, function(err, result) {
					if (err)
						console.log('Error writing firmware to database');
				});
			});
			console.log("loading firmware done. blocks: " + blocks + " / crc: " + crc);
                } else {
                        console.log("error: %j", res.body);
                }
        });
}

/*
function decode(msg) {
	var msgs = msg.toString().split(";");
	rsender = +msgs[0];
	rsensor = +msgs[1];
	rcommand = +msgs[2];
	rtype = +msgs[3];
	var pl = msgs[4].trim();
	rpayload = [];
	for (var i = 0; i < pl.length; i+=2) {
		var b = parseInt(pl.substring(i, i + 2), 16);
		rpayload.push(b);
	}
}
*/

function encode(destination, sensor, command, acknowledge, type, payload) {
	var msg = destination.toString(10) + ";" + sensor.toString(10) + ";" + command.toString(10) + ";" + acknowledge.toString(10) + ";" + type.toString(10) + ";";
	if (command == 4) {
		for (var i = 0; i < payload.length; i++) {
			if (payload[i] < 16)
				msg += "0";
			msg += payload[i].toString(16);
		}
	} else {
		msg += payload;
	}
	msg += '\n';
	return msg.toString();
}

function saveProtocol(sender, payload, db) {
	/*db.collection('node', function(err, c) {
		c.update({
			'id': sender
		}, {
			$set: {
				'protocol': payload
			}
		}, {
			upsert: true
		}, function(err, result) {
			if (err)
				console.log("Error writing protocol to database");
		});
	});*/
}

function saveSensor(sender, sensor, type) {
	LogDate("info", "Save saveSensor : Value-" + sender.toString() + "-" + sensor.toString()+ "-" + type.toString() );

	url = urlJeedom + "&messagetype=saveSensor&type=mySensors&id="+sender.toString()+"&sensor=" + sensor.toString() + "&value="+type;

		
	request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		LogDate("info", "Got response saveSensor: " + response.statusCode);
	  }
	});
}

function saveGateway(status) {
	LogDate("info", "Save Gateway Status " + status);

	url = urlJeedom + "&messagetype=saveGateway&type=mySensors&status="+status;

	request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		LogDate("info", "Got response saveSensor: " + response.statusCode);
	  }
	});
}

function saveValue(sender, sensor, type, payload) {
	LogDate("info", "Save Value : Value-" + payload.toString() + "-" + sender.toString() + "-" + sensor.toString() );

	url = urlJeedom + "&messagetype=saveValue&type=mySensors&id="+sender.toString()+"&sensor=" + sensor.toString()+"&type=" + type.toString() + "&value="+payload;

	LogDate("info", url);
	request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		LogDate("info", "Got response Value: " + response.statusCode);
	  }else{
	  
	  	LogDate("info", "SaveValue Error : "  + error );
	  }
	});
}

function saveBatteryLevel(sender, payload ) {
	 LogDate("info", "Save BatteryLevel : Value-" + sender.toString() + "-" + payload );

		url = urlJeedom + "&messagetype=saveBatteryLevel&type=mySensors&id="+sender.toString()+"&value="+payload;
	LogDate("info", url);
		request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		 LogDate("info", "Got response saveBatteryLevel: " + response.statusCode);
	  }
	});
}

function saveSketchName(sender, payload) {

	LogDate("info", "Save saveSketchName : Value-" + sender.toString() + "-" + payload );

		url = urlJeedom + "&messagetype=saveSketchName&type=mySensors&id="+sender.toString()+"&value="+payload;
	LogDate("info", url);
		request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		LogDate("info", "Got response saveSketchName: " + response.statusCode);
	  }
	});
	
}

function saveSketchVersion(sender, payload ) {

	LogDate("info", "Save saveSketchVersion : Value-" + sender.toString() + "-" + payload );

		url = urlJeedom + "&messagetype=saveSketchVersion&type=mySensors&id="+sender.toString()+"&value="+payload;
	LogDate("info", url);
		request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		LogDate("info", "Got response saveSketchVersion: " + response.statusCode);
	  }
	});
}

function saveLibVersion(sender, payload ) {

	LogDate("info", "Save saveLibVersion : Value-" + sender.toString() + "-" + payload );

		url = urlJeedom + "&messagetype=saveLibVersion&type=mySensors&id="+sender.toString()+"&value="+payload;
	LogDate("info", url);
		request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		LogDate("info", "Got response saveLibVersion: " + response.statusCode);
	  }
	});
}

function sendTime(destination, sensor, gw) {
	var payload = new Date().getTime();
 	var command = C_INTERNAL;
 	var acknowledge = 0; // no ack
 	var type = I_TIME;
 	var td = encode(destination, sensor, command, acknowledge, type, payload);
 	console.log('-> ' + td.toString());
 	gw.write(td);
}
	
function sendNextAvailableSensorId( gw) {
	
	var destination = BROADCAST_ADDRESS;
	var sensor = NODE_SENSOR_ID;
	var command = C_INTERNAL;
	var acknowledge = 0; // no ack
	var type = I_ID_RESPONSE;
	var payload = Math.floor((Math.random() * 200) + 1);
	var td = encode(destination, sensor, command, acknowledge, type, payload);
	console.log('-> ' + td.toString());
	gw.write(td);
	
	
}

function sendConfig(destination, gw) {
	var payload = "M";
	var sensor = NODE_SENSOR_ID;
	var command = C_INTERNAL;
	var acknowledge = 0; // no ack
	var type = I_CONFIG;
	var td = encode(destination, sensor, command, acknowledge, type, payload);
	console.log('-> ' + td.toString());
	gw.write(td);
}

function sendFirmwareConfigResponse(destination, fwtype, fwversion, db, gw) {
	// keep track of type/versin info for each node
	// at the same time update the last modified date
	// could be used to remove nodes not seen for a long time etc.
	db.collection('node', function(err, c) {
		c.update({
			'id': destination
		}, {
			$set: {
				'type': fwtype,
				'version': fwversion,
				'reboot': 0
			}
		}, {
			upsert: true
		}, function(err, result) {
			if (err)
				console.log("Error writing node type and version to database");
		});
	});
	if (fwtype == 0xFFFF) {
		// sensor does not know which type / blank EEPROM
		// take predefined type (ideally selected in UI prior to connection of new sensor)
		if (fwDefaultType == 0xFFFF)
			throw new Error('No default sensor type defined');
		fwtype = fwDefaultType;
	}
	db.collection('firmware', function(err, c) {
		c.findOne({
			$query: {
				'type': fwtype
			},
			$orderby: {
				'version': -1
			}
		}, function(err, result) {
			if (err)
				console.log('Error finding firmware for type ' + fwtype);
			else if (!result)
				console.log('No firmware found for type ' + fwtype);
			else {
				var payload = [];
				pushWord(payload, result.type);
				pushWord(payload, result.version);
				pushWord(payload, result.blocks);
				pushWord(payload, result.crc);
				var sensor = NODE_SENSOR_ID;
				var command = C_STREAM;
				var acknowledge = 0; // no ack
				var type = ST_FIRMWARE_CONFIG_RESPONSE;
				var td = encode(destination, sensor, command, acknowledge, type, payload);
				console.log('-> ' + td.toString());
				gw.write(td);
			}
		});
	});
}

function sendFirmwareResponse(destination, fwtype, fwversion, fwblock, db, gw) {
	db.collection('firmware', function(err, c) {
		c.findOne({
			'type': fwtype,
			'version': fwversion
		}, function(err, result) {
			if (err)
				console.log('Error finding firmware version ' + fwversion + ' for type ' + fwtype);
			var payload = [];
			pushWord(payload, result.type);
			pushWord(payload, result.version);
			pushWord(payload, fwblock);
			for (var i = 0; i < FIRMWARE_BLOCK_SIZE; i++)
				payload.push(result.data[fwblock * FIRMWARE_BLOCK_SIZE + i]);
			var sensor = NODE_SENSOR_ID;
			var command = C_STREAM;
			var acknowledge = 0; // no ack
			var type = ST_FIRMWARE_RESPONSE;
			var td = encode(destination, sensor, command, acknowledge, type, payload);
			console.log('-> ' + td.toString());
			gw.write(td);
		});
	});
}

function saveRebootRequest(destination, db) {
	db.collection('node', function(err, c) {
		c.update({
			'id': destination
		}, {
			$set: {
				'reboot': 1
			}
		}, function(err, result) {
			if (err)
				console.log("Error writing reboot request to database");
		});
	});
}

function checkRebootRequest(destination, db, gw) {
	db.collection('node', function(err, c) {
		c.find({
			'id': destination
		}, function(err, item) {
			if (err)
				console.log('Error checking reboot request');
			else if (item.reboot == 1)
				sendRebootMessage(destination, gw);
		});
	});
}

function sendRebootMessage(destination, gw) {
	var sensor = NODE_SENSOR_ID;
        var command = C_INTERNAL;
        var acknowledge = 0; // no ack
        var type = I_REBOOT;
        var payload = "";
        var td = encode(destination, sensor, command, acknowledge, type, payload);
        console.log('-> ' + td.toString());
        gw.write(td);
}


function appendData(str, db, gw) {
    pos=0;
    while (str.charAt(pos) != '\n' && pos < str.length) {
        appendedString=appendedString+str.charAt(pos);
        pos++;
    }
    if (str.charAt(pos) == '\n') {
        rfReceived(appendedString.trim(), db, gw);
        appendedString="";
    }
    if (pos < str.length) {
        appendData(str.substr(pos+1,str.length-pos-1), db, gw);
    }
}

function rfReceived(data, db, gw) {
	if ((data != null) && (data != "")) {
		console.log('<- ' + data);
		// decoding message
		var datas = data.toString().split(";");
		var sender = +datas[0];
		var sensor = +datas[1];
		var command = +datas[2];
		var ack = +datas[3];
		var type = +datas[4];
                var rawpayload="";
                if (datas[5]) {
                	rawpayload = datas[5].trim();
		}
		var payload;
		if (command == C_STREAM) {
			payload = [];
			for (var i = 0; i < rawpayload.length; i+=2)
				payload.push(parseInt(rawpayload.substring(i, i + 2), 16));
		} else {
			payload = rawpayload;
		}
		// decision on appropriate response
		switch (command) {
		case C_PRESENTATION:
			if (sensor == NODE_SENSOR_ID)
			//	saveProtocol(sender, payload, db); //arduino ou arduino relay
				;
			else
				saveSensor(sender, sensor, type);
				saveLibVersion(sender, payload);
			break;
		case C_SET:
			saveValue(sender, sensor, type, payload);
			break;
		case C_REQ:
			break;
		case C_INTERNAL:
			switch (type) {
			case I_BATTERY_LEVEL:
				saveBatteryLevel(sender, payload, db);
				break;
			case I_TIME:
				sendTime(sender, sensor, gw);
				break;
			case I_VERSION:
				saveLibVersion(sender, payload);
				break;
			case I_ID_REQUEST:
				sendNextAvailableSensorId(gw);
				break;
			case I_ID_RESPONSE:
				break;
			case I_INCLUSION_MODE:
				break;
			case I_CONFIG:
				sendConfig(sender, gw);
				break;
			case I_PING:
				break;
			case I_PING_ACK:
				break;
			case I_LOG_MESSAGE:
				break;
			case I_CHILDREN:
				break;
			case I_SKETCH_NAME:
				saveSketchName(sender, payload);
				break;
			case I_SKETCH_VERSION:
				saveSketchVersion(sender, payload);
				break;
			case I_REBOOT:
				break;
			}
			break;
		case C_STREAM:
			switch (type) {
					case ST_FIRMWARE_CONFIG_REQUEST:
							var fwtype = pullWord(payload, 0);
							var fwversion = pullWord(payload, 2);
							sendFirmwareConfigResponse(sender, fwtype, fwversion, db, gw);
							break;
					case ST_FIRMWARE_CONFIG_RESPONSE:
							break;
					case ST_FIRMWARE_REQUEST:
							var fwtype = pullWord(payload, 0);
							var fwversion = pullWord(payload, 2);
							var fwblock = pullWord(payload, 4);
							sendFirmwareResponse(sender, fwtype, fwversion, fwblock, db, gw);
							break;
					case ST_FIRMWARE_RESPONSE:
							break;
					case ST_SOUND:
							break; 
					case ST_IMAGE:
							break;
			}
			break;
		}
		//checkRebootRequest(sender, db, gw);
		
	}
}

	var db = null;

	//pour la connexion avec Jeedom => Node
	var pathsocket = '/tmp/mysensor.sock';
	fs.unlink(pathsocket, function () {
	  var server = net.createServer(function(c) {

		LogDate("info", "server connected");

		c.on('error', function(e) {
		  LogDate("error", "Error server disconnected");
		});
		
		c.on('close', function() {
		  LogDate("error", "server disconnected");
		});

		c.on('data', function(data) {
			LogDate("info", "Response: " + data);
			gw.write(data.toString() + '\n');
		});

	  });
	  server.listen(8019, function(e) {
		LogDate("info", "server bound on 8019");
	  });
	});
	
	if (gwType == 'Ethernet') {
		gw = require('net').Socket();
		gw.connect(gwPort, gwAddress);
		gw.setEncoding('ascii');
		gw.on('connect', function() {
			LogDate("info", "connected to ethernet gateway at " + gwAddress + ":" + gwPort);
		}).on('data', function(rd) {
			appendData(rd.toString(), db, gw);
		}).on('end', function() {
			LogDate("error", "disconnected from gateway");
		}).on('error', function() {
			LogDate("error", "connection error - trying to reconnect");
			gw.connect(gwPort, gwAddress);
			gw.setEncoding('ascii');
		});
	} else if (gwType == 'Serial') {
	
		var serialPort = require("serialport");
		var SerialPort = require('serialport').SerialPort;
		gw = new SerialPort(gwPort, { baudrate: gwBaud });
     	gw.open();
		gw.on('open', function() {
			LogDate("info", "connected to serial gateway at " + gwPort);
			saveGateway('1');
		}).on('data', function(rd) {
			appendData(rd.toString(), db, gw);
		}).on('end', function() {
			LogDate("error", "disconnected from gateway");
			saveGateway('0');
		}).on('error', function() {
			LogDate("error", "connection error - trying to reconnect");
			saveGateway('0');
			gw.open();
		});
	} 
	
	
	process.on('uncaughtException', function ( err ) {
    console.error('An uncaughtException was found, the program will end.');
    //hopefully do some logging.
    //process.exit(1);
});

    
