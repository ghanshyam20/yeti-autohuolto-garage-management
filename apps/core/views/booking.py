from django.contrib import messages
from django.shortcuts import redirect, render

from apps.core.forms import BookingForm
from apps.core.services.email_service import (send_booking_confirmation,notify_garage_owner)


def booking(request):

    if request.method == "POST":

        form = BookingForm(request.POST)

        if form.is_valid():

            booking = form.save()

            send_booking_confirmation(booking)
            notify_garage_owner(booking)

            messages.success(
                request,
                "Thank you! Your booking request has been received. We'll contact you shortly.",
            )

            return redirect("booking")

    else:

        form = BookingForm()

    context = {
        "form": form,
    }

    return render(
        request,
        "core/booking.html",
        context,
    )