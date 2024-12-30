import random
import db
import time
from datetime import datetime, timedelta

def convert_ugm3_to_ppb(gas, concentration_ugm3):
    molar_masses = {
        'CO2' : 44.01,   
        'SO2' : 64.06,  
        'O3' : 48.00,   
        'NO2' : 46.0055,
        'HC' : 44
    }
    
    if gas not in molar_masses:
        raise ValueError("Gas tidak dikenal. Pilih antara 'CO2', 'SO2', 'O3', 'NO2' , 'HC'.")
    
    molar_mass = molar_masses[gas]
    ppb = (concentration_ugm3 * 1e-6) / (molar_mass * 0.022414) * 1000 if gas != 'HC' else (concentration_ugm3 * 1000 / ( 0.0409 * molar_mass))

    return  ppb

# Contoh penggunaan
gas = 'CO2'
concentration_ugm3 = 500  # contoh konsentrasi CO2 dalam µg/m³

ppm, ppb = convert_ugm3_to_ppb(gas, concentration_ugm3)

print(f"{gas} - {concentration_ugm3} µg/m³ = {ppm:.6f} PPM = {ppb:.0f} PPB")


while True:
    try:
        current_time = datetime.now()
        hour = current_time.hour

        # insert data PM
        pm10 = random.uniform(20, 50)
        pm25 = random.uniform(9, 12) if 0 <= hour < 18 else random.uniform(15, 16)

        data_pm = f"PM_OPC;0;1;{pm25};{pm10};{round(random.uniform(27, 33), 1)};{round(random.uniform(1, 1.8), 1)};{round(random.uniform(0.5, 2.0), 2)};[HeaterStatus];[HeaterONTime];[HeaterOFFTimer];END_PM_OPC;"
        db.update_sensor_values(1, 10, data_pm)

        # insert HC
        mg_hc = round(random.uniform(1.00, 2.50), 2)
        hc = round(mg_hc * 1000 / ( 0.0409 * 44),2)
        value_hc = f"HC;0;{hc};{mg_hc};END_HC;"
        db.update_sensor_values(2, 20, value_hc)

        co = random.uniform(500, 2000)
        so2 = random.uniform(30,70)
        hc = random.uniform(5, 30)
        no2 = random.uniform(80,200)
        o3 = random.uniform(6,30)


        
    except Exception as e:
        print("Error in script:", e)
    
    time.sleep(2)
