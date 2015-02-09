#include <SPI.h>
#include <MySensor.h>  
#include <DHT.h>  

#define CHILD_ID_HUM 0
#define HUMIDITY_SENSOR_DIGITAL_PIN 3
unsigned long SLEEP_TIME = 30000; // Sleep time between reads (in milliseconds)

MySensor gw;
DHT dht;
float lastTemp;
boolean metric = true; 
MyMessage msgHum(CHILD_ID_HUM, V_HUM);


void setup()  
{ 
  gw.begin();

  // Send the Sketch Version Information to the Gateway
  gw.sendSketchInfo("Config", "1.0");

  // Register all sensors to gw (they will be created as child devices)
  gw.present(CHILD_ID_HUM, S_HUM);
  
    metric = gw.getConfig().isMetric;
  Serial.print(metric);
  
}

void loop()      
{  
    metric = gw.getConfig().isMetric;
  Serial.print(metric);

  gw.sleep(SLEEP_TIME); //sleep a bit
}
