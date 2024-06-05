#include <Arduino.h>
#include <Wire.h>
#include "Adafruit_SHT31.h"

String string_pm01 = "";
String string_pm02 = "";

int pinFanPwm = 8;
int pinVacuum = A1;
int vacuum = 0;
float temp1 = 0.0;
float temp2 = 0.0;
float hum1 = 0.0;
float hum2 = 0.0;
bool isSHT_1 = false;
bool isSHT_2 = false;

Adafruit_SHT31 sht31_1 = Adafruit_SHT31();
Adafruit_SHT31 sht31_2 = Adafruit_SHT31();


void setup() {
    Serial.begin(9600);
    Serial1.begin(9600);
    Serial2.begin(9600);
    Serial.println("FS2_ANALYZER_START");
    pinMode(pinFanPwm, OUTPUT);
    delay(1000);
    digitalWrite(pinFanPwm, HIGH);
    Serial.println("Connecting SHT31 I...");
    if (! sht31_1.begin(0x44)) {   // Set to 0x45 for alternate i2c addr
      Serial.println("Couldn't find SHT31 I");
    }else{
      isSHT_1 = true;
    }
    delay(10);
    Serial.println("Connecting SHT31 II...");
    if (! sht31_2.begin(0x45)) {   // Set to 0x45 for alternate i2c addr
      Serial.println("Couldn't find SHT31 II");
    }else{
      isSHT_2 = true;
    }
    delay(1000);
    Serial.println("FS2_ANALYZER_BEGIN");
}

String getValue(String data)
{
    String retval = "";
    String temp = "";
    if(data.length() > 0){
      temp += data[0];
      temp += data[1];
      temp += data[2];
      temp += data[3];
      if(temp == "000."){
        int comas = 0;
        for(int i=0;i<data.length() - 1;i++){
          if(comas < 2 && data[i] != ','){
            retval += data[i];
          } else {
            if(comas < 1) retval += "|||";
            comas++;
          }
        }
      } else {
        retval = "000.000|||0.0";
      }
    }
    return "|||" + retval + "|||";
}

void loop() {
  if(!isSHT_1){
    if (sht31_1.begin(0x44)) {   // Set to 0x45 for alternate i2c addr
      isSHT_1 = true;
    }
  }
  
  if(isSHT_1){
    temp1 = sht31_1.readTemperature();
    hum1 = sht31_1.readHumidity();
  } else {
    temp1 = 0.0;
    hum1 = 0.0;
  }
  
  if(!isSHT_2){
    if (sht31_2.begin(0x45)) {   // Set to 0x45 for alternate i2c addr
      isSHT_2 = true;
    }
  }
    
  if(isSHT_2){
    temp2 = sht31_2.readTemperature();
    hum2 = sht31_2.readHumidity();
  } else {
    temp2 = 0.0;
    hum2 = 0.0;
  }

  
  vacuum = analogRead(pinVacuum);
  string_pm01 = "";
  if(Serial1.available() > 0){
    string_pm01 = "";
    while(Serial1.available() > 0){
      char pm01_read = Serial1.read();
      if(pm01_read != 13 && pm01_read != 10){string_pm01 += pm01_read;}
    }
  }

  string_pm02 = "";
  if(Serial2.available() > 0){
    string_pm02 = "";
    while(Serial2.available() > 0){
      char pm02_read = Serial2.read();
      if(pm02_read != 13 && pm02_read != 10){string_pm02 += pm02_read;}
    }
  }
  
  Serial.println("FS2_ANALYZER;" + getValue(string_pm01) + ";" + getValue(string_pm02) + ";" + vacuum + ";" + temp1 + ";" + hum1 + ";" + temp2 + ";" + hum2 + ";");
  delay(2000);
}
