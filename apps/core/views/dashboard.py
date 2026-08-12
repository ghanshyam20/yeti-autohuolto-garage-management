from django.contrib import messages
from django.contrib.auth import authenticate, login
from django.shortcuts import render, redirect
from django.contrib.auth.decorators import login_required
from apps.core.models import Booking




@login_required(login_url="owner_login")
def dashboard(request):

    context = {

        "total_bookings": Booking.objects.count(),

        "pending_bookings": Booking.objects.filter(
            status="Pending"
        ).count(),

        "confirmed_bookings": Booking.objects.filter(
            status="Confirmed"
        ).count(),

        "completed_bookings": Booking.objects.filter(
            status="Completed"
        ).count(),

        "recent_bookings": Booking.objects.order_by(
            "-created_at"
        )[:5],

    }

    return render(
        request,
        "dashboard/dashboard.html",
        context,
    )



def owner_login(request):

    if request.method == "POST":

        username = request.POST.get("username")
        password = request.POST.get("password")

        user = authenticate(
            request,
            username=username,
            password=password,
        )

        if user is not None:

            login(request, user)

            return redirect("dashboard")

        messages.error(
            request,
            "Invalid username or password.",
        )

    return render(
        request,
        "dashboard/login.html",
    )