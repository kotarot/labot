#!/usr/bin/python
# -*- coding: utf-8 -*- 
# (C)2016 Dr.Hiroshi Ishikawa

from RPi_AS3935 import RPi_AS3935
import RPi.GPIO as GPIO
import time
import csv #(1)
from datetime import datetime

GPIO.setmode(GPIO.BCM)

# Rev. 1 Raspberry Piの場合は bus=0,  rev. 2 の場合はbus=1
# 秋月製の雷センサーはI2Cアドレスは0x00固定

sensor = RPi_AS3935(address=0x00, bus=1) #(2)
sensor.reset() 
sensor.set_indoors(False) #(3) Outdoor
sensor.set_noise_floor(0) #(4)
sensor.calibrate(tun_cap=0x06) #(5) 32pF チューニングの結果をここで指定する

def handle_interrupt(channel):
	time.sleep(0.003)
	global sensor
	reason = sensor.get_interrupt()
	now = datetime.now().strftime("%Y/%m/%d %H:%M:%S")
	outputfile = open(filename , "a" )
	writer = csv.writer(outputfile)
	if reason == 0x01: #(6)
		sensor.raise_noise_floor()
		buffer = [now, "Noise level too high",  str(distance)  , str(energy)]
		print (buffer)
		writer.writerow(buffer)
	elif reason == 0x04: #(7)
		sensor.set_mask_disturber(True)
		buffer = [now, "Disturber detected",  str(distance)  , str(energy)]
		print (buffer)
		writer.writerow(buffer)
	elif reason == 0x08: #(8)
		distance = sensor.get_distance()
		energy = sensor.get_energy()
		buffer = [now, "lightning!" ,  str(distance)  , str(energy)]
		print (buffer)
		writer.writerow(buffer)
	outputfile.close()

#ここから始まり
IRQ = 4

GPIO.setup(IRQ, GPIO.IN)
GPIO.add_event_detect(IRQ, GPIO.RISING, callback=handle_interrupt) #(9)
now = datetime.now().strftime("%Y/%m/%d %H:%M:%S")

filename = 'lightning/' + datetime.now().strftime("%Y") + 'lightning.txt' #(1)
outputfile = open(filename , "a" )
writer = csv.writer(outputfile)
distance = sensor.get_distance()
energy = sensor.get_energy()
buffer = [now, "Waiting for lightning",  str(distance)  , str(energy) ]
print (buffer)
writer.writerow(buffer)
outputfile.close()

while True:
	time.sleep(1.0) #(10)
