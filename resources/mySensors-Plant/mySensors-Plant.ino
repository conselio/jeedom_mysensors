#include <SPI.h>
#include <MySensor.h>  
#include <DHT.h> 

#define CHILD_ID_HUM 0
#define CHILD_ID_TEMP 1
#define CHILD_ID_MOTION 2
#define CHILD_ID_LIGHT 3

#define LIGHT_SENSOR_ANLAOG_PIN 0 // Analog input for light sensor
#define DIGITAL_INPUT_SOIL_SENSOR 3   // Digital input did you attach your soil sensor.  
#define HUMIDITY_SENSOR_DIGITAL_PIN 4 // Digital input for DHT sensor
#define INTERRUPT DIGITAL_INPUT_SOIL_SENSOR-2 // Usually the interrupt = pin -2 (on uno/nano anyway)
unsigned long SLEEP_TIME = 30000; // Sleep time between reads (in milliseconds)

MySensor gw;
DHT dht;
int oldValue=-1;
float lastTemp;
float lastHum;
int lastSoilValue = -1;
boolean metric = true; 
MyMessage msgHum(CHILD_ID_HUM, V_HUM);
MyMessage msgTemp(CHILD_ID_TEMP, V_TEMP);
MyMessage msgMot(CHILD_ID_MOTION, V_TRIPPED);
MyMessage msgLight(CHILD_ID_LIGHT, V_LIGHT_LEVEL);
int lastLightLevel;


void setup()  
{ 
  gw.begin();
  dht.setup(HUMIDITY_SENSOR_DIGITAL_PIN); 
  
  pinMode(DIGITAL_INPUT_SOIL_SENSOR, INPUT);       // sets the motion sensor digital pin as input

  // Send the Sketch Version Information to the Gateway
  gw.sendSketchInfo("Plant-Sensor", "1.0");

  // Register all sensors to gw (they will be created as child devices)
  gw.present(CHILD_ID_HUM, S_HUM);
  gw.present(CHILD_ID_TEMP, S_TEMP);
  gw.present(CHILD_ID_MOTION, S_MOTION);
  gw.present(CHILD_ID_LIGHT, S_LIGHT_LEVEL);
  
}

void loop()      
{  
  delay(dht.getMinimumSamplingPeriod());

  float temperature = dht.getTemperature();
  if (isnan(temperature)) {
      Serial.println("Failed reading temperature from DHT");
  } else if (temperature != lastTemp) {
    lastTemp = temperature;
    gw.send(msgTemp.set(temperature, 1));
    Serial.print("T: ");
    Serial.println(temperature);
  }
  
  float humidity = dht.getHumidity();
  if (isnan(humidity)) {
      Serial.println("Failed reading humidity from DHT");
  } else if (humidity != lastHum) {
      lastHum = humidity;
      gw.send(msgHum.set(humidity, 1));
      Serial.print("H: ");
      Serial.println(humidity);
  }

  // Read digital soil value
  int soilValue = digitalRead(DIGITAL_INPUT_SOIL_SENSOR); // 1 = Not triggered, 0 = In soil with water 
  if (soilValue != lastSoilValue) {
    Serial.println(soilValue);
    gw.send(msgMot.set(soilValue==0?1:0));  // Send the inverse to gw as tripped should be when no water in soil
    lastSoilValue = soilValue;
  }
  
  int lightLevel = (1023-analogRead(LIGHT_SENSOR_ANLAOG_PIN))/10.23; 
  Serial.println(lightLevel);
  if (lightLevel != lastLightLevel) {
      gw.send(msgLight.set(lightLevel));
      lastLightLevel = lightLevel;
  }
 
  // Sleep until interrupt comes in on motion sensor. Send update every two minute. 
  gw.sleep(INTERRUPT,CHANGE, SLEEP_TIME);
}



