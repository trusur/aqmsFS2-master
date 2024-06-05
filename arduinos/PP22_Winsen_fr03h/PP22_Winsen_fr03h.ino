int flowpin = A0;

void setup() {
  Serial.begin(9600);
}

void loop() {
    Serial.println(analogRead(flowpin));    
    delay(1000);
}
