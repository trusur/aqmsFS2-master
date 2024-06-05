void setup() {
  Serial.begin(9600);
  Serial.println("PP22_ANALOGINPUT_SENSORS_START");
  pinMode(A0, INPUT);
  pinMode(A1, INPUT);
  pinMode(A2, INPUT);
  pinMode(A3, INPUT);
  pinMode(A4, INPUT);
}

void loop() { int a0_read = analogRead(A0);
  int a0 = analogRead(A0);
  int a1 = analogRead(A1);
  int a2 = analogRead(A2);
  int a3 = analogRead(A3);
  int a4 = analogRead(A4);
  Serial.println("PP22_ANALOGINPUT_SENSORS;" + String(a0) + ";" + String(a1) + ";" + String(a2) + ";" + String(a3) + ";" + String(a4) + ";");
  delay(1000);
}
