# -*- coding: utf-8 -*-

# 433_receiverdb_op.py - output screen version of the program with MySQL entry and feedback
# on detection of allowed sensors and feedback only for unrecognised signals
"""
@author     Keith Clark
@update     27.Jan.2017
@info       modified from code by Alexander Rüedlinger and Malcolm Maclaren
"""

from pi_switch import RCSwitchReceiver
import RPi.GPIO as GPIO
import logging
import MySQLdb as mdb
import time

# set up to log MySQL database errors
logging.basicConfig(filename='sensor_error.log',
                    level=logging.DEBUG,
                    format='%(asctime)s %(levelname)s %(name)s %(message)s')
logger=logging.getLogger(__name__)

# Function called to read allowed sensor codes from MySQL db into config file
def readSensors():
    
  db = mdb.connect('localhost', \
                   'sensorreader', \
                   'MwhCmQVatUFyEzF5', \
                   'homesensor');

  try:
    cur = db.cursor()
    cur.execute("""SELECT sensorcode FROM knownsensors""")
    return cur.fetchall() # use fetchall to get all the return results which is a list object

  except mdb.Error. e:
    logger.error(e)

  finally:
    if db:
      db.close()

# Function called when received_value is true and matches an allowed sensor value
def storeFunction(channel):
        
  con = mdb.connect('localhost', \
                    'sensorwriter', \
                    'EWE-5h9-eSM-WTS', \
                    'homesensor');
            
  try:
    cur = con.cursor()
    cur.execute("""INSERT INTO sensordata(sensorcode) VALUES(%s)""", (received_value))
    con.commit()
        
  except mdb.Error, e:
    logger.error(e)
    
  finally:
    if con:
        con.close()

# set up RCSwitchReceiver parameters
receiver = RCSwitchReceiver()
receiver.enableReceive(2) # pin 2 WiringPi, GPIO27

# set up GPIO parameters for wait_for_edge command
GPIO.setmode(GPIO.BOARD) # use the BOARD GPIO pin numbering
GPIO_PIR = 13 # pin 13 GPIO.BOARD, GPIO27
GPIO.setup(GPIO_PIR, GPIO.IN)

# refresh config file with list of allowed sensors from MySQL db
allowsensors = readSensors()
filewrite = open("433_receiver.cfg", "w")
for row in allowsensors:
    print>>filewrite, row[0]
filewrite.close()

num = 0
fsr = 0

print "433MHz sensor event monitoring and DB logging active (CTRL-C to exit)"
print " "

try:
  while True:
    
    GPIO.wait_for_edge(GPIO_PIR, GPIO.BOTH) # wait_for_edge inserted to reduce CPU usage
        
    if receiver.available():
        received_value = receiver.getReceivedValue()

        if received_value:
		with open("433_receiver.cfg", "r") as fileread:
		    for line in fileread:
			line = line.rstrip() # strip carriage return from line values in file
			if int(line) == int(received_value):
				num += 1
				print("Received[%s]:" % num)
				print(received_value)
				print("%s / %s bit" % (received_value, receiver.getReceivedBitlength()))
				print("Protocol: %s" % receiver.getReceivedProtocol())
				print("Delay: %s" % receiver.getReceivedDelay())
				print(" ")
				storeFunction(13) # insert received value into MySQL database
				time.sleep(1.8) # sleep for reliable single data event from sensors
				break # break to ensure 'for loop' stops when match found
		    # use the following if statement for debugging rogue unknown sensor triggering
		    # comment out during normal operation to avoid time delay issues due to sleep
		    if int(line) != int(received_value):
			fsr += 1
			print("Sensor code not recognised: %s [%s]" % (received_value, fsr))
			print(" ")
			time.sleep(1.8)

	receiver.resetAvailable()

	time.sleep(0.25) # sleep to reduce CPU usage due to noise triggering

except KeyboardInterrupt:
    print ("")
    print ("Be Seeing You!")
    GPIO.cleanup() # Reset the GPIO settings
