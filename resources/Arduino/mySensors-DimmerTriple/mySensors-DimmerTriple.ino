/***
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * version 2 as published by the Free Software Foundation.
 * 
 * DESCRIPTION
 * This sketch provides a Dimmable LED Light using PWM and based Henrik Ekblad 
 * <henrik.ekblad@gmail.com> Vera Arduino Sensor project.  
 * Developed by Bruce Lacey, inspired by Hek's MySensor's example sketches.
 * 
 * The circuit uses a MOSFET for Pulse-Wave-Modulation to dim the attached LED or LED strip.  
 * The MOSFET Gate pin is connected to Arduino pin 3 (LED_PIN), the MOSFET Drain pin is connected
 * to the LED negative terminal and the MOSFET Source pin is connected to ground.  
 *
 * This sketch is extensible to support more than one MOSFET/PWM dimmer per circuit.
 *
 * REVISION HISTORY
 * Version 1.0 - February 15, 2014 - Bruce Lacey
 * Version 1.1 - August 13, 2014 - Converted to 1.4 (hek) 
 *
 ***/
#define SN "DimmableLED"
#define SV "1.1"

#include <MySensor.h> 
#include <SPI.h>

#define DIMMER_1  3  // Arduino Digital I/O pin number for first relay (second on pin+1 etc)
#define NUMBER_OF_DIMMER 1 // Total number of attached relays
#define FADE_DELAY 10  // Delay in ms for each percentage fade up/down (10ms = 1s full-range dim)

MySensor gw(9,10);

int currentLevel[10];  // Current dim level...
MyMessage dimmerMsg(0, V_DIMMER);
MyMessage lightMsg(0, V_LIGHT);


/***
 * Dimmable LED initialization method
 */
void setup()  
{ 
  Serial.println( SN ); 
  gw.begin( incomingMessage );
  gw.sendSketchInfo(SN, SV);
  
    for (int sensor=1, pin=DIMMER_1; sensor<=NUMBER_OF_DIMMER;sensor++, pin++) {
    // Register all sensors to gw (they will be created as child devices)
    gw.present(sensor, S_DIMMER);
    currentLevel[sensor] = 0;
    // Then set relay pins in output mode
    pinMode(pin, OUTPUT);   
  }
}

/***
 *  Dimmable LED main processing loop 
 */
void loop() 
{
  gw.process();
}



void incomingMessage(const MyMessage &message) {
  if (message.type == V_LIGHT || message.type == V_DIMMER) {
    
    //  Retrieve the power or dim level from the incoming request message
    int requestedLevel = atoi( message.data );
    
    // Adjust incoming level if this is a V_LIGHT variable update [0 == off, 1 == on]
    requestedLevel *= ( message.type == V_LIGHT ? 100 : 1 );
    
    // Clip incoming level to valid range of 0 to 100
    requestedLevel = requestedLevel > 100 ? 100 : requestedLevel;
    requestedLevel = requestedLevel < 0   ? 0   : requestedLevel;
    
    Serial.print( "Changing level to " );
    Serial.print( requestedLevel );
    Serial.print( ", from " ); 
    Serial.println( currentLevel[message.sensor] );

    fadeToLevel( requestedLevel, message.sensor );
    
    }
}

/***
 *  This method provides a graceful fade up/down effect
 */
void fadeToLevel( int toLevel, int senSor ) {

  int delta = ( toLevel - currentLevel[senSor] ) < 0 ? -1 : 1;
  int pin = DIMMER_1-1+senSor;
  
  while ( currentLevel[senSor] != toLevel ) {
    currentLevel[senSor] += delta;
    analogWrite( pin, (int)(currentLevel[senSor] / 100. * 255) );
    delay( FADE_DELAY );
  }
}


