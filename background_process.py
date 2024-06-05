import subprocess
import time

counter = 0

while True:
    counter = counter + 1
    subprocess.Popen("php gui/spark command:formula_measurement_logs", shell=True)
    if(counter >= 60):
        subprocess.Popen("php gui/spark command:sentdata", shell=True)
        subprocess.Popen("php gui/spark command:measurement_averaging", shell=True)
        counter = 0
    time.sleep(1)
