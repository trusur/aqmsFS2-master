#include <Wire.h>
#include "Adafruit_SGP30.h"

Adafruit_SGP30 sgp;
int voc = 0;
int co2 = 0;
int h2 = 0;
int ethanol = 0;

uint32_t getAbsoluteHumidity(float temperature, float humidity) {
    // approximation formula from Sensirion SGP30 Driver Integration chapter 3.15
    const float absoluteHumidity = 216.7f * ((humidity / 100.0f) * 6.112f * exp((17.62f * temperature) / (243.12f + temperature)) / (273.15f + temperature)); // [g/m^3]
    const uint32_t absoluteHumidityScaled = static_cast<uint32_t>(1000.0f * absoluteHumidity); // [mg/m^3]
    return absoluteHumidityScaled;
}

void setup() {
  Serial.begin(9600);
  Serial.println("FS2_SGP30_START");

  if (! sgp.begin()){
    Serial.println("FS2_SGP30_Sensor_not_found");
    while (1);
  }
  // If you have a baseline measurement from before you can assign it to start, to 'self-calibrate'
  //sgp.setIAQBaseline(0x8E68, 0x8F41);  // Will vary for each sensor!
}

int counter = 0;
void loop() {
  if (! sgp.IAQmeasure()) {
    voc = 0;
    co2 = 0;
    return;
  } else {
    voc = sgp.TVOC;
    co2 = sgp.eCO2;
  }
  
  if (! sgp.IAQmeasureRaw()) {
    h2 = 0;
    ethanol = 0;
    return;
  }else{
    h2 = sgp.rawH2;
    ethanol = sgp.rawEthanol;
  }
  Serial.println("FS2_SGP30;" + String(voc) + ";" + String(co2) + ";" + String(h2) + ";" + String(ethanol) + ";");
 
  delay(1000);
  /*
  counter++;
  if (counter == 30) {
    counter = 0;

    uint16_t TVOC_base, eCO2_base;
    if (! sgp.getIAQBaseline(&eCO2_base, &TVOC_base)) {
      Serial.println("Failed to get baseline readings");
      return;
    }
    Serial.print("****Baseline values: eCO2: 0x"); Serial.print(eCO2_base, HEX);
    Serial.print(" & TVOC: 0x"); Serial.println(TVOC_base, HEX);
  }*/
}
