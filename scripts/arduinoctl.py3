#!/usr/bin/python

import serial
import time
import json
import sys
import os

tempPath = "/mnt/ramdisk/temperature.txt"
humPath = "/mnt/ramdisk/humidity.txt"
pressPath = "/mnt/ramdisk/pressure.npy"
dataPath = "/mnt/ramdisk/data.json"

with open(tempPath,"w") as tempFile:
    tempFile.write("nodata")

with open(humPath,"w") as humFile:
    humFile.write("nodata")

os.system("cp /var/www/data.json " + dataPath)

# NumPy #
os.system("cp /var/www/pressure.npy.empty " + pressPath)
npyworks = True

if os.system("python -c 'import numpy'") == 0:
    import numpy as np
else:
    os.system("dpkg -i /var/cache/apt/archives/python-numpy*")

    if os.system("python -c 'import numpy'") == 0:
        import numpy as np
    else:
        npyworks = False

if npyworks:
    np.save(pressPath, np.array([]))
# NumPy #

while True:
    try:
        arduino = serial.Serial("/dev/ttyUSB0", 9600, timeout=0)
        break
    except:
        time.sleep(2)

time.sleep(2)

lastStatus = 0
pressureParity = True
errors = 0

while True:
    try:
        raw = arduino.readline()
        print("Incoming: " + raw.decode("utf-8"))

        data = json.loads(raw)

        data['temp'] -= 2    #soglia di aggiustamento temperatura

        arduino.flush()
        arduino.flushInput()
        arduino.flushOutput()

        errors = 0

        if data['connected']:

            with open(dataPath,"r") as dataFile:
                storedData = json.loads(dataFile.read())

            readTemp = "%1.1f" % data['temp']
            if 3 <= len(str(readTemp)) <= 4:
                with open(tempPath,"w") as tempFile:
                    tempFile.write(str(readTemp) + "*" + str(data['status']))

            with open(humPath,"w") as humFile:
                humFile.write(str("%1.f" % data['hum']))

            if pressureParity and npyworks:

                presLst = np.load(pressPath)

                if len(presLst) < 1800:
                    presLst = np.append([0], presLst)
                else:
                    presLst = np.roll(presLst,1)

                presLst[0] = data['pres']

                np.save(pressPath, presLst)

                pressureParity = False

            else:
                pressureParity = True

            if data['temp'] < (storedData['max'] + 0.4) and storedData['active'] and not storedData['night']:

                if data['temp'] < (storedData['max'] - 0.4):
                    arduino.write("1".encode())
                    lastStatus = 1
                elif lastStatus and data['temp'] > (storedData['max'] - 0.4):
                    arduino.write("1".encode())
                    lastStatus = 1
                else:
                    arduino.write("0".encode())
                    lastStatus = 0

            elif data['temp'] < (storedData['min'] + 0.4) and storedData['active'] and storedData['night']:

                if data['temp'] < (storedData['min'] - 0.4):
                    arduino.write("1".encode())
                    lastStatus = 1
                elif lastStatus and data['temp'] > (storedData['min'] - 0.4):
                    arduino.write("1".encode())
                    lastStatus = 1
                else:
                    arduino.write("0".encode())
                    lastStatus = 0

            else:
                arduino.write("0".encode())
                lastStatus = 0

    except Exception as e:
        print("Error: " + str(e) + "\n")

        if len(sys.argv) > 1:
            errors = errors + 1
            if errors == 10 and not str(sys.argv[1]) == "--no-overwrite":
                errors = 0
                os.system("avrdude -p m328p -c arduino -P /dev/ttyUSB0 -b 57600 -U flash:w:/usr/local/lib/termometro3.0.ino.hex:i")

        if not os.path.exists('/dev/ttyUSB0'):
            break

    time.sleep(2)
