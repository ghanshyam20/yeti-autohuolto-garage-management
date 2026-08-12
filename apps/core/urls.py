from django.urls import path

from .views import *
from .views.auth import owner_login, owner_logout
from .views.dashboard import dashboard

urlpatterns = [
    path("", home, name="home"),
    path("services/", services, name="services"),
    path("booking/", booking, name="booking"),
    path("about/", about, name="about"),
    path("contact/", contact, name="contact"),
    path("dashboard/login/", owner_login, name="owner_login"),
    path("dashboard/logout/", owner_logout, name="owner_logout"),
    path("dashboard/", dashboard, name="dashboard"),
]

