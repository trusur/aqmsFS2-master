/*
 Name:		power_reset.ino
 Created:	4/18/2022 16:25:32
 Author:	harits
*/

/*SYSTEM CONFIGURATION
MCU: ARDUINO PRO MINI ATmega 328P
Processor: ATmega328P (5V, 16MHz)
Baud rate: 9600 
*/

#define device_ID F("MCU_PWRRST")
#define software_version F("VER.1.0")

#define relayTrigger_pin 12
#define buidInLed_pin 13


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

//FLushing Serial Buffer
void flush_serialPort(HardwareSerial* SerialPort) {
	if (SerialPort->available()) {
		char read;
		for (byte i = 0; i < SerialPort->available(); i++)
		{
			read = SerialPort->read();
			ledBlink(1);
		}
	}
}

void(*resetFunc) (void) = 0;

void serialEvent() {
	if (Serial.read() == '$') {
		char* token[10] = { 0 };
		byte token_count = 0;
		char buff[SERIAL_RX_BUFFER_SIZE] = { 0 };
		byte index = 0;
		delay(100);
		for (uint16_t i = 0; i < 500; i++) {
			if (Serial.available()) {
				if (index < SERIAL_RX_BUFFER_SIZE) {
					buff[index++] = Serial.read();
					if (buff[index - 1] == ',') token_count++;
					if (buff[index - 1] == '#') {
						token_count++;
						break;
					}
				}
			}
			else break;
		}

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

		if ((String)token[0] == "ID") { //SEND DEVICE ID AND SOFTWARE VERSION
			Serial.print('$' + (String)device_ID + ',' + (String)software_version + '#');
			ledBlink(1);
		}

		else if ((String)token[0] == "RESTART") { //RESTARTING UNIT
			uint32_t halt = 0;
			halt = atoi(token[1]);
			if (halt < 3000) halt = 3000;
			digitalWrite(relayTrigger_pin, HIGH);
			digitalWrite(LED_BUILTIN, HIGH);
			
			//resetFunc();

			Serial.end();
			////Serial.print('$' + String(token[0]) + ',' + (String)token[1] + '#');
			delay(halt);
			//Serial.begin(9600);
			digitalWrite(LED_BUILTIN, LOW);
			digitalWrite(relayTrigger_pin, LOW);
			////delay(5000);


			//ledBlink(1);
			//ledBlink(1);

			resetFunc();
			//Serial.print('$' + String(token[0]) + ',' + (String)token[1] + '#');
		}

		else if ((String)token[0] == "OFF") { //TURN OFF UNIT
			//Serial.print('$' + String(device_ID) + ",OFF#");
			digitalWrite(relayTrigger_pin, HIGH);
			digitalWrite(LED_BUILTIN, HIGH);
			ledBlink(1);
			resetFunc();
		}
		else if ((String)token[0] == "ON") { //TURN ON UNIT
			//Serial.print('$' + String(device_ID) + ",ON#");
			digitalWrite(relayTrigger_pin, LOW);
			digitalWrite(LED_BUILTIN, LOW);
			ledBlink(1);
			resetFunc();
		}
		flush_serialPort(&Serial);
	}
}

void setup() {
	Serial.begin(9600);
	pinMode(relayTrigger_pin, OUTPUT);
	pinMode(LED_BUILTIN, OUTPUT);

	ledBlink(1);
	ledBlink(1);
	ledBlink();
}

// the loop function runs over and over again until power down or reset
void loop() {
	serialEvent();

}