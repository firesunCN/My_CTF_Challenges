# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.shortcuts import render
from django.shortcuts import render_to_response
from django.template import RequestContext
from django.http import JsonResponse
from django.http import HttpResponseRedirect
from django.http import Http404, HttpResponse
from django.shortcuts import redirect
from django.views.decorators.clickjacking import xframe_options_exempt
from django.views.decorators.csrf import csrf_exempt
from django.views.decorators.http import require_http_methods
from django import forms
from django.contrib.auth.forms import UserCreationForm
from django.contrib import auth
from django.contrib.auth.models import User
from django.contrib.auth import get_user_model

from oauth_client.models import Survey, Diary, Url
from random import Random

import time
import requests
import json
import re
import hashlib

oauth_server="http://auth.bctf.xctf.org.cn"
app_server="http://diary.bctf.xctf.org.cn"
client_id="nSGRX6eRNbkHBy457ZfiNG1JrUjJ9k7hZigKYA1q"
client_secret="S5Elk8WWpylMbNedDlpN12ds0zCgMNzcJCbHQqnc32Td4YKMpkLEX8F8i02I8DuYbcwZQvn0wbiiSlGSNHGhlLoOxos4xqHE5TCHvFlklUDkPF4VtNBVVdSKY8wC9btA"
UserModel = get_user_model()

# Create your views here.
@require_http_methods(["GET"])
def index(request):
    return render(request, 'index.html')

@require_http_methods(["GET"])
def about(request):
    return render(request, 'about.html')    

@require_http_methods(["GET"])
def logout(request):
    auth.logout(request)
    return HttpResponseRedirect(oauth_server+"/accounts/logout/")
    
def _authenticate(request, username):
    try:
        user = UserModel._default_manager.get_by_natural_key(username)
    except :
        pass
    else:
        if _user_can_authenticate(user):
            return user
    return None
    
def _user_can_authenticate(user):
    """
    Reject users with is_active=False. Custom user models that don't have
    that attribute are allowed.
    """
    is_active = getattr(user, 'is_active', None)
    return is_active or is_active is None    

@require_http_methods(["GET"])
@csrf_exempt
def receive_authcode(request):
    if 'start_oauth' in request.session and request.session['start_oauth'] == 1:
        request.session['start_oauth'] = 0
    else:
        raise Http404()
    try:
        if 'code' in request.GET:
            code = request.GET.get('code', '').strip()
            if code=='':
                raise Http404()
            url = oauth_server+'/o/token/'
            s = requests.Session()
            var = {'grant_type':'authorization_code',
                   'code':code,
                   'redirect_uri':app_server+'/o/receive_authcode',
                   'client_id':client_id,
                   'client_secret':client_secret,
            }
            r = s.post(url=url,data=var)
            res=json.loads(r.text)
            
            if 'access_token' in res:
                access_token=res['access_token']
                url = oauth_server+'/o/get-username/'
                s = requests.Session()
                var = {'token':access_token,}
                headers = {'Authorization': 'Bearer '+access_token}
                r = s.post(url=url,data=var,headers=headers)
                res=json.loads(r.text)
                username=res['username']
                user = _authenticate(request, username)
                if user!=None:
                    auth.login(request, user)
                    return redirect('/')
                else:
                    new_user = User.objects.create_user(username=username, password="e6gqxLHvFR74LNBLvJpFDw20IrQH6nef")
                    new_user.save()
                    user = _authenticate(request, username)
                    if user!=None:
                        auth.login(request, user)
                        return redirect('/')
                    else:
                        raise Http404()
    except:
        pass
    raise Http404()

@require_http_methods(["GET"])     
def login(request):
    if request.user.is_authenticated:
        return redirect('/')
    auth_url = oauth_server+"/o/authorize/?client_id="+client_id+"&state=preauth&response_type=code"
    request.session['start_oauth'] = 1
    return HttpResponseRedirect(auth_url)

@require_http_methods(["GET"])
def diary(request):
    if not request.user.is_authenticated:
        raise Http404()
    return render(request, 'diary.html')
    
@require_http_methods(["GET","POST"])
def survey(request):
    if not request.user.is_authenticated:
        raise Http404()

    if request.method != 'POST':
        return render(request, 'survey.html')
    rate = request.POST.get('rate', '')
    if rate=='1':
        rate=1
    elif rate=='2':
        rate=2
    elif rate=='3':
        rate=3
    elif rate=='4':
        rate=4
    elif rate=='5':
        rate=5
    else:
        return render(request, 'survey.html', {'msg': 'Rate is invalid!'})

    suggestion = request.POST.get('suggestion', '').strip()
    if len(suggestion) > 2000 :
        return render(request, 'survey.html', {'msg': 'Advice is too long!'})
    if len(suggestion) <= 0:
        return render(request, 'survey.html', {'msg': 'Advice is empty!'})    
    
    try:
        Survey.objects.get(username=request.user.username,rate=rate,suggestion=suggestion)
    except Survey.DoesNotExist:
        Survey.objects.create(username=request.user.username,rate=rate,suggestion=suggestion)
    
    if request.user.username=="firesun":
        return render(request, 'survey.html', {'msg': 'Thank you. I will give you the flag. Flag is bctf{bFJbSakOT72T8HbDIrlst4kXGYbaHWgV}'})
    else:
        return render(request, 'survey.html', {'msg': 'Thank you. But the boss said only admin can get the flag after he finishes this survey, XD'})
    
@require_http_methods(["GET","POST"])
def edit_diary(request):
    if not request.user.is_authenticated:
        raise Http404()

    if request.user.username=="firesun":
        return HttpResponse("Don't do this!")
    if request.method != 'POST':
        return render(request, 'edit_diary.html')
    content = request.POST.get('content', '').strip()
    if len(content) > 1000 :
        return render(request, 'edit_diary.html', {'msg': 'Too long!'})
    if len(content) <= 0:
        return render(request, 'edit_diary.html', {'msg': 'Write something!'})    
    
    try:
        diary=Diary.objects.get(username=request.user.username)
        Diary.objects.filter(id=diary.id).update(content=content)
    except Diary.DoesNotExist:
        Diary.objects.create(username=request.user.username,content=content)

    return redirect('/diary/')
    
@require_http_methods(["GET"])
def report_status(request):
    try:
        url=Url.objects.get(id=request.GET.get('id', ''))
        if url.is_read:
            return HttpResponse("Admin has visited the address.")
        else:
            return HttpResponse("Admin doesn't visit the address yet.")
    except:
        raise Http404()

def random_str(randomlength=5):
    str = ''
    chars = '0123456789abcdef'
    length = len(chars) - 1
    random = Random()
    for i in range(randomlength):
        str+=chars[random.randint(0, length)]
    return str
    
@require_http_methods(["GET","POST"])
def report_bugs(request):
    if not request.user.is_authenticated:
        raise Http404()
    if request.method != 'POST':
        captcha=random_str()
        request.session['captcha']=captcha
        return render(request, 'report.html',{'captcha': captcha})
    else:
        if ('captcha' in request.session) and (request.session['captcha'] == hashlib.md5(request.POST.get('captcha', '')).hexdigest()[0:5]):
            captcha=request.session['captcha']
            url = request.POST.get('url', '').strip()   
            if not url.startswith('http://diary.bctf.xctf.org.cn/'):
                return render(request, 'report.html', {'msg': 'We only care about the problem from this website (http://diary.bctf.xctf.org.cn)!','captcha': captcha})
            if len(url) > 200 or len(url) <= 0:
                return render(request, 'report.html', {'msg': 'URL is too long!','captcha': captcha})
            if not re.match(r'^https?:\/\/[\w\.\/:\-&@%=\?]+$', url):
                return render(request, 'report.html', {'msg': 'Invalid URL!','captcha': captcha})
            try:
                new_url=Url.objects.create(url=url)
            except:
                return render(request, 'report.html', {'msg': 'Invalid URL!','captcha': captcha})

            captcha=random_str()
            request.session['captcha']=captcha
            return render(request, 'report.html', {'msg': 'Report success! Click <a href="/report-status/?id='+str(new_url.id)+'">here</a> to check the status.','captcha': captcha})
        else:
            captcha=random_str()
            request.session['captcha']=captcha
            return render(request, 'report.html',{'msg': 'Invalid Captcha!','captcha': captcha})

@require_http_methods(["GET"])
def view_diary(request):
    if not request.user.is_authenticated:
        raise Http404()
    content="Empty!"
    try:
        diary=Diary.objects.get(username=request.user.username)
        content=diary.content
    except:
        pass
    return JsonResponse({'content':content})
