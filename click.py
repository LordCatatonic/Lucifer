from selenium import webdriver
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.common.by import By
import time
import thread
import random

# Click random URL on page
def clickad(url, useragent):
	global driver
	try:
		try:
			webdriver.DesiredCapabilities.PHANTOMJS['phantomjs.page.customHeaders.User-Agent'] = useragent
			driver = webdriver.PhantomJS(os.getenv('appdata') + '/phantomjs.exe')
		except:
			try:
				driver = webdriver.Chrome()
			except:
				driver = webdriver.Firefox()
		driver.set_window_size(1024, 768)
		driver.get(url)
		time.sleep(3)
		print prefix + "Clicking " + url
		print driver.current_url
		thread.start_new_thread(doclick, ())
		time.sleep(10)
		driver.quit()
		return True
	except:
		return False

def doclick():
	global driver
	continue_link = driver.find_elements(By.XPATH, '//a')
	driver.set_window_size(1000, 1000)
	random.choice(continue_link).click()
	driver.set_window_size(1, 1)
