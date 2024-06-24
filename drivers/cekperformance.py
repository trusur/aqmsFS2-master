import platform
import psutil
from datetime import datetime
import requests
import json
import sys
import mysql.connector

# get date
now = datetime.now()
timestamp = now.strftime('%Y-%m-%d %H:%M:%S')
minutes = now.strftime('%M')

try:
    mydb = mysql.connector.connect(
    host="localhost",
    user="root",
    password="R2h2s12*",
    database="aqms_efs1"
    )
    
    mycursor = mydb.cursor()

    mycursor.execute("SELECT * FROM configurations where name = 'id_stasiun'")

    id_stasiun = mycursor.fetchone()[2]

    def sentData(cpu,memory,harddisk):
        url = "https://api.trusur.tech/api/aqms_unit_performance.php"

        payload = json.dumps({
        "id_stasiun": id_stasiun,
        "architecture": platform.architecture()[0],
        "machine": platform.machine(),
        "node": platform.node(),
        "sistem": platform.system(),
        "cpu": round(psutil.cpu_percent()),
        "memory": round(psutil.virtual_memory()[2]),
        "hdd": round((hdd.used / (2**30)) * 100 / (hdd.total / (2**30))),
        "waktu": timestamp
        })
        headers = {
        'Api-Key': 'VHJ1c3VyVW5nZ3VsVGVrbnVzYV9wVA==',
        'Content-Type': 'application/json',
        'Authorization': 'Basic S0xISy0yMDE5OlByb2plY3QyMDE2LTIwMTk='
        }

        response = requests.request("PUT", url, headers=headers, data=payload)
    
    # get hdd info
    hdd = psutil.disk_usage('/')

    # Architecture
    architecture = platform.architecture()[0]

    # machine
    machine = platform.machine()

    # node
    node = platform.node()

    # system
    system = platform.system()

    # cpu
    cpu = round(psutil.cpu_percent())

    #memory
    memory = round(psutil.virtual_memory()[2])

    #hdd
    harddisk = round((hdd.used / (2**30)) * 100 / (hdd.total / (2**30)))

    sentData(cpu,memory,harddisk)

    if(cpu > 80 or memory > 80 or harddisk > 80):
        sentData(cpu,memory,harddisk)
        sys.exit
    
    if(minutes == "30" or minutes == "00"):
        sentData(cpu,memory,harddisk)
        sys.exit
except Exception as e:
    None