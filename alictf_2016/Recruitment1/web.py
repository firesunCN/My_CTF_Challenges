import tornado.ioloop
import tornado.web
import hashlib
from Crypto import Random
from Crypto.Cipher import AES
import base64
import os.path
import torndb
import urllib
from tornado.options import define, options
import tornado.gen
from torndsession.sessionhandler import SessionBaseHandler
from geetest import GeetestLib
import json
import re
import time

captcha_id = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
private_key = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"

db=torndb.Connection('localhost','firecms',user='fire',password='fire')  
BS = 16
password = "C0bEVhYz0BJ9JqPD"
key = hashlib.md5(password).hexdigest()[:BS]
pad = lambda s: s + (BS - len(s) % BS) * chr(BS - len(s) % BS)
unpad = lambda s : s[:-ord(s[len(s)-1:])]

class AESCipher:
    def __init__(self, key):
        self.key = key

    def encrypt(self, raw):
        raw = pad(raw)
        iv = Random.new().read(AES.block_size)
        cipher = AES.new(self.key, AES.MODE_CBC, iv)
        return base64.urlsafe_b64encode(iv + cipher.encrypt(raw)) 

    def decrypt(self, enc):
        enc = base64.urlsafe_b64decode(enc.encode('utf-8'))
        iv = enc[:BS]
        cipher = AES.new(self.key, AES.MODE_CBC, iv)
        s= cipher.decrypt(enc[BS:])
        if (ord(s[len(s)-1:])>len(s)):
            raise Exception("pad error")
        for i in s[-ord(s[len(s)-1:]):]:
            if ord(i)!=ord(s[len(s)-1:]):
                raise Exception("pad error")
        return unpad(s)

class MainHandler(tornado.web.RequestHandler):
    def get(self):
        cookie=self.get_cookie("username")
        if cookie==None or cookie=="":
            self.redirect('/login')
            return
        cookie=urllib.unquote(cookie)
        aes=AESCipher(key)
        username=aes.decrypt(cookie)

        self.render("index.html")

class InfoHandler(tornado.web.RequestHandler):
    def get(self):
        cookie=self.get_cookie("username")
        if cookie==None or cookie=="":
            self.redirect('/login')
            return
        cookie=urllib.unquote(cookie)
        aes=AESCipher(key)
        username=aes.decrypt(cookie)

        try:
            res = db.get("select * from aliuser where aliuser='%s';"%username)
            if res==None:
                self.clear_cookie("username")
                self.redirect('/login')
                return
            else:
                self.render("info.html",info=res)
        except:
            self.clear_cookie("username")
            self.redirect('/login')
            return
        
class LoginHandler(SessionBaseHandler):
    def get(self):
        cookie=self.get_cookie("username")
        if cookie!=None and cookie!="":
            self.redirect('/')
            return
        self.render("login.html",error=None)
        
    def post(self):
        cookie=self.get_cookie("username")
        if cookie!=None and cookie!="":
            self.redirect('/')
            return
           
        gt = GeetestLib(captcha_id, private_key)
        challenge = self.get_argument(gt.FN_CHALLENGE, "")
        validate = self.get_argument(gt.FN_VALIDATE, "")
        seccode = self.get_argument(gt.FN_SECCODE, "")
        status = self.session[gt.GT_STATUS_SESSION_KEY]
        user_id = self.session["user_id"]
        if status:
            result = gt.success_validate(challenge, validate, seccode, user_id)
        else:
            result = gt.failback_validate(challenge, validate, seccode)
            self.session["user_id"] = user_id
        if not result:
            self.render("login.html",error='You aren\'t human,are you?')
            return

        username = self.get_argument("username", strip=True)
        password = self.get_argument("password", strip=True)
        if (re.match(r"^\w+$",  username)==None or re.match(r"^\w+$",  password)==None):  
            self.render("reg.html",error='Only allow [\w+]!')
            return
        
        res = db.get('select * from aliuser where aliuser=%s and passwd=%s',username,password)

        if (res):
            aes=AESCipher(key)
            self.set_cookie("username", urllib.quote(aes.encrypt(username)))
            self.redirect('/')
        else:
            self.render("login.html",error='wrong username or password!')

class LogoutHandler(tornado.web.RequestHandler):
    def get(self):
        self.clear_cookie("username")
        self.redirect('/login')
    
class RegHandler(SessionBaseHandler):
    def get(self):    
        cookie=self.get_cookie("username")
        if cookie!=None and cookie!="":
            self.redirect('/')
            return
        self.render("reg.html",error=None)
    def post(self):
        cookie=self.get_cookie("username")
        if cookie!=None and cookie!="":
            self.redirect('/')
            return
            
        gt = GeetestLib(captcha_id, private_key)
        challenge = self.get_argument(gt.FN_CHALLENGE, "")
        validate = self.get_argument(gt.FN_VALIDATE, "")
        seccode = self.get_argument(gt.FN_SECCODE, "")
        status = self.session[gt.GT_STATUS_SESSION_KEY]
        user_id = self.session["user_id"]
        if status:
            result = gt.success_validate(challenge, validate, seccode, user_id)
        else:
            result = gt.failback_validate(challenge, validate, seccode)
            self.session["user_id"] = user_id
        if not result:
            self.render("reg.html",error='You aren\'t human,are you?')
            return
            
        username = self.get_argument("username", strip=True)
        password = self.get_argument("password", strip=True)    
        if (re.match(r"^\w+$",  username)==None or re.match(r"^\w+$",  password)==None):  
            self.render("reg.html",error='Only allow [\w+]!')
            return

        try:
            res = db.execute('insert into aliuser values(%s,%s)',username,password)
            self.redirect('/login')
        except:
            self.render("reg.html",error='Register Failed: maybe duplicate username!')

class GetCaptchaHandler(SessionBaseHandler):
    def get(self):
        user_id = 'firesun'
        gt = GeetestLib(captcha_id, private_key)
        status = gt.pre_process(user_id)
        self.session[gt.GT_STATUS_SESSION_KEY] = status
        self.session["user_id"] = user_id
        response_str = gt.get_response_str()
        self.write(response_str)

class AjaxValidateHandler(SessionBaseHandler):
    def post(self):
        gt = GeetestLib(captcha_id, private_key)
        challenge = self.get_argument(gt.FN_CHALLENGE, "")
        validate = self.get_argument(gt.FN_VALIDATE, "")
        seccode = self.get_argument(gt.FN_SECCODE, "")
        status = self.session[gt.GT_STATUS_SESSION_KEY]
        user_id = self.session["user_id"]
        if status:
            result = gt.success_validate(challenge, validate, seccode, user_id)
        else:
            result = gt.failback_validate(challenge, validate, seccode)
            self.session["user_id"] = user_id
        result = result = {"status":"success"} if result else {"status":"fail"}
        self.write(json.dumps(result))
     
settings = {
    "static_path": os.path.join(os.path.dirname(__file__), "static"),
}        

application = tornado.web.Application([
    (r"/", MainHandler),
    (r"/login", LoginHandler),    
    (r"/reg", RegHandler),    
    (r"/logout", LogoutHandler),
    (r"/info", InfoHandler),
    (r"/register", GetCaptchaHandler),
    (r"/ajax_validate", AjaxValidateHandler)
], debug=False,**settings)

if __name__ == "__main__":
    tornado.options.options.logging = "none"
    application.listen(8888)
    tornado.ioloop.IOLoop.instance().start() 