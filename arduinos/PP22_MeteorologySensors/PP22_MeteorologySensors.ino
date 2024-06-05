/*
 * BME280
 *  SDA => A4
 *  SCL => A5
 *  VCC => 3.3V
 *  GND => GND
 *  CSB => NC
 *  SDD => NC
 * 
 * AM2306 HUM & TEMP
 *  RED => 5V
 *  BLACK => GND
 *  YELLOW => D3
 * 
 * Davis 6415 Anemometer
 *  YELLOW => 5V
 *  RED => GND
 *  BLACK => D2
 *  GREEN => A0
 *  D2 to 5V using 4K7 ohm
 *  
 *  
 *  connectorn PIN:
 *    1 => A0
 *    2 => D2
 *    3 => D3
 *    4 => GND
 *    5 => 3V3
 *    6 => V5
 */



#include <Wire.h>
#include <SPI.h>
#include <Adafruit_BMP280.h>
#include "DHT.h"
#include "TimerOne.h" // Timer Interrupt set to 2 second for read sensors
#include <math.h>

#define DHTPIN 3
#define DHTTYPE DHT22
#define WindSensorPin (2) // The pin location of the anemometer sensor
#define WindVanePin (A0) // The pin the wind vane sensor is connected to
#define VaneOffset 0; // define the anemometer offset from magnetic north

int VaneValue; // raw analog value from wind vane
int Direction; // translated 0 - 360 direction
int CalDirection; // converted value with offset applied
int WindDirection;
int LastValue; // last direction value

volatile bool IsSampleRequired; // this is set true every 2.5s. Get wind speed
volatile unsigned int TimerCount; // used to determine 2.5sec timer count
volatile unsigned long Rotations; // cup rotation counter used in interrupt routine
volatile unsigned long ContactBounceTime; // Timer to avoid contact bounce in isr

float WindSpeed; // speed miles per hour

Adafruit_BMP280 bmp;
 
DHT dht(DHTPIN, DHTTYPE);
 
void setup() {
  Serial.begin(9600);
  bmp.begin(0x76);
  dht.begin();
  
  bmp.setSampling(Adafruit_BMP280::MODE_NORMAL,     /* Operating Mode. */
                  Adafruit_BMP280::SAMPLING_X2,     /* Temp. oversampling */
                  Adafruit_BMP280::SAMPLING_X16,    /* Pressure oversampling */
                  Adafruit_BMP280::FILTER_X16,      /* Filtering. */
                  Adafruit_BMP280::STANDBY_MS_500); /* Standby time. */

  LastValue = 0;
  IsSampleRequired = false;
  TimerCount = 0;
  Rotations = 0; // Set Rotations to 0 ready for calculations
  Serial.begin(9600);
  pinMode(WindSensorPin, INPUT);
  attachInterrupt(digitalPinToInterrupt(WindSensorPin), isr_rotation, FALLING);
  Timer1.initialize(500000);// Timer interrupt every 2.5 seconds
  Timer1.attachInterrupt(isr_timer);
  
  Serial.println("PP22_MeteorologySensors_START");
}
 
void loop() {
  delay(2000);
  float humOut = dht.readHumidity();
  float tempOut = dht.readTemperature();
  float tempIn = bmp.readTemperature();
  float pressure = bmp.readPressure()/100;
  float altitude = bmp.readAltitude(1013.25);

  if (isnan(humOut)){
    humOut = 0.0;
  }

  if (isnan(tempOut)){
    tempOut = 0.0;
  }

  if (isnan(tempIn)){
    tempIn = 0.0;
  }
  
  if (isnan(pressure)){
    pressure = 0.0;
  }
  
  if (isnan(altitude)){
    altitude = 0.0;
  }

  getWindDirection();
  if(abs(CalDirection - LastValue) > 5) {
      LastValue = CalDirection;
  }
  if(IsSampleRequired) {
      WindSpeed = Rotations * 0.9;
      WindDirection = CalDirection;
      Rotations = 0; // Reset count for next sample
      IsSampleRequired = false;
  }  
  
  Serial.print("PP22_MeteorologySensors;");
  Serial.print(humOut);
  Serial.print(";");
  Serial.print(tempOut);
  Serial.print(";");
  Serial.print(tempIn);
  Serial.print(";");
  Serial.print(pressure);
  Serial.print(";");
  Serial.print(altitude);
  Serial.print(";");
  Serial.print(getkph(WindSpeed));
  Serial.print(";");
  Serial.print(CalDirection);
  Serial.println(";");
}

void isr_timer() {
    TimerCount++;
    if(TimerCount == 6){
        IsSampleRequired = true;
        TimerCount = 0;
    }
}

void isr_rotation() {
    if((millis() - ContactBounceTime) > 15 ) { // debounce the switch contact.
        Rotations++;
        ContactBounceTime = millis();
    }
}

float getkph(float speed){
  return speed * 1.609;
}

void getWindDirection() {
    VaneValue = analogRead(WindVanePin);
    Direction = map(VaneValue, 0, 1023, 0, 359);
    CalDirection = Direction + VaneOffset;
    if(CalDirection > 360)
        CalDirection = CalDirection - 360;
    if(CalDirection < 0)
        CalDirection = CalDirection + 360;
}
