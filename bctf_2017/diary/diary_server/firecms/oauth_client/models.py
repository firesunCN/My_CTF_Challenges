# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models
from django.contrib.auth.models import User
from django.db.models.signals import post_save
from django.dispatch import receiver

# Create your models here.

class Url(models.Model):
    url = models.URLField()
    is_read = models.BooleanField(default=False)

class Diary(models.Model):
    username = models.CharField(max_length=100, default='')
    content = models.CharField(max_length=1000, default='')
	
class Survey(models.Model):
    username = models.CharField(max_length=100, default='')
    rate = models.IntegerField(default=5)
    suggestion = models.CharField(max_length=2000, default='')