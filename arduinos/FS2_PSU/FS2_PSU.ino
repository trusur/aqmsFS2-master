#include <Arduino.h>
#include <Wire.h>
#include "Adafruit_SHT31.h"

float temp = 0.0;
float hum = 0.0;
int pinPeltier = 3;
int pinFanPwm = 6;
bool isSHT = false;

Adafruit_SHT31 sht31 = Adafruit_SHT31();


void setup() {
  Serial.begin(9600);
  pinMode(pinPeltier,OUTPUT);
  pinMode(pinFanPwm,OUTPUT);
  Serial.println("FS2_PSU_START");
  digitalWrite(pinFanPwm, HIGH);
  delay(1000);
  digitalWrite(pinFanPwm, LOW);
  delay(1000);
  digitalWrite(pinFanPwm, HIGH);
  delay(1000);
  digitalWrite(pinPeltier, HIGH);
  Serial.println("Connecting SHT31...");
  if (! sht31.begin(0x44)) {   // Set to 0x45 for alternate i2c addr
    Serial.println("Couldn't find SHT31");
  }else{
    isSHT = true;
  }
  delay(1000);
  Serial.println("FS2_PSU_BEGIN");
}

void loop() {
  if(!isSHT){
    if (sht31.begin(0x44)) {   // Set to 0x45 for alternate i2c addr
      isSHT = true;
    }
  }
  
  if(isSHT){
    temp = sht31.readTemperature();
    hum = sht31.readHumidity();
  } else {
    temp = 0.0;
    hum = 0.0;
  }

  Serial.print("FS2_PSU;");
  Serial.print(temp);
  Serial.print(";");
  Serial.print(hum);
  Serial.println(";");
  delay(1000);
}