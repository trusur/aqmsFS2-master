#include "TimerOne.h" // Timer Interrupt set to 2 second for read sensors
#include <math.h>

#define WindSensorPin (2) // The pin location of the anemometer sensor
#define WindVanePin (A4) // The pin the wind vane sensor is connected to
#define VaneOffset 0; // define the anemometer offset from magnetic north

int VaneValue; // raw analog value from wind vane
int Direction; // translated 0 - 360 direction
int CalDirection; // converted value with offset applied
int LastValue; // last direction value

volatile bool IsSampleRequired; // this is set true every 2.5s. Get wind speed
volatile unsigned int TimerCount; // used to determine 2.5sec timer count
volatile unsigned long Rotations; // cup rotation counter used in interrupt routine
volatile unsigned long ContactBounceTime; // Timer to avoid contact bounce in isr

float WindSpeed; // speed miles per hour

void setup() {
    LastValue = 0;
    IsSampleRequired = false;
    TimerCount = 0;
    Rotations = 0; // Set Rotations to 0 ready for calculations
    Serial.begin(9600);
    pinMode(WindSensorPin, INPUT);
    attachInterrupt(digitalPinToInterrupt(WindSensorPin), isr_rotation, FALLING);
    Serial.println("PP22_DavisWindSensor_START");
    Timer1.initialize(500000);// Timer interrupt every 2.5 seconds
    Timer1.attachInterrupt(isr_timer);
}

void loop() {
    getWindDirection();
    if(abs(CalDirection - LastValue) > 5) {
        LastValue = CalDirection;
    }
    if(IsSampleRequired) {
        WindSpeed = Rotations * 0.9;
        Rotations = 0; // Reset count for next sample
        IsSampleRequired = false;
        Serial.print("PP22_DavisWindSensor;");
        Serial.print(getkph(WindSpeed));
        Serial.print(";");
        Serial.print(CalDirection);
        Serial.println(";");
        delay(1000);
    }
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