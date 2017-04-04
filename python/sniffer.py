# -*- coding: utf-8 -*-

# 433_receiverdb_sniff.py - 433MHz sniffing output screen version of the program
# with feedback on detection of all 433MHz signals - note: no logging to MySQL tables
"""
@author     Keith Clark
@update     27.Jan.2017
@info       modified from code by Alexander Rüedlinger and Malcolm Maclaren
"""

from pi_switch import RCSwitchReceiver
import RPi.GPIO as GPIO
import time

# set up RCSwitchReceiver parameters
receiver = RCSwitchReceiver()
receiver.enableReceive(2) # pin 2 WiringPi, GPIO27

# set up GPIO parameters for wait_for_edge command
GPIO.setmode(GPIO.BOARD) # use the BOARD GPIO pin numbering
GPIO_PIR = 13 # pin 13 GPIO.BOARD, GPIO27
GPIO.setup(GPIO_PIR, GPIO.IN)

num = 0

print "433MHz sensor event sniffing active (CTRL-C to exit)"
print " "

try:
  while True:
    
    GPIO.wait_for_edge(GPIO_PIR, GPIO.BOTH) # wait_for_edge inserted to reduce CPU usage
        
    if receiver.available():
        received_value = receiver.getReceivedValue()

        if received_value:
            	  num += 1
            	  print("Received[%s]:" % num)
            	  print(received_value)
            	  print("%s / %s bit" % (received_value, receiver.getReceivedBitlength()))
            	  print("Protocol: %s" % receiver.getReceivedProtocol())
            	  print("Delay: %s" % receiver.getReceivedDelay())
            	  print(" ")
            	  time.sleep(1.8) # sleep for reliable single data event from sensors

        receiver.resetAvailable()

    time.sleep(0.25) # sleep to reduce CPU usage due to noise triggering

except KeyboardInterrupt:
    print ("")
    print ("Be Seeing You!")
    GPIO.cleanup() # Reset the GPIO settings

