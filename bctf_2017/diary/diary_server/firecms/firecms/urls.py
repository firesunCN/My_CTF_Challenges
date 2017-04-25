"""firecms URL Configuration

The `urlpatterns` list routes URLs to views. For more information please see:
    https://docs.djangoproject.com/en/1.11/topics/http/urls/
Examples:
Function views
    1. Add an import:  from my_app import views
    2. Add a URL to urlpatterns:  url(r'^$', views.home, name='home')
Class-based views
    1. Add an import:  from other_app.views import Home
    2. Add a URL to urlpatterns:  url(r'^$', Home.as_view(), name='home')
Including another URLconf
    1. Import the include() function: from django.conf.urls import url, include
    2. Add a URL to urlpatterns:  url(r'^blog/', include('blog.urls'))
"""
from django.conf.urls import url
from django.contrib import admin
from oauth_client import views
from django.views.static import serve
urlpatterns = [
    url(r'^$', views.index, name='index'),
    url(r'^o/receive_authcode', views.receive_authcode),
    url(r'^accounts/login/', views.login, name='login'),
    url(r'^accounts/logout/', views.logout, name='logout'),
    url(r'^report_bugs/', views.report_bugs, name='report_bugs'),
    url(r'^diary/view/', views.view_diary, name='view_diary'),
    url(r'^diary/edit/', views.edit_diary, name='edit_diary'),
    url(r'^diary/', views.diary, name='diary'),
    url(r'^survey/', views.survey, name='survey'),
    url(r'^report-status/', views.report_status, name='report_status'),
    url(r'^about/', views.about, name='about'),	
    url(r'^static/(?P<path>.*)$', serve, {'document_root': '/var/www/html/static'}),
]
