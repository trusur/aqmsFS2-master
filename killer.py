import time
import subprocess

subprocess.Popen("echo admin | sudo kill -S $(ps aux | grep '[p]hp' | awk '{print $2}')", shell=True)
time.sleep(1)
subprocess.Popen("echo admin | sudo kill $(ps aux | grep 'terminal' | awk '{print $2}')", shell=True)
time.sleep(1)
subprocess.Popen("echo admin | sudo kill $(ps aux | grep 'firefox' | awk '{print $2}')", shell=True)