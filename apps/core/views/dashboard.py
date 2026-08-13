from django.shortcuts import render
from django.contrib.auth.decorators import login_required
from apps.core.models import Booking




@login_required(login_url="owner_login")
def dashboard(request):

    context = {

        "total_bookings": Booking.objects.count(),

        "pending_bookings": Booking.objects.filter(
            status="pending"
        ).count(),

        "confirmed_bookings": Booking.objects.filter(
            status="confirmed"
        ).count(),

        "completed_bookings": Booking.objects.filter(
            status="completed"
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
