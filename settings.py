import platform
import random

def proxies():
	proxies = ['onion.city']
	return random.choice(proxies)
	
def onions():
	onions = ['BULLSHIT.onion', 'HUMANSACRIFICE.onion']
	return random.choice(onions)

def ostype():
	return platform.system()
	
def connectwait():
	return 2
	
def knocktime():
	return 180

def version():
	return 1.0
