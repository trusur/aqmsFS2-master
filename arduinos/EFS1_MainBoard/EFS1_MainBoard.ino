#include <ModbusMaster.h>
#include <Wire.h>
#include <Adafruit_INA219.h>
#include <DHT.h>
#include <SPI.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BME680.h>

#define Pump 7
#define SwitchPump 5
#define Offset 0
#define WindSensorPin 3
#define DHTPIN 2
#define DHTTYPE DHT22

Adafruit_INA219 ina219;
DHT dht(DHTPIN, DHTTYPE);
Adafruit_BME680 bme;
ModbusMaster node;

int currentPumpState, currentPumpSpeed, tempPumpSpeed, isStreamingAllData;
String command;
String currentPM1 = "";
String currentPM2 = "";

void setup() {
  Serial.begin(9600);
  Serial.setTimeout(500);
  ina219.begin();
  dht.begin();
  bme.begin(0x77);
  if (! bme.performReading()) {
    bme.begin(0x76);
  }
  if (! bme.performReading()) {
    Serial.println("BME Error!");
  }

  pinMode(Pump, OUTPUT);
  pinMode(SwitchPump, OUTPUT);
  pinMode(WindSensorPin, INPUT);


  bme.setTemperatureOversampling(BME680_OS_8X);
  bme.setHumidityOversampling(BME680_OS_2X);
  bme.setPressureOversampling(BME680_OS_4X);
  bme.setIIRFilterSize(BME680_FILTER_SIZE_3);
  
  // softStartPump(100,0);
  isStreamingAllData = 0;
  
}

void(* resetFunc) (void) = 0;

void loop() {
  if (Serial.available() > 0) {
    command = Serial.readStringUntil('#');
    // Serial.println("COMMAND : " + command);
    
    if(command.equals("data.streaming.all")){
      isStreamingAllData = 1;
    }
    
    if(command.equals("data.streaming.none")){
      isStreamingAllData = 0;
    }
    
    if(command.equals("data.getall")){
      showAllData();
    }
    
    if(command.equals("data.membrasens.ppm")){
      Serial.println("MEMBRASENS_PPM;" + getMEMBRASENS_PPM() + "END_MEMBRASENS_PPM");
    }
    
    if(command.equals("data.membrasens.temp")){
      Serial.println("MEMBRASENS_TEMP;" + getMEMBRASENS_TEMP() + "END_MEMBRASENS_TEMP");
    }
    
    if(command.equals("data.membrasens.zero")){
		Serial.println("MEMBRASENS_PPM;" + getMEMBRASENS_PPM() + "END_MEMBRASENS_PPM");
		setMEMBRASENS_ZERO();
		Serial.println("MEMBRASENS_ZERO;" + getMEMBRASENS_PPM() + "END_MEMBRASENS_ZERO");
    }
	
	if(command.substring(0,21).equals("data.membrasens.span.")){
	  int port = command.substring(21,command.indexOf(".",22)).toInt();
	  int span = command.substring(command.lastIndexOf(".") + 1,command.length()).toInt();
	  Serial.println("MEMBRASENS_PPM;" + getMEMBRASENS_PPM() + "END_MEMBRASENS_PPM");
	  setMEMBRASENS_SPAN(port,span);
	  Serial.println("MEMBRASENS_SPAN;" + getMEMBRASENS_PPM() + "END_MEMBRASENS_SPAN");
    }
    
    if(command.equals("data.pm.1")){
      Serial.println("PM1;" + getPM1() + ";END_PM1");
    }
    
    if(command.equals("data.pm.2")){
      Serial.println("PM2;" + getPM2() + ";END_PM2");
    }
    
    if(command.equals("data.ina219")){
      Serial.println("INA219;" + getINA219() + "END_INA219");
    }
    
    if(command.equals("data.dht")){
      Serial.println("DHT;" + getDHT() + "END_DHT");
    }
    
    if(command.equals("data.bme")){
      Serial.println("BME;" + getBME() + "END_BME");
    }
    
    if(command.equals("data.sentec")){
      Serial.println("SENTEC;" + getSENTEC() + "END_SENTEC");
    }
    
    if(command.equals("data.pressure")){
      Serial.println("PRESSURE;" + getPRESSURE() + "END_PRESSURE");
    }
    
    if(command.equals("data.pump")){
      Serial.println("PUMP;" + String(currentPumpSpeed) + ";"  + String(currentPumpState) + ";END_PUMP");
    }
    
    if(command.equals("pump.switch")){
      togglePump();
    }
      
    if(command.substring(0,11).equals("pump.speed.")){
      softStartPump(command.substring(11,command.length()).toInt(),currentPumpState);
    }
  
    if(command.substring(0,11).equals("pump.state.")){
	  tempPumpSpeed = currentPumpSpeed;
      softStartPump(0,currentPumpState);
	  delay(6000);
      softStartPump(tempPumpSpeed,command.substring(11,command.length()).toInt());
    }
  
    command="";
  }
  
  if(isStreamingAllData == 1){
    showAllData();
  }
}

void showAllData(){
  Serial.println("BEGIN");
  Serial.println("MEMBRASENS_PPM;" + getMEMBRASENS_PPM() + "END_MEMBRASENS_PPM");
  Serial.println("MEMBRASENS_TEMP;" + getMEMBRASENS_TEMP() + "END_MEMBRASENS_TEMP");
  Serial.println("PM1;" + getPM1() + ";END_PM1");
  Serial.println("PM2;" + getPM2() + ";END_PM2");
  Serial.println("INA219;" + getINA219() + "END_INA219");
  Serial.println("DHT;" + getDHT() + "END_DHT");
  Serial.println("BME;" + getBME() + "END_BME");
  Serial.println("SENTEC;" + getSENTEC() + "END_SENTEC");
  Serial.println("PRESSURE;" + getPRESSURE() + "END_PRESSURE");
  Serial.println("PUMP;" + String(currentPumpSpeed) + ";"  + String(currentPumpState) + ";END_PUMP");
  Serial.println("FINISH");
}

void softStartPump(int pumpspeed,int pumpstate){
  int i;
  currentPumpState = pumpstate;
  currentPumpSpeed = pumpspeed;
  analogWrite(Pump, 0);
  delay(50);
  digitalWrite(SwitchPump,currentPumpState);
  delay(50);
  for(i=30;i<=pumpspeed;i++){
    analogWrite(Pump, map(i, 0, 100, 0, 255));
    delay(50);
  }
}

void togglePump(){
  if(currentPumpState == 1){
    currentPumpState = 0;
  } else {
    currentPumpState = 1;
  }
  softStartPump(currentPumpSpeed,currentPumpState);
}

String getMEMBRASENS_PPM() {
  float board0[4];
  float board1[4];
  String str_return = "";
  Serial1.begin(19200, SERIAL_8E1);
  node.begin(1, Serial1);

  uint8_t i, result1;
  uint16_t data[8];

  union {
    uint32_t j;
    float f;
  } u;

  result1 = node.readHoldingRegisters(1000, 8);

  if (result1 == node.ku8MBSuccess) {
    for (i = 0; i < 8; i++) {
      data[i] = node.getResponseBuffer(i);
    }
    u.j = ((unsigned long)data[1] << 16 | data[0]); board0[0] = u.f;
    u.j = ((unsigned long)data[3] << 16 | data[2]); board0[1] = u.f;
    u.j = ((unsigned long)data[5] << 16 | data[4]); board0[2] = u.f;
    u.j = ((unsigned long)data[7] << 16 | data[6]); board0[3] = u.f;
  
    for(i=0; i<4; i++){    
      str_return = str_return + String(board0[i],6) + ";";
    }
    delay(10);
  }

  node.begin(2, Serial1);

  result1 = node.readHoldingRegisters(1000, 8);
  
  if (result1 == node.ku8MBSuccess) {
    for (i = 0; i < 8; i++) {
      data[i] = node.getResponseBuffer(i);
    }
    u.j = ((unsigned long)data[1] << 16 | data[0]); board1[0] = u.f;
    u.j = ((unsigned long)data[3] << 16 | data[2]); board1[1] = u.f;
    u.j = ((unsigned long)data[5] << 16 | data[4]); board1[2] = u.f;
    u.j = ((unsigned long)data[7] << 16 | data[6]); board1[3] = u.f;
  
    for(i=0; i<4; i++){    
      str_return = str_return + String(board1[i],6) + ";";
    }
    delay(10);
  }

  Serial1.end();
  
  return str_return;
}

String getMEMBRASENS_TEMP() {
  float board0[4];
  float board1[4];
  String str_return = "";
  Serial1.begin(19200, SERIAL_8E1);
  node.begin(1, Serial1);

  uint8_t i, result1;
  uint16_t data[8];

  union {
    uint32_t j;
    float f;
  } u;

  result1 = node.readHoldingRegisters(1070, 8);

  if (result1 == node.ku8MBSuccess) {
    for (i = 0; i < 8; i++) {
      data[i] = node.getResponseBuffer(i);
    }
    u.j = ((unsigned long)data[1] << 16 | data[0]); board0[0] = u.f;
    u.j = ((unsigned long)data[3] << 16 | data[2]); board0[1] = u.f;
    u.j = ((unsigned long)data[5] << 16 | data[4]); board0[2] = u.f;
    u.j = ((unsigned long)data[7] << 16 | data[6]); board0[3] = u.f;
  
    for(i=0; i<4; i++){    
      str_return = str_return + String(board0[i],6) + ";";
    }
    delay(10);
  }

  node.begin(2, Serial1);

  result1 = node.readHoldingRegisters(1070, 8);
  
  if (result1 == node.ku8MBSuccess) {
    for (i = 0; i < 8; i++) {
      data[i] = node.getResponseBuffer(i);
    }
    u.j = ((unsigned long)data[1] << 16 | data[0]); board1[0] = u.f;
    u.j = ((unsigned long)data[3] << 16 | data[2]); board1[1] = u.f;
    u.j = ((unsigned long)data[5] << 16 | data[4]); board1[2] = u.f;
    u.j = ((unsigned long)data[7] << 16 | data[6]); board1[3] = u.f;
  
    for(i=0; i<4; i++){    
      str_return = str_return + String(board1[i],6) + ";";
    }
    delay(10);
  }

  Serial1.end();
  
  return str_return;
}

void setMEMBRASENS_ZERO() {  
  Serial1.begin(19200, SERIAL_8E1);
  byte zeroing_bytes_01[] = {0x01, 0x10, 0x04, 0xB0, 0x00, 0x04, 0x08, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0xFA, 0xC0};
  byte zeroing_bytes_02[] = {0x01, 0x10, 0x04, 0xC4, 0x00, 0x04, 0x08, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x4A, 0x70};
  byte zeroing_bytes_11[] = {0x02, 0x10, 0x04, 0xB0, 0x00, 0x04, 0x08, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0xB9, 0xC1};
  byte zeroing_bytes_12[] = {0x02, 0x10, 0x04, 0xC4, 0x00, 0x04, 0x08, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x09, 0x71};
  Serial1.write(zeroing_bytes_01, sizeof(zeroing_bytes_01));
  delay(1000);
  Serial1.write(zeroing_bytes_02, sizeof(zeroing_bytes_02));
  delay(1000);
  Serial1.write(zeroing_bytes_11, sizeof(zeroing_bytes_11));
  delay(1000);
  Serial1.write(zeroing_bytes_12, sizeof(zeroing_bytes_12));
  delay(3000);
  Serial1.end();  
}

void setMEMBRASENS_SPAN(int port, int span) {
  if(span > 0 && span < 11){
    int spanAddress = 1230;
    int board;
    if(port < 4){
	  board = 0;
    } else {
	  port = port - 4;
	  board = 1;
    }
    
    byte port_bytes[] = {0xCE, 0xD0, 0xD2, 0XD4};
    byte board_bytes[] = {0x01, 0x02};
    byte span_bytes1[] = {0x00, 0x3F, 0x40, 0x40, 0x40, 0x40, 0x40, 0x40, 0x41, 0x41, 0x41};
    byte span_bytes2[] = {0x00, 0x80, 0x00, 0x40, 0x80, 0xA0, 0xC0, 0xE0, 0x00, 0x10, 0x20};
    
    spanAddress = spanAddress + (2*port);
    
    Serial1.begin(19200, SERIAL_8E1);
    
	byte span_start_bytes[] = {board_bytes[board], 0x10, 0x04, 0xB0, 0x00, 0x04, 0x08, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00};
	byte span_end_bytes[] = {board_bytes[board], 0x10, 0x04, 0xBA, 0x00, 0x04, 0x08, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00};
    byte span_bytes[] = {board_bytes[board], 0x10, 0x04, port_bytes[port], 0x00, 0x02, 0x04, 0x00, 0x00, span_bytes1[span], span_bytes2[span]};
	
	uint16_t crc = calcCRC(span_start_bytes, sizeof(span_start_bytes));
    Serial1.write(span_start_bytes, sizeof(span_start_bytes));
	Serial1.write(lowByte(crc));
    Serial1.write(highByte(crc));
    delay(1000);
	
	crc = calcCRC(span_bytes, sizeof(span_bytes));
    Serial1.write(span_bytes, sizeof(span_bytes));
	Serial1.write(lowByte(crc));
    Serial1.write(highByte(crc));
    delay(1000);
	
	crc = calcCRC(span_end_bytes, sizeof(span_end_bytes));
    Serial1.write(span_end_bytes, sizeof(span_end_bytes));
	Serial1.write(lowByte(crc));
    Serial1.write(highByte(crc));
    delay(1000);
    Serial1.end();	
  } else {
	Serial.println("MEMBRASENS_SPAN;ERROR;SPAN RANGE =>  1-10;END_MEMBRASENS_SPAN");
  }
}

uint16_t calcCRC(byte *data, byte panjang)
{
  int i;
  uint16_t crc = 0xFFFF;
  for (byte p = 0; p < panjang; p++)
  {
    crc ^= data[p];
    for (i = 0; i < 8; ++i)
    {
      if (crc & 1)
        crc = (crc >> 1) ^ 0xA001;
      else
        crc = (crc >> 1);
    }
  }
  return crc;
}

String getINA219() {
  float busVoltage,current,power;
  busVoltage = ina219.getBusVoltage_V();
  current = ina219.getCurrent_mA();
  power = busVoltage * (current / 1000);
  return String(busVoltage,2) + ";" + String(current,2) + ";" + String(power,2) + ";";
}

String getDHT() {
  float Humidity,Temperature;
  Humidity = dht.readHumidity();
  Temperature = dht.readTemperature();
  return String(Humidity,2) + ";" + String(Temperature,2) + ";";
}

String getBME() {
  String str_return = "";
  if (! bme.performReading()) {
    return "0.00;0.00;0.00;";
  }
  str_return = String(bme.temperature,2) + ";";
  str_return = str_return + String(bme.humidity,2) + ";";
  str_return = str_return + String((bme.pressure / 100.0),2) + ";";
  return str_return;
}

String getSENTEC() {
  uint8_t result1;
  String str_return = "";
  delay(50);
  Serial1.begin(4800, SERIAL_8N1);
  node.begin(3, Serial1);
  result1 =  node.readHoldingRegisters(0, 10);

  if (result1 == node.ku8MBSuccess) {
    str_return = node.getResponseBuffer(0);
    delay(10);
  }
  Serial1.end();
  return str_return + ";";
}

String getPRESSURE() {
  String str_return = "";
  str_return = (((analogRead(A2) * (7.63 / 1023.0)) / 5) - 0.04) / 0.009;
  delay(50);
  return String(str_return) + ";";
}

String getPM1(){
  String retval = "";
  Serial2.begin(9600);
  Serial2.setTimeout(100);
  if (Serial2.available() > 0) {
    delay(100);
    retval = Serial2.readStringUntil('\n');
    delay(100);
    if(retval.indexOf(",") > 0 && retval.indexOf("+") > 0 && retval.substring(0,4).equals("000.")){
      currentPM1 = retval;
    }
	currentPM1.replace("\n","");
    currentPM1.replace("\r","");

  }
  
  return currentPM1;
}

String getPM2(){
  String retval = "";
  Serial3.begin(9600);
  Serial3.setTimeout(100);
  if (Serial3.available() > 0) {
    delay(100);
    retval = Serial3.readStringUntil('\n');
    delay(100);
    if(retval.indexOf(",") > 0 && retval.indexOf("+") > 0 && retval.substring(0,4).equals("000.")){
      currentPM2 = retval;
    }
	currentPM2.replace("\n","");
    currentPM2.replace("\r","");

  }
  
  return currentPM2;
}
