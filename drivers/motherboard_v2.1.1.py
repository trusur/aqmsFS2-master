from pyModbusTCP.client import ModbusClient
import logging
def get_registers():
    try:
        sensor_codes = [
            'pm10',
            'pm25',
            'no2',
            'so2',
            'o3',
            'co',
            'hc',
            'h2s',
            'nh3',
            # Weather Data
            'ws',
            'wd',
            'pressure',
            'temperature',
            'humidity',
            'sr',
            'rain',
            # Sample Conditioning Data
            'rain',
        ]
    except Exception as e:
        return []

def connect_motherboard():
    try:
        host = "192.168.123.150"
        port = 502
        return ModbusClient(host=host, port=port, debug=False, timeout=5, unit_id=1, auto_open=True, auto_close=False)
    except Exception as e:
        logging.error("Motherboard connection error: " + str(e))
        return False
    
def main():
        
