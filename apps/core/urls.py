from django.urls import path

from .views import *

urlpatterns = [
    path("", home, name="home"),
    path("services/", services, name="services"),
    path("booking/", booking, name="booking"),
    path("about/", about, name="about"),
    path("contact/", contact, name="contact"),
]

