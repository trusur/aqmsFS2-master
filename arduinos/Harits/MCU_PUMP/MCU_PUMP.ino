/*
 Name:		MCU_PUMP.ino
 Created:	4/25/2022 16:47:23
 Author:	harits
*/

/*SYSTEM CONFIGURATION
MCU: ARDUINO NANO EVERY ATmega 4809
Registers emulation: None (ATmega4809)
Baud rate: 115200
*/

#define device_ID F("MCU_PUMP")
#define software_version F("VER.1.0")
#define devMode_default false
boolean devMode = devMode_default;


#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BMP280.h>


/**********************************************************************************************************************
Command LIST
***********************************************************************************************************************

Command: $ID# return $MCU_PUMP,VER.1.0#

Command: $DEVMODE,ON# return $MCU_PUMP,DEVMODE,ON#
Command: $DEVMODE,OFF# return $MCU_PUMP,DEVMODE,OFF#

Command: $FAN,100# return $MCU_PUMP,FAN,100#
Command: $FAN,IN,200# return $MCU_PUMP,FAN,IN,200#
Command: $FAN,IN,STATUS# return $MCU_PUMP,FAN,IN,200#
Command: $FAN,OUT,10# return $MCU_PUMP,FAN,OUT,10#
Command: $FAN,OUT,STATUS# return $MCU_PUMP,FAN,OUT,70#


Command: $BMP280,BEGIN#
return:
Response: $MCU_PUMP,BMP280,BEGIN#
Response: $MCU_PUMP,BMP280,STATUS,SENSOR_FOUND# or $MCU_PUMP,BMP280,STATUS,SENSOR_NOT_FOUND#

Command: $BMP280,SET,AUTO# return $MCU_PUMP,BMP280,STATUS,AUTO,ON#
Command: $BMP280,SET,AUTO,5000# return $MCU_PUMP,BMP280,STATUS,AUTO,5000#
Command: $BMP280,SET,AUTO,ON# return $MCU_PUMP,BMP280,STATUS,AUTO,ON#
Command: $BMP280,SET,AUTO,OFF# return $MCU_PUMP,BMP280,STATUS,AUTO,OFF#

Command: $BMP280,READ# return $MCU_PSU,BMP280,VAL,23.32,98697.66#

Command: $BMP280,STATUS# return:
Response: $MCU_PUMP,BMP280,STATUS,AUTO,ON#
Response: $MCU_PUMP,BMP280,STATUS,AUTO,OFF#
Response: $MCU_PUMP,BMP280,STATUS,AUTO,5000#

//PUMP
$PUMP,1,SET,0#
$PUMP,1,SET,255#
$PUMP,1,SET,DIAGNOSIS_ON#
$PUMP,1,SET,DIAGNOSIS_OFF#
$PUMP,1,STATUS#
$PUMP,1,STATUS,DIAGNOSIS#

$PUMP,2,SET,0#
$PUMP,2,SET,255#
$PUMP,2,SET,DIAGNOSIS_ON#
$PUMP,2,SET,DIAGNOSIS_OFF#
$PUMP,2,STATUS#
$PUMP,2,STATUS,DIAGNOSIS#

$PUMP,SET,0#
$PUMP,SET,255#
$PUMP,SET,0#

$PUMP,STATUS#

$PUMP,SWITCH#


//PRESSURE SENSOR
Command: $PRESSURE,SET,AUTO# return $MCU_PUMP,PRESSURE,STATUS,AUTO,ON,500,1#
Command: $PRESSURE,SET,AUTO,1000# return $MCU_PUMP,PRESSURE,STATUS,AUTO,1000,1#
Command: $PRESSURE,SET,AUTO,ON# return $MCU_PUMP,PRESSURE,STATUS,AUTO,ON,500,1#
Command: $PRESSURE,SET,AUTO,OFF# return $MCU_PUMP,PRESSURE,STATUS,AUTO,OFF#

Command: $PRESSURE,READ# return $MCU_PUMP,PRESSURE,RAW,607,247#

Command: $PRESSURE,STATUS# return:
Response: $MCU_PUMP,PRESSURE,STATUS,AUTO,ON,10000,10#
Response: $MCU_PUMP,PRESSURE,STATUS,AUTO,OFF#






***********************************************************************************************************************/




//=====================================================================================================================
//DIGITAL PIN DECALRATION & TOOLS
//=====================================================================================================================
void ledBlink(byte type = 0) {
	switch (type)
	{
	case 0:
		digitalWrite(LED_BUILTIN, HIGH);
		delay(50);
		digitalWrite(LED_BUILTIN, LOW);
		break;
	case 1:
		digitalWrite(LED_BUILTIN, HIGH);
		delay(50);
		digitalWrite(LED_BUILTIN, LOW);
		delay(50);
		break;
	default:
		digitalWrite(LED_BUILTIN, HIGH);
		delay(50);
		digitalWrite(LED_BUILTIN, LOW);
		break;
	}
}

//=====================================================================================================================
//SERIAL COMMUNICATION
//=====================================================================================================================

boolean check_isDigit(String text, boolean sign = false) {
	char anz = 0;
	for (uint16_t i = 0; i < text.length(); i++) {
		anz = text.charAt(i);
		if (anz == ' ') return false;
		if (!sign) {
			if (isalpha(anz) || anz == '-') return false;
		}
		else if (sign) if (isalpha(anz)) return false;
	}
	return true;
}

void print_invalidcmd() {
	Serial.print('$' + (String)device_ID + ",GET_INVALID_COMMAND#");
}

//FLushing Serial Buffer
void flush_serialPort(HardwareSerial* SerialPort) {
	if (SerialPort->available()) {
		for (byte i = 0; i < SerialPort->available(); i++)
		{
			SerialPort->read();
			ledBlink(1);
		}
	}
}
//=====================================================================================================================
//PUMP PARAMETER & CONTROL
//=====================================================================================================================


#define PUMP_1_PWM_PIN 10
#define PUMP_1_DIR_PIN 11
#define PUMP_2_PWM_PIN 9
#define PUMP_2_DIR_PIN 8
#define PUMP_1 10
#define PUMP_2 9
#define PUMP_1_DIAG_PIN 2
#define PUMP_2_DIAG_PIN 3

int pump_1_speed = 0;
int pump_2_speed = 0;
byte pump_active = 0;

void pump_begin(boolean testPump = false) {

	Serial.print('$' + (String)device_ID + ",PUMP,BEGIN#");
	if (devMode) Serial.println();

	pinMode(PUMP_1_PWM_PIN, OUTPUT);
	pinMode(PUMP_1_DIR_PIN, OUTPUT);
	pinMode(PUMP_2_PWM_PIN, OUTPUT);
	pinMode(PUMP_2_DIR_PIN, OUTPUT);
	pinMode(PUMP_1_DIAG_PIN, INPUT);
	pinMode(PUMP_2_DIAG_PIN, INPUT);
	digitalWrite(PUMP_1_DIR_PIN, HIGH);
	digitalWrite(PUMP_2_DIR_PIN, HIGH);

	if (testPump) {
		for (int i = 0; i <= 255; i++)
		{
			analogWrite(PUMP_1, i);
			delay(3);
		}
		delay(3000);
		for (int i = 255; i >= 0; i--)
		{
			analogWrite(PUMP_1, i);
			delay(3);
		}
		delay(1500);
		for (int i = 0; i <= 255; i++)
		{
			analogWrite(PUMP_2, i);
			delay(3);
		}
		delay(3000);
		for (int i = 255; i >= 0; i--)
		{
			//analogWrite(PUMP_1, i);
			analogWrite(PUMP_2, i);
			delay(3);
		}
	}
}

int pump_speed(int pump, int pwm) {
	if (pump == PUMP_1) {
		if (pump_active == 2) { //turn off another active pump
			analogWrite(PUMP_2, 0);
			pump_2_speed = 0;
			Serial.print('$' + (String)device_ID + ",PUMP,STATUS,SWITCH_TO_PUMP_1#");
			delay(2000);
		}
		analogWrite(PUMP_1, pwm);
		pump_active = 1;
		pump_1_speed = pwm;
	}
	else if (pump == PUMP_2) {
		if (pump_active == 1) { //turn off another active pump
			analogWrite(PUMP_1, 0);
			pump_1_speed = 0;
			Serial.print('$' + (String)device_ID + ",PUMP,STATUS,SWITCH_TO_PUMP_2#");
			delay(2000);
		}
		analogWrite(PUMP_2, pwm);
		pump_active = 2;
		pump_2_speed = pwm;
	}
	if (pump_1_speed == 0 && pump_2_speed == 0) {
		pump_active = 0;
		Serial.print('$' + (String)device_ID + ",PUMP,STATUS,NO_ACTIVE_PUMP#");
	}
	return pwm;
}


//volatile boolean pump_1_fail_counter = 0;
//volatile boolean pump_2_fail_counter = 0;
boolean pump_1_active_diagnosis = true;
boolean pump_2_active_diagnosis = true;
uint32_t pump_active_diagnosis_tc = millis();
boolean pump_dignosis() {
	if (pump_1_active_diagnosis) {
		if (digitalRead(PUMP_1_DIAG_PIN)) {
			if (millis() - pump_active_diagnosis_tc > 500) {
				Serial.print('$' + (String)device_ID + ",PUMP,1,WARNING,FAILURE_DETECTED#");
				if (devMode) Serial.println();
				pump_active_diagnosis_tc = millis();
			}
			return 1;
		}
	}
	if (pump_2_active_diagnosis) {
		if (digitalRead(PUMP_2_DIAG_PIN)) {
			if (millis() - pump_active_diagnosis_tc > 500) {
				Serial.print('$' + (String)device_ID + ",PUMP,2,WARNING,FAILURE_DETECTED#");
				if (devMode) Serial.println();
				pump_active_diagnosis_tc = millis();
			}
			return 1;
		}
	}
	return 0;
}
//=====================================================================================================================
//PRESSURE SENSOR PARAMETER & CONTROL
//=====================================================================================================================
#define pressure_sensor_signal_pin A0
boolean pressure_status_auto = false;
const uint32_t pressure_sendInterval_default = 500;
const uint32_t pressure_readInterval_default = 1;
uint32_t pressure_sendInterval = pressure_sendInterval_default;
uint32_t pressure_readInterval = 500;
uint32_t pressure_send_tc = millis();
uint32_t pressure_read_tc = millis();
int pressure_raw = 0;
int32_t pressure_buffer = 0;
uint16_t pressure_dataCount = 0;

void pressure_setup() {
	pinMode(pressure_sensor_signal_pin, INPUT);
}

void pressure_read(boolean send = false) {
	Serial.print('$');
	Serial.print(device_ID);
	Serial.print(",PRESSURE,RAW,");
	Serial.print(analogRead(pressure_sensor_signal_pin));
	Serial.print(",1");
	Serial.print('#');
	if (devMode) Serial.println();
}

void pressure_sendData() {
	Serial.print('$');
	Serial.print(device_ID);
	Serial.print(",PRESSURE,RAW,");
	Serial.print(pressure_raw);
	Serial.print(',');
	Serial.print(pressure_dataCount);
	Serial.print('#');
	if (devMode) Serial.println();
}

void pressure_defineReadInterval() { //strecthing sampling rate
	if ((pressure_sendInterval / pressure_readInterval_default) > 1000) {
		pressure_readInterval = pressure_sendInterval / 1000;
	}
	else pressure_readInterval = pressure_readInterval_default;
}


void pressure_autoService(boolean enable) {
	if (enable) {
		if (millis() - pressure_read_tc > pressure_readInterval) {
			pressure_buffer += analogRead(pressure_sensor_signal_pin);
			pressure_dataCount++;
			pressure_read_tc = millis();
		}
		if (millis() - pressure_send_tc > pressure_sendInterval) {
			pressure_raw = pressure_buffer / pressure_dataCount;
			pressure_sendData();
			pressure_buffer = 0;
			pressure_dataCount = 0;
			pressure_send_tc = millis();
		}
	}
}


//=====================================================================================================================
//EXHAUST FAN PARAMETER & CONTROL
//=====================================================================================================================
#define fan_IN_PWM_pin 6
#define fan_IN_DIR 7
#define fan_OUT_PWM 5
#define fan_OUT_DIR 4
#define fan_IN 6
#define fan_OUT 5
int fan_IN_speed = 0;
int fan_OUT_speed = 0;

void fan_begin(boolean testFan = true) {
	pinMode(fan_IN_PWM_pin, OUTPUT);
	pinMode(fan_IN_DIR, OUTPUT);
	pinMode(fan_OUT_PWM, OUTPUT);
	pinMode(fan_OUT_DIR, OUTPUT);
	digitalWrite(fan_IN_DIR, HIGH);
	digitalWrite(fan_OUT_DIR, LOW);

	if (testFan) {
		for (int i = 0; i <= 255; i++)
		{
			analogWrite(fan_IN_PWM_pin, i);
			analogWrite(fan_OUT_PWM, i);
			delay(3);
		}
		delay(1000);
		for (int i = 255; i >= 0; i--)
		{
			analogWrite(fan_IN_PWM_pin, i);
			analogWrite(fan_OUT_PWM, i);
			delay(3);
		}
	}
}

int fan_speed(int fan, int pwm) {

	if (pwm < 80 && pwm > 0) pwm = 80;
	if (fan == fan_IN) {
		analogWrite(fan_IN, pwm);
		fan_IN_speed = pwm;
	}
	else if (fan == fan_OUT) {
		analogWrite(fan_OUT, pwm);
		fan_OUT_speed = pwm;
	}
	return pwm;
}

//=====================================================================================================================
//BMP280 PARAMETER & CONTROL
//=====================================================================================================================
/***************************************************************************
  This is a library for the BMP280 humidity, temperature & pressure sensor

  Designed specifically to work with the Adafruit BMP280 Breakout
  ----> http://www.adafruit.com/products/2651

  These sensors use I2C or SPI to communicate, 2 or 4 pins are required
  to interface.

  Adafruit invests time and resources providing this open source code,
  please support Adafruit andopen-source hardware by purchasing products
  from Adafruit!

  Written by Limor Fried & Kevin Townsend for Adafruit Industries.
  BSD license, all text above must be included in any redistribution
 ***************************************************************************/

 //#include <Wire.h>
 //#include <Adafruit_BMP280.h>

Adafruit_BMP280 bmp; // I2C

boolean BMP280_status_exist = true;
boolean BMP280_status_auto = false;
uint32_t BMP280_readInterval = 500;
const uint32_t BMP280_readInterval_default = 500;
uint32_t BMP280_tc = millis();
int BMP280_decimalPlace = 2;
float BMP280_temperature = 0.0F; //unit: °C
float BMP280_pressure = 0.0F; //unit: pascal

void BMP280_setup() {

	BMP280_status_exist = true;
	BMP280_status_auto = false;
	BMP280_readInterval = 1000;
	BMP280_tc = millis();
	BMP280_decimalPlace = 2;
	BMP280_temperature = 0.0F; //unit: °C
	BMP280_pressure = 0.0F; //unit: pascal
	Wire.begin();
	if (devMode) Serial.println(F("BMP280 Begin"));
	unsigned status;
	status = bmp.begin(BMP280_ADDRESS_ALT, BMP280_CHIPID);
	if (!status) {
		//if (devMode) Serial.println(F("Could not find a valid BMP280 sensor"));
		Serial.print('$' + (String)device_ID + ",BMP280,STATUS,SENSOR_NOT_FOUND#");
		if (devMode) Serial.println();
		BMP280_status_exist = false;
	}
	if (status) {
		//if (devMode) Serial.println(F("Found a valid BMP280 sensor"));
		Serial.print('$' + (String)device_ID + ",BMP280,STATUS,SENSOR_FOUND#");
		if (devMode) Serial.println();
		bmp.setSampling(Adafruit_BMP280::MODE_NORMAL,   /* Operating Mode. */
			Adafruit_BMP280::SAMPLING_X2,				/* Temp. oversampling */
			Adafruit_BMP280::SAMPLING_X16,				/* Pressure oversampling */
			Adafruit_BMP280::FILTER_X16,				/* Filtering. */
			Adafruit_BMP280::STANDBY_MS_500);			/* Standby time. */
	}
}

void BMP280_read(boolean send = false) {

	BMP280_temperature = bmp.readTemperature();
	BMP280_pressure = bmp.readPressure();

	if (BMP280_temperature == 0 && BMP280_pressure == 0) {
		BMP280_status_exist = false;
	}
	if (send) {
		if (BMP280_status_exist) BMP280_sendData();
		else if (!BMP280_status_exist) {
			Serial.print('$');
			Serial.print(device_ID);
			Serial.print(",BMP280,STATUS,NOT_FOUND#");
			if (devMode) Serial.println();
		}
	}
}

void BMP280_sendData() {
	Serial.print('$');
	Serial.print(device_ID);
	Serial.print(",BMP280,VAL,");
	Serial.print(BMP280_temperature, BMP280_decimalPlace);
	Serial.print(',');
	Serial.print(BMP280_pressure, BMP280_decimalPlace);
	Serial.print('#');
	if (devMode) Serial.println();
}


void BMP280_autoService(boolean enable) {
	if (enable) {
		if (millis() - BMP280_tc > BMP280_readInterval) {
			BMP280_read(true);
			BMP280_tc = millis();
		}
	}
}

//=====================================================================================================================
//COMMAND CONTROL (SERIAL COMMUNICATION)
//=====================================================================================================================
void serialEvent() {

	//=================================================================================================================
	//PROCESSING PACKET
	//=================================================================================================================
	if (Serial.read() == '$') {

		char* token[10] = { 0 };
		byte token_count = 0;
		char buff[SERIAL_RX_BUFFER_SIZE] = { 0 };
		byte index = 0;
		boolean valid = false;
		delay(20);
		uint32_t timeout = millis();
		while (millis() - timeout < 500) {
			//for (uint16_t i = 0; i < 500; i++) {
			if (Serial.available()) {
				if (index < SERIAL_RX_BUFFER_SIZE) {
					buff[index++] = Serial.read();
					if (buff[index - 1] == ',') token_count++;
					if (buff[index - 1] == '#') {
						token_count++;
						valid = true;
						break;
					}
				}
			}
			else break;
			//}
		}
		if (valid) {
			if (token_count > 1) {
				token[0] = strtok(buff, ",");
				for (byte i = 1; i < token_count; i++)
				{
					token[i] = strtok(NULL, ",#");
				}
			}
			else {
				token[0] = strtok(buff, ",#");
			}
			//=================================================================================================================
			//=================================================================================================================

			if ((String)token[0] == "ID" && token_count == 1) { //SEND MCU ID & SOFTWARE VERSION
				Serial.print('$' + (String)device_ID + ',' + (String)software_version + '#');
				ledBlink();
			}
			else if ((String)token[0] == "DEVMODE" && token_count == 2) { //SEND MCU ID & SOFTWARE VERSION
				if ((String)token[1] == "ON") {
					devMode = true;
					Serial.println('$' + (String)device_ID + ",DEVMODE,ON#");
					ledBlink();
				}
				else if ((String)token[1] == "OFF") {
					devMode = false;
					Serial.print('$' + (String)device_ID + ",DEVMODE,OFF#");
					ledBlink();
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//PELTIER COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "FAN") { //FAN CONTROL
				if (token_count <= 2) {
					if (token[1] != NULL) {
						if (check_isDigit((String)token[1])) {
							int fan_pwm = atoi(token[1]);
							String check = (String)token[1];
							if (!(check.charAt(0) == '0' && check.length() > 1)) {
								if (fan_pwm >= 0 && fan_pwm <= 255) {
									ledBlink();
									if (fan_pwm > 0 && fan_pwm < 80) fan_pwm = 80;
									fan_speed(fan_IN, fan_pwm);
									fan_speed(fan_OUT, fan_pwm);
									Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)fan_pwm + '#');
								}
								else print_invalidcmd();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else print_invalidcmd();
				}
				else if (token_count > 2) {
					if ((String)token[1] == "IN") {
						if (token[2] != NULL) {
							if (check_isDigit((String)token[2])) {
								int fan_pwm = atoi(token[2]);
								String check = (String)token[2];
								if (!(check.charAt(0) == '0' && check.length() > 1)) {
									if (fan_pwm >= 0 && fan_pwm <= 255) {
										if (fan_pwm > 0 && fan_pwm < 70) fan_pwm = 70;
										fan_speed(fan_IN, fan_pwm);
										Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ',' + (String)fan_pwm + '#');
										ledBlink();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else if ((String)token[2] == "STATUS") {
								Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ',' + (String)fan_IN_speed + '#');
								ledBlink();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else if ((String)token[1] == "OUT") {
						if (token[2] != NULL) {
							if (check_isDigit((String)token[2])) {
								int fan_pwm = atoi(token[2]);
								String check = (String)token[2];
								if (!(check.charAt(0) == '0' && check.length() > 1)) {
									if (fan_pwm >= 0 && fan_pwm <= 255) {
										if (fan_pwm > 0 && fan_pwm < 80) fan_pwm = 80;
										fan_speed(fan_OUT, fan_pwm);
										Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ',' + (String)fan_pwm + '#');
										ledBlink();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else if ((String)token[2] == "STATUS") {
								Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ',' + (String)fan_OUT_speed + '#');
								ledBlink();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else print_invalidcmd();
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//PUMP COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "PUMP") { //PUMP COMMAND AND CONTROL

				if ((String)token[1] == "1") {
					if ((String)token[2] == "SET") {
						if (token[3] != NULL && token_count == 4) {
							if (check_isDigit((String)token[3])) {
								int pump_pwm = atoi(token[3]);
								String check = (String)token[3];
								if (!(check.charAt(0) == '0' && check.length() > 1)) {
									if (pump_pwm >= 0 && pump_pwm <= 255) {
										if (pump_pwm < 100) {
											if (pump_pwm == 0) {
												pump_speed(PUMP_1, pump_pwm);
												Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ",SPEED," + (String)pump_pwm + '#');
												ledBlink();
											}
											else Serial.print('$' + (String)device_ID + ",PUMP,1,WARNING,PUMP_SPEED_TOO_LOW#");
											ledBlink();
										}
										if (pump_pwm >= 100 && pump_pwm < 180) {
											pump_speed(PUMP_1, 150);
											delay(100);
											pump_speed(PUMP_1, 255);
											delay(100);
											for (byte i = 100; i <= pump_pwm; i++) {
												pump_speed(PUMP_1, i);
												delay(30);
											}
											Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ",SPEED," + (String)pump_pwm + '#');
											ledBlink();
										}
										if (pump_pwm >= 180) {
											for (int i = 100; i <= pump_pwm; i++) {
												pump_speed(PUMP_1, i);
												delay(30);
											}
											//if (pump_pwm == 255) pump_speed(PUMP_1, 255);
											Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ",SPEED," + (String)pump_pwm + '#');
											ledBlink();
										}
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else if ((String)token[3] == "DIAGNOSIS_ON" && token_count == 4) {
								pump_1_active_diagnosis = true;
								Serial.print('$' + (String)device_ID + ",PUMP,1,DIAGNOSIS_ON#");
								ledBlink();
							}
							else if ((String)token[3] == "DIAGNOSIS_OFF" && token_count == 4) {
								pump_1_active_diagnosis = false;
								Serial.print('$' + (String)device_ID + ",PUMP,1,DIAGNOSIS_OFF#");
								ledBlink();
							}
							else print_invalidcmd();
						}

						else print_invalidcmd();
					}

					else if ((String)token[2] == "STATUS" && token_count == 3) {
						Serial.print('$' + (String)device_ID + ",PUMP,1,SPEED," + (String)pump_1_speed + '#');
						ledBlink();
					}
					else if ((String)token[2] == "STATUS" && token_count == 4) {
						if ((String)token[3] == "DIAGNOSIS") {
							if (pump_1_active_diagnosis)	Serial.print('$' + (String)device_ID + ",PUMP,1,DIAGNOSIS_ON#");
							else if (!pump_1_active_diagnosis)	Serial.print('$' + (String)device_ID + ",PUMP,1,DIAGNOSIS_OFF#");
						}
						else print_invalidcmd();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "2") {
					if ((String)token[2] == "SET") {
						if (token[3] != NULL && token_count == 4) {
							if (check_isDigit((String)token[3])) {
								int pump_pwm = atoi(token[3]);
								String check = (String)token[3];
								if (!(check.charAt(0) == '0' && check.length() > 1)) {
									if (pump_pwm >= 0 && pump_pwm <= 255) {
										if (pump_pwm < 100) {
											if (pump_pwm == 0) {
												pump_speed(PUMP_2, pump_pwm);
												Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ",SPEED," + (String)pump_pwm + '#');
											}
											else Serial.print('$' + (String)device_ID + ",PUMP,2,WARNING,PUMP_SPEED_TOO_LOW#");
											ledBlink();
										}
										if (pump_pwm >= 100 && pump_pwm < 180) {
											pump_speed(PUMP_2, 150);
											delay(100);
											pump_speed(PUMP_2, 255);
											delay(100);
											for (byte i = 100; i <= pump_pwm; i++) {
												pump_speed(PUMP_2, i);
												delay(30);
											}
											Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ",SPEED," + (String)pump_pwm + '#');
											ledBlink();
										}
										if (pump_pwm >= 180) {
											for (int i = 100; i <= pump_pwm; i++) {
												pump_speed(PUMP_2, i);
												delay(30);
											}
											//if (pump_pwm == 255) pump_speed(PUMP_2, 255);
											Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + ",SPEED," + (String)pump_pwm + '#');
											ledBlink();
										}
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else if ((String)token[3] == "DIAGNOSIS_ON" && token_count == 4) {
								pump_2_active_diagnosis = true;
								Serial.print('$' + (String)device_ID + ",PUMP,2,DIAGNOSIS_ON#");
								ledBlink();
							}
							else if ((String)token[3] == "DIAGNOSIS_OFF" && token_count == 4) {
								pump_2_active_diagnosis = false;
								Serial.print('$' + (String)device_ID + ",PUMP,2,DIAGNOSIS_OFF#");
								ledBlink();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else if ((String)token[2] == "STATUS" && token_count == 3) {
						Serial.print('$' + (String)device_ID + ",PUMP,2,SPEED," + (String)pump_2_speed + '#');
						ledBlink();
					}
					else if ((String)token[2] == "STATUS" && token_count == 4) {
						if ((String)token[3] == "DIAGNOSIS") {
							if (pump_2_active_diagnosis)	Serial.print('$' + (String)device_ID + ",PUMP,2,DIAGNOSIS_ON#");
							else if (!pump_2_active_diagnosis)	Serial.print('$' + (String)device_ID + ",PUMP,2,DIAGNOSIS_OFF#");
						}
						else print_invalidcmd();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "SET") {
					if (token[2] != NULL && token_count == 3) {
						int pump_num = 0;
						if (pump_active == 1) pump_num = PUMP_1;
						if (pump_active == 2) pump_num = PUMP_2;
						if (pump_num != 0) {
							if (check_isDigit((String)token[2])) {
								int pump_pwm = atoi(token[2]);
								String check = (String)token[2];
								if (!(check.charAt(0) == '0' && check.length() > 1)) {
									if (pump_pwm >= 0 && pump_pwm <= 255) {
										if (pump_pwm < 100) {
											if (pump_pwm == 0) {
												pump_speed(pump_num, pump_pwm);
												//Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)pump_active + ",SPEED," + (String)pump_pwm + '#');
												ledBlink();
											}
											else Serial.print('$' + (String)device_ID + ",PUMP,WARNING,PUMP_SPEED_TOO_LOW#");
										}
										if (pump_pwm >= 100 && pump_pwm < 180) {
											pump_speed(pump_num, 150);
											delay(100);
											pump_speed(pump_num, 255);
											delay(100);
											for (byte i = 100; i <= pump_pwm; i++) {
												pump_speed(pump_num, i);
												delay(30);
											}
											Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)pump_active + ",SPEED," + (String)pump_pwm + '#');
											ledBlink();
										}
										if (pump_pwm >= 180) {
											for (byte i = 100; i < pump_pwm; i++) {
												pump_speed(pump_num, i);
												delay(30);
											}
											//if (pump_pwm == 255) pump_speed(PUMP_2, 255);
											Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)pump_active + ",SPEED," + (String)pump_pwm + '#');
											ledBlink();
										}
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else print_invalidcmd();
						}
						else Serial.print('$' + (String)device_ID + ",PUMP,STATUS,NO_ACTIVE_PUMP#");
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "SWITCH" && token_count == 2) {
					int pump_pwm = 0;
					if (pump_active == 1) {
						//pump_num = PUMP_1;
						pump_pwm = pump_1_speed;
					}
					if (pump_active == 2) {
						//pump_num = PUMP_2;
						pump_pwm = pump_2_speed;
					}
					switch (pump_active)
					{
					case 0:
						Serial.print('$' + (String)device_ID + ",PUMP,STATUS,NO_ACTIVE_PUMP#");
						break;
					case 1:
						if (pump_pwm >= 100 && pump_pwm < 180) {
							pump_speed(PUMP_2, 150);
							delay(100);
							pump_speed(PUMP_2, 255);
							delay(100);
							for (byte i = 100; i <= pump_pwm; i++) {
								pump_speed(PUMP_2, i);
								delay(30);
							}
						}
						if (pump_pwm >= 180) {
							for (int i = 100; i <= pump_pwm; i++) {
								pump_speed(PUMP_2, i);
								delay(30);
							}
							if (pump_pwm == 255) pump_speed(PUMP_2, 255);

						}
						Serial.print('$' + (String)device_ID + ",PUMP,2,SPEED," + (String)pump_2_speed + '#');
						ledBlink();
						break;
					case 2:
						if (pump_pwm >= 100 && pump_pwm < 180) {
							pump_speed(PUMP_1, 150);
							delay(100);
							pump_speed(PUMP_1, 255);
							delay(100);
							for (byte i = 100; i <= pump_pwm; i++) {
								pump_speed(PUMP_1, i);
								delay(30);
							}
						}
						if (pump_pwm >= 180) {
							for (int i = 100; i <= pump_pwm; i++) {
								pump_speed(PUMP_1, i);
								delay(30);
							}
						}
						Serial.print('$' + (String)device_ID + ",PUMP,1,SPEED," + (String)pump_1_speed + '#');
						ledBlink();
						break;
					default:
						print_invalidcmd();
						break;
					}
				}
				else if ((String)token[1] == "STATUS" && token_count == 2) {
					switch (pump_active)
					{
					case 0:
						Serial.print('$' + (String)device_ID + ",PUMP,STATUS,NO_ACTIVE_PUMP#");
						ledBlink();
						break;
					case 1:
						Serial.print('$' + (String)device_ID + ",PUMP,STATUS,PUMP_1_ACTIVE,SPEED," + (String)pump_1_speed + '#');
						break;
					case 2:
						Serial.print('$' + (String)device_ID + ",PUMP,STATUS,PUMP_2_ACTIVE,SPEED," + (String)pump_2_speed + '#');
						ledBlink();
						break;
					default:
						print_invalidcmd();
						ledBlink();
						break;
					}
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//PRESSURE COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "PRESSURE") { //PRESSURE SENSOR CONTROL
				if ((String)token[1] == "SET") {
					if (token_count <= 3) {
						if ((String)token[2] == "AUTO") {
							pressure_status_auto = true;
							pressure_sendInterval = pressure_sendInterval_default;
							pressure_defineReadInterval();
							Serial.print('$' + (String)device_ID + ",PRESSURE,STATUS,AUTO,ON," + (String)pressure_sendInterval + ',' + (String)pressure_readInterval + '#');
							ledBlink();
						}
					}
					else if (token_count == 4) {
						if ((String)token[2] == "AUTO") {
							if ((String)token[3] == "ON") {
								pressure_status_auto = true;
								pressure_sendInterval = pressure_sendInterval_default;
								pressure_defineReadInterval();
								//Serial.print('$' + (String)device_ID + ",PRESSURE,STATUS,AUTO,ON#");
								Serial.print('$' + (String)device_ID + ",PRESSURE,STATUS,AUTO,ON," + (String)pressure_sendInterval + ',' + (String)pressure_readInterval + '#');
								ledBlink();
							}
							else if ((String)token[3] == "OFF") {
								pressure_status_auto = false;
								Serial.print('$' + (String)device_ID + ",PRESSURE,STATUS,AUTO,OFF#");
								ledBlink();
							}
							else if (check_isDigit((String)token[3]) && token[3] != NULL) {
								long send_interval = atol(token[3]);
								if (send_interval >= 50 && send_interval <= 1000000000L) {
									String check = (String)token[3];
									if (check.charAt(0) != '0' && check.length() > 1) {
										pressure_sendInterval = send_interval;
										pressure_defineReadInterval();
										pressure_status_auto = true;
										Serial.print('$' + (String)device_ID + ",PRESSURE,STATUS,AUTO,ON," + (String)pressure_sendInterval + ',' + (String)pressure_readInterval + '#');
										ledBlink();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "READ") {
					if (token_count <= 2) {
						pressure_read(true);
						pressure_status_auto = false;
						ledBlink();
					}
					else print_invalidcmd();
				}
				else if ((String)token[1] == "STATUS") {
					if (pressure_status_auto) {
						Serial.print('$' + (String)device_ID + ",PRESSURE,STATUS,AUTO,ON," + (String)pressure_sendInterval + ',' + (String)pressure_readInterval + '#');
						ledBlink();
					}
					else if (!pressure_status_auto) {
						Serial.print('$' + (String)device_ID + ",PRESSURE,STATUS,AUTO,OFF#");
						ledBlink();
					}
					if (devMode) Serial.println();
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//BMP280 COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "BMP280") { //BMP280 CONTROL
				if (BMP280_status_exist) {
					if ((String)token[1] == "BEGIN") {
						Serial.print('$' + (String)device_ID + ",BMP280,BEGIN#");
						BMP280_setup();
						ledBlink();
					}
					else if ((String)token[1] == "SET") {
						if (token_count <= 3) {
							if ((String)token[2] == "AUTO") {
								BMP280_status_auto = true;
								BMP280_readInterval = BMP280_readInterval_default;
								Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO,ON#");
								ledBlink();
							}
						}
						else if (token_count == 4) {
							if ((String)token[2] == "AUTO") {
								if ((String)token[3] == "ON") {
									BMP280_status_auto = true;
									BMP280_readInterval = BMP280_readInterval_default;
									Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO,ON#");
									ledBlink();
								}
								else if ((String)token[3] == "OFF") {
									BMP280_status_auto = false;
									Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO,OFF#");
									ledBlink();
								}
								else if (check_isDigit((String)token[3]) && token[3] != NULL) {
									long read_interval = atol(token[3]);
									if (read_interval <= 1000000000L) {
										String check = (String)token[3];
										if (!(check.charAt(0) == '0' && check.length() > 1)) {
											if (read_interval < 1000) {
												BMP280_readInterval = BMP280_readInterval_default;
												BMP280_status_auto = true;
												Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO," + (String)BMP280_readInterval + '#');
												ledBlink();
											}
											else {
												BMP280_readInterval = read_interval;
												BMP280_status_auto = true;
												Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO," + (String)BMP280_readInterval + '#');
												ledBlink();
											}
										}
										else print_invalidcmd();
									}
									else print_invalidcmd();
								}
								else print_invalidcmd();
							}
							else print_invalidcmd();
						}
						else print_invalidcmd();
					}
					else if ((String)token[1] == "READ") {
						if (token_count <= 2) {
							BMP280_read(true);
							BMP280_status_auto = false;
							ledBlink();
						}
						else print_invalidcmd();
					}
					else if ((String)token[1] == "STATUS") {
						if (BMP280_status_auto) {
							if (BMP280_readInterval == BMP280_readInterval_default) {
								Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO,ON#");
								ledBlink();
							}
							else if (BMP280_readInterval != BMP280_readInterval_default) {
								Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO," + (String)BMP280_readInterval + '#');
								ledBlink();
							}
						}
						else if (!BMP280_status_auto) {
							Serial.print('$' + (String)device_ID + ",BMP280,STATUS,AUTO,OFF#");
							ledBlink();
						}
						if (devMode) Serial.println();
					}
					else print_invalidcmd();
				}
				else Serial.print('$' + (String)device_ID + ",BMP280,STATUS,SENSOR_NOT_FOUND#");
			}
			else print_invalidcmd();
		}
		else print_invalidcmd();
	}
}
//=====================================================================================================================
//=====================================================================================================================

void setup() {
	Serial.begin(115200);
	delay(500);


	pinMode(LED_BUILTIN, OUTPUT);

	Serial.print('$' + (String)device_ID + ",SETUP#");
	if (devMode) Serial.println();

	ledBlink(1);
	ledBlink(1);
	ledBlink(1);

	pump_begin();
	fan_begin();
	BMP280_setup();
	pressure_setup();
}

boolean MCU_READY = false;

void loop() {
	if (!MCU_READY) {
		MCU_READY = true;
		Serial.print('$' + (String)device_ID + ',' + (String)software_version + '#');
		if (devMode) Serial.println();
		Serial.print('$' + (String)device_ID + ",READY#");
		if (devMode) Serial.println();
	}

	serialEvent();
	pump_dignosis();
	BMP280_autoService(BMP280_status_auto);
	pressure_autoService(pressure_status_auto);
}

