from django.core.mail import send_mail
from django.conf import settings


def send_booking_confirmation(booking):
    """
    Send confirmation email to customer after booking request.
    """

    subject = "Booking Request Received | Yeti Autohuolto"

    message = f"""
Hello {booking.full_name},

Thank you for choosing Yeti Autohuolto.

We have received your booking request successfully.

Booking Details
-----------------------
Vehicle:
{booking.vehicle_make} {booking.vehicle_model}

Requested Service:
{booking.get_service_required_display()}

Preferred Date:
{booking.preferred_date}

Preferred Time:
{booking.get_preferred_time_display()}

Current Status:
Pending

Our mechanic will review your request and contact you soon to confirm your appointment.

Thank you,

Yeti Autohuolto
Espoo, Finland
"""

    send_mail(
        subject=subject,
        message=message,
        from_email=settings.DEFAULT_FROM_EMAIL,
        recipient_list=[booking.email],
        fail_silently=False,
    )






def notify_garage_owner(booking):
    """
    Send notification email to garage owner.
    """

    subject = f"🚗 New Booking Request - {booking.full_name}"

    message = f"""
A new booking request has been received.

----------------------------------------
CUSTOMER
----------------------------------------

Name:
{booking.full_name}

Phone:
{booking.phone_number}

Email:
{booking.email}

----------------------------------------
VEHICLE
----------------------------------------

Make:
{booking.vehicle_make}

Model:
{booking.vehicle_model}

Registration:
{booking.registration_number}

----------------------------------------
SERVICE
----------------------------------------

Requested Service:
{booking.get_service_required_display()}

Problem Description:
{booking.problem_description}

Preferred Date:
{booking.preferred_date}

Preferred Time:
{booking.get_preferred_time_display()}

----------------------------------------
STATUS

Pending

----------------------------------------

Please log into the dashboard to review this booking.

Yeti Autohuolto
"""

    send_mail(
        subject=subject,
        message=message,
        from_email=settings.DEFAULT_FROM_EMAIL,
        recipient_list=[settings.GARAGE_OWNER_EMAIL],
        fail_silently=False,
    )