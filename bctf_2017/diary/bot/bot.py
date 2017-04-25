#!/usr/bin/python
# -*- coding: UTF-8 -*-
__author__      = "firesun"
__license__     = "GPL"
from selenium import webdriver
import requests
import os
import time
from pyvirtualdisplay import Display

display = Display(visible=0, size=(800,800))
display.start()

while True:
    try:
        r = requests.get('A url where could get the reported address')
        url = r.text
        if url!="" and url.startswith('http://diary.bctf.xctf.org.cn/'):
            print "[INFO] URL="+url
            driver = webdriver.Chrome()
            try:
                driver.set_page_load_timeout(10)
                driver.set_script_timeout(10)
                #login as firesun
                driver.get("http://diary.bctf.xctf.org.cn/accounts/logout/")
                time.sleep(1)
                driver.get("http://auth.bctf.xctf.org.cn/accounts/logout/")
                time.sleep(1)
                driver.get("http://diary.bctf.xctf.org.cn/accounts/login/")
                time.sleep(2)
                elem = driver.find_element_by_name("username")
                elem.clear()
                elem.send_keys('firesun')
                elem = driver.find_element_by_name("password")
                elem.clear()
                elem.send_keys('xxxxxxxxxxxxxxxxxxxxxxxxx')
                elem = driver.find_element_by_tag_name("button")
                elem.click()
                time.sleep(1)
                print "[INFO] Login as firesun"
                #clear referer
                driver.get("about:blank")
                #visit the url
                driver.get(url)
                time.sleep(10)
                print "[INFO] Visited the address"
                driver.get("about:blank")
                #free server resources (session)
                driver.get("http://diary.bctf.xctf.org.cn/accounts/logout/")
                time.sleep(1)
                driver.get("http://auth.bctf.xctf.org.cn/accounts/logout/")
                time.sleep(1)
                driver.quit()
                print "[INFO] Close the chrome"  
            except Exception as e: 
                print "[ERROR] "+str(e)
                #important
                driver.quit()
    except Exception as e: 
        print "[ERROR] "+str(e)
    os.system("ps aux|grep chrome|awk '{print $2}'|xargs kill -9")
    #clear cookies
    os.system("rm -rf ~/.config/google-chrome/Default/Cookies")
    time.sleep(1)