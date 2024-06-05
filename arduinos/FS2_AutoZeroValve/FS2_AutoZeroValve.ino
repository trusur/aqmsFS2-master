#include <SoftwareSerial.h>
int valve1 = 12;
int valve2 = 11;
int activeValve = 1;
char inChar;
void setup() {
    Serial.begin(9600);
    pinMode(valve1, OUTPUT);
    pinMode(valve2, OUTPUT);
    Serial.println("FS2_AUTO_ZERO_VALVE_START");
//    digitalWrite(valve1,HIGH);
//    delay(100);
//    digitalWrite(valve2,LOW);
//    delay(1000);
//    digitalWrite(valve1,LOW);
//    delay(100);
//    digitalWrite(valve2,HIGH);
//    delay(1000);
    digitalWrite(valve1,HIGH);
    delay(500);
    digitalWrite(valve2,LOW);
    activeValve = 1;
    delay(1000);
    Serial.println("FS2_AUTO_ZERO_VALVE_BEGIN");
    
}

void loop() {
    if (Serial.available() > 0) {
        inChar = Serial.read();
        if(inChar == 'i'){
            activeValve = 1;
            digitalWrite(valve1,HIGH);
            delay(500);
            digitalWrite(valve2,LOW);
        }else if(inChar == 'j'){
            activeValve = 2;
            digitalWrite(valve2,HIGH);
            delay(500);
            digitalWrite(valve1,LOW);
        }
        delay(1000);
    }
    Serial.print("FS2_AUTO_ZERO_VALVE;");
    Serial.print(activeValve);
    Serial.println(";");
    delay(1000);
}
