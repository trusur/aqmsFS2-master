String string_pm01 = "";
String string_pm02 = "";
int vacuum = 0;
float temp1 = 0.0;
float temp2 = 0.0;
float hum1 = 0.0;
float hum2 = 0.0;

void setup() {
    Serial.begin(9600);
    Serial.println("FS2_ANALYZER_BEGIN");
    delay(1000);
}

void loop() {
  string_pm01 =  "0" + String(float(random(10000,20000))/1000,3) + "|||" + String(float(random(18,22))/10,1);
  string_pm02 = "0" + String(float(random(30000,40000))/1000,3) + "|||" + String(float(random(18,22))/10,1);
  vacuum = random(10,20);
  temp1 = float(random(250,300))/10;
  hum1 = float(random(600,1000))/10;
  temp2 = float(random(250,300))/10;
  hum2 = float(random(600,1000))/10;
  // put your main code here, to run repeatedly:
  Serial.println("FS2_ANALYZER;" + string_pm01 + ";" + string_pm02 + ";" + vacuum + ";" + temp1 + ";" + hum1 + ";" + temp2 + ";" + hum2 + ";");
  delay(2000);
}
