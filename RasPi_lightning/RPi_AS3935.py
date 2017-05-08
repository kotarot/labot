# -*- coding: utf-8 -*- 
 
#Developed by  Phil Fenstermacher. Revised by Hiroshi Ishikawa.
# 2016/12/30 (C) Hiroshi Ishikawa.

import time

class RPi_AS3935:
	def __init__(self, address, bus=1):
		self.address = address
		import smbus
		self.i2cbus = smbus.SMBus(bus)

	def calibrate(self, tun_cap=None):
		#tune_capの値をレジスタ0x08のビット[3:0]にセットする
		time.sleep(0.08)#マニュアルに指定のタイミングをとる
		self.read_data()
		if tun_cap is not None:
			if tun_cap < 0x10 and tun_cap > -1:
				self.set_byte(0x08, (self.registers[0x08] & 0xF0) | tun_cap)
				time.sleep(0.002)
			else:
				raise Exception("Value of TUN_CAP must be between 0 and 15")
		#レジスタ0x3Dに0x96をセットし、内部クロック発振器の較正
		self.set_byte(0x3D, 0x96)
		time.sleep(0.002)
		self.read_data() 
		self.set_byte(0x08, self.registers[0x08] | 0x20)
		time.sleep(0.002)
		self.read_data() 
		self.set_byte(0x08, self.registers[0x08] & 0xDF)
		time.sleep(0.002)

	def reset(self):
		#レジスタ0x3Cに0x96を書き込み、すべてのレジスタをデフォルト値に
		self.set_byte(0x3C, 0x96)

	def get_interrupt(self):
		#レジスタ0x03のビット[3:0]が割り込み情報
		self.read_data()
		return self.registers[0x03] & 0x0F

	def get_distance(self):
		#レジスタ0x07のビット[5:0]が距離情報
		self.read_data()
		return self.registers[0x07] & 0x3F

	def get_energy(self):
		#レジスタ0x04、0x05、0x06のビット[4:0]がエネルギー。ただしこの値は一つの「雷」のエネルギー量で、内部処理用であり物理的な意味を持たない
		self.read_data()
		return ((self.registers[0x06] & 0x1F) * 65536)+ (self.registers[0x05] * 256)+ (self.registers[0x04])

	def set_noise_floor(self, noisefloor):
		#レジスタ0x01のビット[6:4]にノイズ下限レベルを設定
		self.read_data()
		noisefloor = (noisefloor & 0x07) << 4
		write_data = (self.registers[0x01] & 0x8F) + noisefloor
		self.set_byte(0x01, write_data)

	def get_noise_floor(self):
		#レジスタ0x01のビット[6:4]のノイズ下限レベルを読む
		self.read_data()
		return (self.registers[0x01] & 0x70) >> 4

	def raise_noise_floor(self, max_noise=7):
		#ノイズ下限レベルを1あげる
		floor = self.get_noise_floor()
		if floor < max_noise:
			floor = floor + 1
			self.set_noise_floor(floor)
		return floor

	def lower_noise_floor(self, min_noise=0):
		#ノイズ下限レベルを1さげる
		floor = self.get_noise_floor()
		if floor > min_noise:
			floor = floor - 1
			self.set_noise_floor(floor)
		return floor

	def set_indoors(self, indoors):
		#レジスタ0x00のビット[5:1] AFE利得に屋内か屋外かを指定
		self.read_data()
		if indoors:
			write_value = (self.registers[0x00] & 0xC1) | (0b10010 << 1) 
		else:
			write_value = (self.registers[0x00] & 0xC1) | (0b01110 << 1) 
		self.set_byte(0x00, write_value)

	def set_mask_disturber(self, mask_dist):
		#レジスタ0x03のビット[5]を制御
		self.read_data()
		if mask_dist:
			write_value = self.registers[0x03] | 0x20
		else:
			write_value = self.registers[0x03] & 0xDF
		self.set_byte(0x03, write_value)

	def get_mask_disturber(self):
		#レジスタ0x03のビット[5]を読む
		self.read_data()
		if self.registers[0x03] & 0x20 == 0x20:
			return True
		else:
			return False

	def set_disp_lco(self, display):
		#レジスタ0x08のビット[7]により、アンテナ共振周波数をIRQピンに出力
		self.read_data()
		if display:
			self.set_byte(0x08, (self.registers[0x08] | 0x80))
		else:
			self.set_byte(0x08, (self.registers[0x08] & 0x7F))
		time.sleep(0.002)

	def set_byte(self, register, value):
		#i2cbus書き込みの基本コマンド
		try:
			self.i2cbus.write_byte_data(self.address, register, value)
		except Exception as e:#エラーでもメッセージをだして処理続行
			print '==Error set_byte()==' + str(e)

	def read_data(self):
		#i2cbus読み込みの基本コマンド
		try:
			self.registers = self.i2cbus.read_i2c_block_data(self.address, 0x00)
		except Exception as e:#エラーでもメッセージをだして処理続行
			print '==Error read_data()==' + str(e)
