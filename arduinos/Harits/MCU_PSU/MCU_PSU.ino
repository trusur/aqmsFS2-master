/*
 Name:		MCU_PSU.ino
 Created:	4/18/2022 19:14:58
 Author:	harits
*/

/*SYSTEM CONFIGURATION
MCU: ARDUINO NANO EVERY ATmega 4809
Registers emulation: None (ATmega4809)
Baud rate: 115200
*/

#define device_ID F("MCU_PSU")
#define software_version F("VER.1.0")
#define devMode_default false
boolean devMode = devMode_default;

#include <SoftwareSerial.h>
#include <Wire.h>
#include <Adafruit_Sensor.h>
#include <Adafruit_BMP280.h>

//void(*resetFunc) (void) = 0;

/**********************************************************************************************************************
Command LIST
***********************************************************************************************************************

Command: $ID# return $MCU_PSU,VER.1.0#

Command: $DEVMODE,ON# return $MCU_PSU,DEVMODE,ON#
Command: $DEVMODE,OFF# return $MCU_PSU,DEVMODE,OFF#

Command: $TURN_OFF#
Command: $MCU_PSU,TURN_ON# return $MCU_PSU,TURN_ON#
Command: $RESTART# return $MCU_PSU,RESTART#
Command: $RESTART,3000# return $MCU_PSU,RESTART,3000#

Command: $PELTIER,OFF# return $MCU_PSU,PELTIER,OFF#
Command: $PELTIER,ON# return $MCU_PSU,PELTIER,ON#
Command: $PELTIER,STATUS# return $MCU_PSU,PELTIER,OFF#

Command: $FAN,100# return $MCU_PSU,FAN,100#
Command: $FAN,IN,200# return $MCU_PSU,FAN,IN,200#
Command: $FAN,IN,STATUS# return $MCU_PSU,FAN,IN,200#
Command: $FAN,OUT,10# return $MCU_PSU,FAN,OUT,10#
Command: $FAN,OUT,STATUS# return $MCU_PSU,FAN,OUT,70#


Command: $BMP280,BEGIN#
return:
Response: $MCU_PSU,BMP280,BEGIN#
Response: $MCU_PSU,BMP280,STATUS,SENSOR_FOUND# or $MCU_PSU,BMP280,STATUS,SENSOR_NOT_FOUND#

Command: $BMP280,SET,AUTO# return $MCU_PSU,BMP280,STATUS,AUTO,ON#
Command: $BMP280,SET,AUTO,5000# return $MCU_PSU,BMP280,STATUS,AUTO,5000#
Command: $BMP280,SET,AUTO,ON# return $MCU_PSU,BMP280,STATUS,AUTO,ON#
Command: $BMP280,SET,AUTO,OFF# return $MCU_PSU,BMP280,STATUS,AUTO,OFF#

Command: $BMP280,READ# return $MCU_PSU,BMP280,VAL,23.32,98697.66#

Command: $BMP280,STATUS# return:
Response: $MCU_PSU,BMP280,STATUS,AUTO,ON#
Response: $MCU_PSU,BMP280,STATUS,AUTO,OFF#
Response: $MCU_PSU,BMP280,STATUS,AUTO,5000#

***********************************************************************************************************************/



//=====================================================================================================================
//DIGITAL PIN DECALRATION & TOOLS
//=====================================================================================================================

#define peltier_relayTrigger_pin 12
volatile boolean peltier_status = false;

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

//#include <SoftwareSerial.h>
SoftwareSerial mySerial(2, 3); // RX, TX

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
		//analogWrite(fan_IN_PWM, 0);
		//analogWrite(fan_OUT_PWM, 0);
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
boolean BMP280_status_auto = true;
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
		delay(10);
		uint32_t timeout = millis();
		while (millis() - timeout < 500) {
			//for (uint16_t i = 0; i < 500; i++) {
			if (Serial.available()) {
				if (index < SERIAL_RX_BUFFER_SIZE) {
					buff[index++] = Serial.read();
					if (buff[index - 1] == ',') token_count++;
					if (buff[index - 1] == '#') {
						valid = true;
						token_count++;
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
			//UNIT POWER COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "RESTART") { //RESTARTING UNIT
				if (token_count == 1) {
					uint32_t halt = 3000;
					byte retry = 5;
					for (byte i = 1; i <= retry; i++)
					{
						mySerial.print('$' + (String)(token[0]) + ',' + (String)halt + '#');
						Serial.print('$' + (String)(token[0]) + ',' + (String)halt + '#');
						delay(100);
						boolean check_response = mySerial.findUntil("RESTART", "#");
						if (check_response) {
							Serial.print('$' + (String)device_ID + ',' + String(token[0]) + ',' + (String)halt + '#');
							ledBlink();
							break;
						}
						if (i >= retry) {
							Serial.print('$' + (String)device_ID + ",RESTART,NO_RESPONSE#");
							mySerial.end();
							delay(1000);
							mySerial.begin(9600);
						}
					}
				}
				else if (token_count == 2) {
					if (check_isDigit((String)token[1])) {
						int halt = atoi(token[1]);
						if (halt < 0) Serial.print('$' + (String)device_ID + ",GET_INVALID_COMMAND#");
						if (halt >= 0) {
							if (halt < 3000) halt = 3000;
							byte retry = 5;
							for (byte i = 1; i <= retry; i++)
							{
								mySerial.print('$' + (String)token[0] + ',' + (String)halt + '#');
								Serial.print('$' + (String)token[0] + ',' + (String)halt + '#');
								delay(100);
								boolean check_response = mySerial.findUntil("RESTART", "#");
								if (check_response) {
									Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)halt + '#');
									ledBlink();
									break;
								}
								if (i >= retry) {
									Serial.print('$' + (String)device_ID + ",RESTART,NO_RESPONSE#");
									mySerial.end();
									delay(1000);
									mySerial.begin(9600);
								}
							}
						}
					}
					else if (!check_isDigit((String)token[1])) print_invalidcmd();
				}
				else print_invalidcmd();
			}
			else if ((String)token[0] == "TURN_OFF") { //TURN OFF UNIT

				if (token_count == 1) {
					byte retry = 5;
					for (byte i = 1; i <= retry; i++)
					{
						mySerial.print("$OFF#");
						delay(100);

						boolean check_response = mySerial.findUntil("MCU_PWRRST", "#");
						if (check_response) {
							Serial.print('$' + (String)device_ID + ",TURN_OFF#");
							ledBlink();
							break;
						}
						if (i >= retry) {
							Serial.print('$' + (String)device_ID + ",RESTART,NO_RESPONSE#");
							mySerial.end();
							delay(1000);
							mySerial.begin(9600);
						}

					}
				}
				else print_invalidcmd();
			}
			else if ((String)token[0] == "TURN_ON") { //TURN ON UNIT

				if (token_count == 1) {
					byte retry = 5;
					for (byte i = 1; i <= retry; i++)
					{
						mySerial.print("$ON#");
						delay(100);

						boolean check_response = mySerial.findUntil("MCU_PWRRST", "#");
						if (check_response) {
							Serial.print('$' + (String)device_ID + ",TURN_ON#");
							ledBlink();
							break;
						}
						if (i >= retry) {
							Serial.print('$' + (String)device_ID + ",RESTART,NO_RESPONSE#");
						}

					}
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//PELTIER COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "PELTIER") { //PELTIER CONTROL
				if (token_count == 2) {
					if ((String)token[1] == "ON") {
						digitalWrite(peltier_relayTrigger_pin, HIGH);
						peltier_status = true;
						Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + '#');
						ledBlink();
					}
					else if ((String)token[1] == "OFF") {
						digitalWrite(peltier_relayTrigger_pin, LOW);
						peltier_status = false;
						Serial.print('$' + (String)device_ID + ',' + (String)token[0] + ',' + (String)token[1] + '#');
						ledBlink();
					}
					else if ((String)token[1] == "STATUS") {
						if (peltier_status) Serial.print('$' + (String)device_ID + ',' + "PELTIER,ON" + '#');
						else if (!peltier_status) Serial.print('$' + (String)device_ID + ',' + "PELTIER,OFF" + '#');
						ledBlink();
					}
					else print_invalidcmd();
				}
				else print_invalidcmd();
			}
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			//FAN COMMANDS
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
			//BMP280 COMMANDS
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else if ((String)token[0] == "BMP280") { //BME280 CONTROL
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
							else if (!(BMP280_readInterval == BMP280_readInterval_default)) {
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
			//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			else print_invalidcmd();
		}
		else print_invalidcmd();

	}
}
//=====================================================================================================================
//=====================================================================================================================

void setup() {
	Serial.begin(115200);
	mySerial.begin(9600);
	delay(500);

	pinMode(peltier_relayTrigger_pin, OUTPUT);
	pinMode(LED_BUILTIN, OUTPUT);

	Serial.print('$' + (String)device_ID + ",SETUP#");
	if (devMode) Serial.println();


	ledBlink(1);
	ledBlink(1);
	ledBlink(1);

	fan_begin();
	BMP280_setup();

	Serial.print('$' + (String)device_ID + ',' + (String)software_version + '#');
	if (devMode) Serial.println();
	Serial.print('$' + (String)device_ID + ",READY#");
	if (devMode) Serial.println();
	ledBlink();
}

void loop() {

	serialEvent();

	BMP280_autoService(BMP280_status_auto);
}

