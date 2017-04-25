# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.contrib import admin
from oauth_client.models import Survey, Diary, Url

class UrlAdmin(admin.ModelAdmin):
    list_display = ('url','is_read')
    search_fields = ('is_read',)
    fieldsets = (
        ['Main',{
            'fields':('url','is_read'),
        }],
    )
    
class DiaryAdmin(admin.ModelAdmin):
    list_display = ('username','content')
    search_fields = ('username',)
    fieldsets = (
        ['Main',{
            'fields':('username','content'),
        }],
    )
    
class SurveyAdmin(admin.ModelAdmin):
    list_display = ('username','rate','suggestion')
    search_fields = ('username',)
    fieldsets = (
        ['Main',{
            'fields':('username','suggestion'),
        }],
        ['Advance',{
            'classes': ('collapse',),
            'fields': ('rate',),
        }]
    )    
# Register your models here.
admin.site.register(Survey,SurveyAdmin)
admin.site.register(Diary,DiaryAdmin)
admin.site.register(Url,UrlAdmin)