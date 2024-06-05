// byte perintah[] = {0x01, 0x03, 0x03, 0xE8, 0x00, 0x08, 0xC4, 0x7C};
byte perintah[] = {0x01, 0x03, 0x03, 0xE8, 0x00, 0x08};
byte bufferDataModbus[100];
byte *ptr;
 
void setup() {
  ptr = bufferDataModbus;
  Serial.begin(9600);
  uint16_t crc = calcCRC(perintah, sizeof(perintah));
  Serial.println(lowByte(crc));
  Serial.println(highByte(crc));
  
}
 
void loop()
{
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