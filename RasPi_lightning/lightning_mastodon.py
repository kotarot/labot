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
sensor.set_indoors(True) #(3) Outdoor
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
		buffer2 = [now, "ノイズレベルが高すぎます",  str(distance)  , str(energy)]
		post_mastodon_lightning(buffer2)
	elif reason == 0x04: #(7)
		sensor.set_mask_disturber(True)
		buffer = [now, "Disturber detected",  str(distance)  , str(energy)]
		print (buffer)
		writer.writerow(buffer)
		buffer2 = [now, "嵐だ！！！！",  str(distance)  , str(energy)]
		post_mastodon_lightning(buffer2)
	elif reason == 0x08: #(8)
		distance = sensor.get_distance()
		energy = sensor.get_energy()
		buffer = [now, "lightning!" ,  str(distance)  , str(energy)]
		print (buffer)
		writer.writerow(buffer)
		buffer2 = [now, "雷だ！！！！",  str(distance)  , str(energy)]
		post_mastodon_lightning(buffer2)
	outputfile.close()

#ここから始まり

import subprocess
import urllib
def post_mastodon(status):
	global MASTODON_ACCESS_TOKEN, MASTODON_HOST
	command = 'curl -X POST -d "access_token=' + MASTODON_ACCESS_TOKEN + \
                  '&status=' + urllib.quote_plus(status) + \
                  '&visibility=public" -Ss ' + \
                  'https://' + MASTODON_HOST + '/api/v1/statuses'
	ret = subprocess.check_call(command, shell=True)
	print ret

def post_mastodon_lightning(buffer):
	post_mastodon(':zap: ' + buffer[0] + ' ' + buffer[1] + \
		' 距離: ' + buffer[2] + ' エネルギー: ' + buffer[3])

# マストドン設定ファイルを無理やり読み込む
import os
MASTODON_ACCESS_TOKEN = ''
MASTODON_HOST = ''
with open(os.path.abspath(os.path.dirname(__file__)) + '/../mastodon.config.php') as f:
	lines = f.readlines()
	for line in lines:
		if 'define' in line:
			terms = line.split("'")
			if terms[1] == 'MASTODON_ACCESS_TOKEN':
				MASTODON_ACCESS_TOKEN = terms[3]
			if terms[1] == 'MASTODON_HOST':
				MASTODON_HOST = terms[3]

IRQ = 4

GPIO.setup(IRQ, GPIO.IN)
GPIO.add_event_detect(IRQ, GPIO.RISING, callback=handle_interrupt) #(9)
now = datetime.now().strftime("%Y/%m/%d %H:%M:%S")

filename = os.path.abspath(os.path.dirname(__file__)) + '/lightning/' + datetime.now().strftime("%Y") + 'lightning.txt' #(1)
outputfile = open(filename , "a" )
writer = csv.writer(outputfile)
distance = sensor.get_distance()
energy = sensor.get_energy()
buffer = [now, "Waiting for lightning",  str(distance)  , str(energy) ]
print (buffer)
writer.writerow(buffer)
buffer2 = [now, "雷を待っているのです",  str(distance)  , str(energy) ]
post_mastodon_lightning(buffer2)
outputfile.close()

while True:
	time.sleep(1.0) #(10)
