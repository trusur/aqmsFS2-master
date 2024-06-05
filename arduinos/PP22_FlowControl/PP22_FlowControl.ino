int ntcpin = A0;
int flowpin = A1;
int pwmpin = 11;
int heaterpin = 12;
int adjpin = A2;
int pumpspeed = 20; // edit sesuai start awal kecepatan pompa yang diinginkan dalam persen
int sccm = 0;
int adj = 0;
int ntc = 0;
int ntcmax = 800; // edit sesuai suhu maksimal heater
int ntcmin = 700; // edit sesuai suhu minimal heater
int sccmmin = 300; // edit sesuai flow toleransi minimum
int sccmmax = 450; // edit sesuai flow toleransi maksimum
int heaterstate = LOW;

void setup() {
  Serial.begin(9600);
  pinMode(pwmpin, OUTPUT);
  pinMode(heaterpin, OUTPUT);
  digitalWrite(heaterpin, HIGH);
  delay(1000);
  digitalWrite(heaterpin, LOW);
}

void loop() {
    //Pump
    sccm = analogRead(flowpin);
    adj = map(analogRead(adjpin),0,1023,-512,512);
    sccm = sccm + adj;
    Serial.println("Adj \t\t= " + String(adj)); 
    Serial.println("Pumpspeed \t= " + String(pumpspeed));
    Serial.println("Sccm \t\t= " + String(sccm));
    if (sccm < sccmmin){
      pumpspeed++;
    }
    if (sccm > sccmmax){
      pumpspeed --;
    }
    analogWrite(pwmpin, map(pumpspeed,0,100,0,255));

    //heater
    ntc = analogRead(ntcpin);
    if (ntc > ntcmax){
      heaterstate = LOW;
    }
    if (ntc < ntcmin){
      heaterstate = HIGH;
    }
    Serial.println("ntc \t\t= " + String(ntc));
    Serial.println("Heaterstate \t= " + String(heaterstate));
    digitalWrite(heaterpin, heaterstate);
    Serial.println("");
    
    delay(1000);
}
