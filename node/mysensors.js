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

	console.log("30-10-2014 10:24:05 | info | Save saveSensor : " + "Value-" + sender.toString() + "-" + sensor.toString()+ "-" + type.toString() );

	url = urlJeedom + "&messagetype=saveSensor&type=mySensors&id="+sender.toString()+"&sensor=" + sensor.toString() + "&value="+type;

		
	request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		console.log("Got response saveSensor: " + response.statusCode);
	  }
	});

	

}

function saveValue(sender, sensor, type, payload) {


	console.log(new Date.toString(now) + " | info | Save Value : " + "Value-" + sender.toString() + "-" + sensor.toString() );

	
	url = urlJeedom + "&messagetype=saveValue&type=mySensors&id="+sender.toString()+"&sensor=" + sensor.toString() + "&value="+payload;

			console.log(new Date.toString(now) + " | info | " + url);
	request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		console.log(new Date.toString(now) + " info | Got response Value: " + response.statusCode);
	  }else{
	  
	  	console.log('SaveValue Error : '  + error );
	  }
	});
	
	

}

function saveBatteryLevel(sender, payload ) {


	console.log("Save BatteryLevel : " + "Value-" + sender.toString() + "-" + payload );

		url = urlJeedom + "&messagetype=saveBatteryLevel&type=mySensors&id="+sender.toString()+"&value="+payload;

		request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		console.log("Got response saveSketchName: " + response.statusCode);
	  }
	});
}

function saveSketchName(sender, payload) {

	console.log("Save saveSketchName : " + "Value-" + sender.toString() + "-" + payload );

		url = urlJeedom + "&messagetype=saveSketchName&type=mySensors&id="+sender.toString()+"&value="+payload;

		request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		console.log("Got response saveSketchName: " + response.statusCode);
	  }
	});
	
}

function saveSketchVersion(sender, payload ) {

	console.log("Save saveSketchVersion : " + "Value-" + sender.toString() + "-" + payload );

		url = urlJeedom + "&messagetype=saveSketchVersion&type=mySensors&id="+sender.toString()+"&value="+payload;

		request(url, function (error, response, body) {
	  if (!error && response.statusCode == 200) {
		console.log("Got response saveSketchVersion: " + response.statusCode);
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

		console.log('server connected');

		c.on('error', function(e) {
		  console.log('Error server disconnected');
		});
		
		c.on('close', function() {
		  console.log(new Date.toString() + ' | server disconnected');
		});

		c.on('data', function(data) {
			console.log('Response: "' + data + '"');
			gw.write(data.toString() + '\n');
		});

	  });
	  server.listen(8019, function(e) {
		console.log(new Date.toString() + ' | info | server bound on 8019');
	  });
	});
	
	if (gwType == 'Ethernet') {
		gw = require('net').Socket();
		gw.connect(gwPort, gwAddress);
		gw.setEncoding('ascii');
		gw.on('connect', function() {
			console.log('connected to ethernet gateway at ' + gwAddress + ":" + gwPort);
		}).on('data', function(rd) {
			appendData(rd.toString(), db, gw);
		}).on('end', function() {
			console.log('disconnected from gateway');
		}).on('error', function() {
			console.log('connection error - trying to reconnect');
			gw.connect(gwPort, gwAddress);
			gw.setEncoding('ascii');
		});
	} else if (gwType == 'Serial') {
	
		var serialPort = require("serialport");
		var SerialPort = require('serialport').SerialPort;
		gw = new SerialPort(gwPort, { baudrate: gwBaud });
     	gw.open();
		gw.on('open', function() {
			console.log('connected to serial gateway at ' + gwPort);
		}).on('data', function(rd) {
			appendData(rd.toString(), db, gw);
		}).on('end', function() {
			console.log('disconnected from gateway');
		}).on('error', function() {
			console.log('connection error - trying to reconnect');
			gw.open();
		});
	} 
	
	
	process.on('uncaughtException', function ( err ) {
    console.error('An uncaughtException was found, the program will end.');
    //hopefully do some logging.
    //process.exit(1);
});

    
