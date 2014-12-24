// Example sketch showing how to control physical relays.
// This example will remember relay state even after power failure.

#include <MySensor.h>
#include <SPI.h>
#include <RCSwitch.h>

#define RELAY_1  1  // Arduino Digital I/O pin number for first relay (second on pin+1 etc)
#define NUMBER_OF_RELAYS 6 // Total number of attached relays
#define RELAY_ON 1  // GPIO value to write to turn on attached relay
#define RELAY_OFF 0 // GPIO value to write to turn off attached relay
// RCSwitch configuration
RCSwitch mySwitch = RCSwitch();
int RCTransmissionPin = 3;
int rcGroup = 1;
int rcAddr = 1;

MySensor gw;

void setup()
{
  // Initialize library and add callback for incoming messages
  gw.begin(incomingMessage, AUTO, true);
  // Send the sketch version information to the gateway and Controller
  gw.sendSketchInfo("RCSwitch", "1.0");
  // Initialize RF Transmitter
  mySwitch.enableTransmit( RCTransmissionPin );

  // Fetch relay status
  for (int plug=1; plug <= NUMBER_OF_RELAYS ;plug++) {
    // Register all sensors to gw (they will be created as child devices)
    gw.present(plug, S_LIGHT);
    gw.sendBatteryLevel(0);
  }
}


void loop()
{
  // Alway process incoming messages whenever possible
  gw.process();
}

void incomingMessage(const MyMessage &message) {
  // We only expect one type of message from controller. But we better check anyway.
  if (message.type==2) {
     // Get the sensor for using rcswitch
     if (message.getBool() == 1) {
     	switch (message.sensor) {
     		case 1:
     			mySwitch.switchOn(1,1);
     			break;
     		case 2:
     			mySwitch.switchOn(1,2);
     			break;
     		case 3:
    	 		mySwitch.switchOn(1,3);
     			break;
     		case 4:
     			mySwitch.switchOn(2,1);
     			break;
     		case 5:
     			mySwitch.switchOn(2,2);
     			break;
     		case 6:
     			mySwitch.switchOn(2,3);
     			break;
     	}
     } else {
     	switch (message.sensor) {
     		case 1:
     			mySwitch.switchOff(1,1);
     			break;
     		case 2:
     			mySwitch.switchOff(1,2);
     			break;
     		case 3:
    	 		mySwitch.switchOff(1,3);
     			break;
     		case 4:
     			mySwitch.switchOff(2,1);
     			break;
     		case 5:
     			mySwitch.switchOff(2,2);
     			break;
     		case 6:
     			mySwitch.switchOff(2,3);
     			break;
     	}
     }

     	Serial.print("Recu");

     // Store state in eeprom
     gw.saveState(message.sensor, message.getBool());
     // Write some debug info
     Serial.print("Incoming change for sensor:");
     Serial.print(message.sensor);
     Serial.print(", New status: ");
     Serial.println(message.getBool());
     Serial.print(", Message Type: ");
     Serial.println(message.type);
     gw.sendBatteryLevel(0);
   }
}
