import logging

from django.contrib import messages
from django.shortcuts import redirect, render

from apps.core.forms import BookingForm
from apps.core.services.email_service import (send_booking_confirmation,notify_garage_owner)


logger = logging.getLogger(__name__)


def booking(request):

    if request.method == "POST":

        form = BookingForm(request.POST)

        if form.is_valid():

            booking = form.save()

            email_failed = False

            for send_email in (notify_garage_owner, send_booking_confirmation):
                try:
                    send_email(booking)
                except Exception:
                    email_failed = True
                    logger.exception(
                        "Booking %s was saved, but an email could not be sent.",
                        booking.pk,
                    )

            if email_failed:
                messages.warning(
                    request,
                    "Your booking request was saved. If you do not receive an email, "
                    "please call +358 45 156 6199.",
                )
            else:
                messages.success(
                    request,
                    "Thank you! Your booking request has been received. "
                    "We'll contact you shortly.",
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
