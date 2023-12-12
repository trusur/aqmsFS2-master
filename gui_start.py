import subprocess
# subprocess.Popen("chromium --fullscreen --kiosk http://localhost:8080", shell=True)
subprocess.Popen("firefox --kiosk=http://localhost:8080", shell=True)
# subprocess.Popen("chromium-browser --fullscreen --kiosk http://localhost:8080", shell=True)