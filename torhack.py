# Tor monkey hack
# http://fitblip.pub/2012/11/13/proxying-dns-with-python/ Thanks for the botnet help lmao
import socks
import socket
orig_sock = socket.socket

# Magic!
def getaddrinfo(*args):
    return [(socket.AF_INET, socket.SOCK_STREAM, 6, '', (args[0], args[1]))]
socket.getaddrinfo = getaddrinfo

import requests

# Set our current proxy
def setProxy(type=socks.PROXY_TYPE_SOCKS5, host="127.0.0.1", port="9050"):
    socks.setdefaultproxy(type, host, port)
    socket.socket = socks.socksocket

# Reset proxy to our original socket (no proxy)
def unsetProxy():
    socket.socket = orig_sock

# Check tor
def checkTor():
    if "Sorry" in requests.get('https://check.torproject.org/').text:
        return False
    else:
        return True
