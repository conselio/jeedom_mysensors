//  sketch pour piloter un ou plusieurs relais
//  avec un mode normal, implusionnel et bistable
// 

#include <MySensor.h>
#include <SPI.h>

// pour activer ou désactiver un mode il suffit de mettre ou d'enlever les "//"

 
//#define Mode_Normal
//#define Mode_Impulsion
#define Mode_Bistable

#ifdef Mode_Normal
// paramettre pour le mode normal
#define RELAY_1  6  // N° de la première pin Arduino du relai mode normal
#define NUMBER_OF_RELAYS 1 // Total du nombre de relai
#endif
#ifdef Mode_Impulsion
// paramettre pour le mode Implusionel
#define RELAYImpuls_1  6  //N° de la première pin Arduino du relai mode impulsionnel (Attention au mode normal)
#define NUMBER_OF_RELAYSImpuls 1 // Total number of attached relays
#define TpsPulse 1000 // temps de impulsion
#endif
#ifdef Mode_Bistable
//parametre pour mode Bistable
#define RELAYBistable_1  6  //N° de la première pin Arduino du relai mode impulsionnel (Attention auautre mode)
#define NUMBER_OF_RELAYSBistable 1 // Total number of attached relays
#define Pulse 1000 // temps pour changer état
#endif
//
#define RELAY_ON 1 
#define RELAY_OFF 0 


MySensor gw;

void setup() 
{   
  // Initialize library and add callback for incoming messages
  gw.begin(incomingMessage, AUTO);
  // Send the sketch version information to the gateway and Controller
  gw.sendSketchInfo("Relay", "1.0");

int NbRelay = 0;
#ifdef Mode_normal
  // Init relais
  for (int sensor=1, pin=RELAY_1; sensor<=NUMBER_OF_RELAYS;sensor++, pin++) {
    // Register all sensors to gw (they will be created as child devices)
    gw.present(sensor, S_LIGHT);
    // Then set relay pins in output mode
    pinMode(pin, OUTPUT);   
    // Set relay to last known state (using eeprom storage)
    digitalWrite(pin, gw.loadState(sensor)?RELAY_ON:RELAY_OFF);
  }
 NbRelay = NUMBER_OF_RELAYS;
#endif
#ifdef Mode_Impulsion
  // Init relais
  for (int sensor=1 + NbRelay, pin=RELAYImpuls_1; sensor<=NUMBER_OF_RELAYSImpuls + NbRelay;sensor++, pin++) {
    // Register all sensors to gw (they will be created as child devices)
    gw.present(sensor, S_LIGHT);
    // Then set relay pins in output mode
    pinMode(pin, OUTPUT);   
    // Set relay to last known state (using eeprom storage)
    digitalWrite(pin, LOW);
  }
NbRelay = NbRelay + NUMBER_OF_RELAYSImpuls;
#endif
#ifdef Mode_Bistable
  // Init relais
  for (int sensor=1 + NbRelay, pin=RELAYBistable_1; sensor<=NUMBER_OF_RELAYSBistable + NbRelay;sensor++, pin++) {
    // Register all sensors to gw (they will be created as child devices)
    gw.present(sensor, V_VAR1);
    // Then set relay pins in output mode
    pinMode(pin, OUTPUT);   
    // Set relay to last known state (using eeprom storage)
    digitalWrite(pin, LOW);
   
  }
#endif



}


void loop()
{
  // Alway process incoming messages whenever possible
  gw.process();
}

void incomingMessage(const MyMessage &message) {
#ifdef Mode_Normal
  // Si recetion d'un ordre pour un relai Mode normal
  if (message.type==V_LIGHT) {
     // Change relay state
     digitalWrite(message.sensor-1+RELAY_1, message.getBool()?RELAY_ON:RELAY_OFF);
     // Store state in eeprom
     gw.saveState(message.sensor, message.getBool());
   }
#endif
#ifdef Mode_Impulsion
  // Si recetion d'un ordre pour un relai Mode Impulsionnel
  if (message.type==V_VAR1) {
     // Change relay state
     digitalWrite(message.sensor-1+RELAYImpuls_1, HIGH);
     delay(TpsPulse);
     digitalWrite(message.sensor-1+RELAYImpuls_1, LOW);
    // Store state in eeprom
     gw.saveState(message.sensor, LOW);
   }
#endif
#ifdef Mode_Bistable   
   // Si recetion d'un ordre pour un relai Mode Bistable
   if (message.type==V_VAR2) {
     Serial.println(message.type);
     Serial.print("sensor : ");Serial.println(message.sensor);
     Serial.print("Pin : ");Serial.println(message.sensor-1+RELAYBistable_1);
     Serial.print("data : ");Serial.println(message.data);
     Serial.print("Memoire : ");Serial.println(gw.loadState(message.sensor));
     // Change relay state
     if (message.getBool()!=gw.loadState(message.sensor)){
        Serial.println("changement d'etat");
     digitalWrite(message.sensor-1+RELAYBistable_1, HIGH);
     delay(Pulse);
     digitalWrite(message.sensor-1+RELAYBistable_1, LOW);
     // Store state in eeprom
     Serial.print("GetBool : ");Serial.println(message.getBool());
     gw.saveState(message.sensor, message.getBool());
     }
   }
#endif   
}
