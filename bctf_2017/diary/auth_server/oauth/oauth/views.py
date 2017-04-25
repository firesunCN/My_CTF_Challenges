# -*- coding: utf-8 -*-
from __future__ import unicode_literals
from django.http import JsonResponse
from django.shortcuts import render
from django.shortcuts import render_to_response
from django.template import RequestContext
from django.http import HttpResponseRedirect
from django.http import Http404, HttpResponse
from django.shortcuts import redirect
from django.views.decorators.clickjacking import xframe_options_exempt
from django.views.decorators.csrf import csrf_exempt
from django import forms
from django.contrib.auth.forms import UserCreationForm
from django.contrib import auth
from django.contrib.auth.models import User
from oauth2_provider.models import AccessToken
from oauth2_provider.decorators import protected_resource
from django.views.decorators.http import require_http_methods
import re

app_server="http://diary.bctf.xctf.org.cn"

# Create your views here.
@require_http_methods(["GET","POST"])
def register(request):
    if request.user.is_authenticated:
        raise Http404()
    if request.method != 'POST':
        return render(request, 'register.html')
    else:
        username = request.POST.get('username', '').strip()
        password = request.POST.get('password', '').strip()
        if len(username) > 64 or len(username) <= 0:
            return render(request, 'register.html', {'msg': 'Invalid username!'})
        if len(password) > 64 or len(password) <= 0:
            return render(request, 'register.html', {'msg': 'Invalid password!'})
        if not re.match(r'^\w+$', username):
            return render(request, 'register.html', {'msg': 'Invalid username!'})
        if not re.match(r'^\w+$', password):
            return render(request, 'register.html', {'msg': 'Invalid password!'})
        if User.objects.filter(username=username):
            return render(request, 'register.html', {'msg': 'This username has already been used!'})
        try:
            new_user = User.objects.create_user(username=username, password=password)
            new_user.save()
        except:
            return render(request, 'register.html', {'msg': 'This username has already been used!'})

        return render(request, 'success.html')

@require_http_methods(["GET","POST"])        
def login(request):
    if ('next' in request.GET) or ('next' in request.POST):
        if request.user.is_authenticated:
            if 'next' in request.GET:
                return redirect(request.GET['next'])
            else:
                return redirect(request.POST['next'])
        if request.method != 'POST':
            return render(request, 'login.html', {'next': request.GET.get('next', '/')})#accounts/profile
        else:
            username = request.POST.get('username', '').strip()
            password = request.POST.get('password', '').strip()
            user = auth.authenticate(username=username, password=password)
            if user is not None and user.is_active:
                # Correct password, and the user is marked "active"
                auth.login(request, user)
                # Redirect to a success page.
                return redirect(request.POST['next'])
            else:
                # Show an error page
                return render(request, 'login.html', {'msg': 'Invalid username or password', 'next': request.POST['next']})
    raise Http404()

@require_http_methods(["GET"])
def logout(request):
    auth.logout(request)
    referer_url = request.META.get('HTTP_REFERER', '/')
    if referer_url.startswith(app_server):
        return HttpResponseRedirect(app_server)
    raise Http404()

@csrf_exempt
@protected_resource()
@require_http_methods(["POST"])
def get_username(request):
    if 'token' in request.POST:
        try:
            return JsonResponse({'username':AccessToken.objects.get(token=request.POST['token']).user.__unicode__()})
        except:
            pass
    raise Http404()
