int relay1 = 22;
int relay2 = 23;
int relay3 = 24;
int relay4 = 25;
int count1 = 0;
int count2 = 0;
int count3 = 0;
int count4 = 0;
int statestatus = LOW;
int state1 = LOW;
int state2 = LOW;
int state3 = LOW;
int state4 = LOW;
int toggle1 = 3600; // 1 jam
int toggle2 = 7200; // 2 jam
int toggle3 = 14400; // 4 jam
int toggle4 = 21600; // 6 Jam

void setup() {
  pinMode(relay1, OUTPUT);
  pinMode(relay2, OUTPUT);
  pinMode(relay3, OUTPUT);
  pinMode(relay4, OUTPUT);
  pinMode(13, OUTPUT);
  digitalWrite(relay1, HIGH);
  digitalWrite(relay2, HIGH);
  digitalWrite(relay3, HIGH);
  digitalWrite(relay4, HIGH);
  delay(2000);
  digitalWrite(relay1, LOW);
  digitalWrite(relay2, LOW);
  digitalWrite(relay3, LOW);
  digitalWrite(relay4, LOW);
  delay(2000);
  digitalWrite(relay1, HIGH);
  digitalWrite(relay2, HIGH);
  digitalWrite(relay3, HIGH);
  digitalWrite(relay4, HIGH);
  delay(2000);
  digitalWrite(relay1, LOW);
  digitalWrite(relay2, LOW);
  digitalWrite(relay3, LOW);
  digitalWrite(relay4, LOW);
  delay(2000);
}

void loop() {
  count1++;
  count2++;
  count3++;
  count4++;
  if(count1%toggle1 == 0){
    state1 = toggle_value(state1);
    count1 = 0;
  }
  
  if(count2%toggle2 == 0){
    state2 = toggle_value(state2);
    count2 = 0;
  }
  
  if(count3%toggle3 == 0){
    state3 = toggle_value(state3);
    count3 = 0;
  }
  
  if(count4%toggle4 == 0){
    state4 = toggle_value(state4);
    count4 = 0;
  }
  
  digitalWrite(relay1, state1);
  digitalWrite(relay2, state2);
  digitalWrite(relay3, state3);
  digitalWrite(relay4, state4);
  statestatus = toggle_value(statestatus);
  digitalWrite(13, statestatus);
  
  delay(1000);
}

int toggle_value(int state){
  if(state == HIGH){
    return LOW;
  }else{
    return HIGH;
  }
}
