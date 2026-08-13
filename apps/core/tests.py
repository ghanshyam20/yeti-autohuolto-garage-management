from datetime import timedelta
from unittest.mock import patch

from django.contrib.auth import get_user_model
from django.core import mail
from django.test import TestCase, override_settings
from django.urls import reverse
from django.utils import timezone

from apps.core.models import Booking


EMAIL_TEST_SETTINGS = {
    "ALLOWED_HOSTS": ["testserver"],
    "EMAIL_BACKEND": "django.core.mail.backends.locmem.EmailBackend",
    "DEFAULT_FROM_EMAIL": "info@yetiautohuolto.fi",
    "GARAGE_OWNER_EMAIL": "info@yetiautohuolto.fi",
}


@override_settings(**EMAIL_TEST_SETTINGS)
class PublicPageTests(TestCase):
    def test_public_pages_load(self):
        for name in ("home", "services", "booking", "about", "contact"):
            with self.subTest(name=name):
                response = self.client.get(reverse(name))
                self.assertEqual(response.status_code, 200)


@override_settings(**EMAIL_TEST_SETTINGS)
class BookingTests(TestCase):
    def booking_data(self, **overrides):
        data = {
            "full_name": "Test Customer",
            "phone_number": "+358451234567",
            "email": "customer@example.com",
            "vehicle_make": "Toyota",
            "vehicle_model": "Corolla",
            "registration_number": "ABC-123",
            "service_required": "routine_maintenance",
            "problem_description": "Annual service",
            "preferred_date": (timezone.localdate() + timedelta(days=2)).isoformat(),
            "preferred_time": "morning",
        }
        data.update(overrides)
        return data

    def test_booking_is_saved_and_sends_both_emails(self):
        response = self.client.post(reverse("booking"), self.booking_data())

        self.assertRedirects(response, reverse("booking"))
        self.assertEqual(Booking.objects.count(), 1)
        self.assertEqual(len(mail.outbox), 2)
        self.assertIn("info@yetiautohuolto.fi", mail.outbox[0].to)
        self.assertIn("customer@example.com", mail.outbox[1].to)

    def test_past_booking_date_is_rejected(self):
        yesterday = (timezone.localdate() - timedelta(days=1)).isoformat()

        response = self.client.post(
            reverse("booking"),
            self.booking_data(preferred_date=yesterday),
        )

        self.assertEqual(response.status_code, 200)
        self.assertContains(response, "Please choose today or a future date.")
        self.assertEqual(Booking.objects.count(), 0)

    @patch(
        "apps.core.views.booking.send_booking_confirmation",
        side_effect=RuntimeError("SMTP unavailable"),
    )
    @patch(
        "apps.core.views.booking.notify_garage_owner",
        side_effect=RuntimeError("SMTP unavailable"),
    )
    def test_email_error_does_not_lose_saved_booking(self, owner_mail, customer_mail):
        response = self.client.post(reverse("booking"), self.booking_data())

        self.assertRedirects(response, reverse("booking"))
        self.assertEqual(Booking.objects.count(), 1)
        owner_mail.assert_called_once()
        customer_mail.assert_called_once()


@override_settings(**EMAIL_TEST_SETTINGS)
class ContactTests(TestCase):
    def test_contact_form_sends_email_to_owner(self):
        response = self.client.post(
            reverse("contact"),
            {
                "full_name": "Test Customer",
                "phone_number": "+358451234567",
                "email": "customer@example.com",
                "subject": "Service question",
                "message": "Can you service my car next week?",
            },
        )

        self.assertRedirects(response, reverse("contact"))
        self.assertEqual(len(mail.outbox), 1)
        self.assertEqual(mail.outbox[0].to, ["info@yetiautohuolto.fi"])
        self.assertEqual(mail.outbox[0].reply_to, ["customer@example.com"])


@override_settings(ALLOWED_HOSTS=["testserver"])
class DashboardTests(TestCase):
    def setUp(self):
        self.user = get_user_model().objects.create_user(
            username="owner",
            password="test-password-123",
        )

    def create_booking(self, status):
        return Booking.objects.create(
            full_name=f"{status} customer",
            phone_number="+358451234567",
            email=f"{status}@example.com",
            vehicle_make="Toyota",
            vehicle_model="Corolla",
            service_required="routine_maintenance",
            preferred_date=timezone.localdate() + timedelta(days=2),
            preferred_time="morning",
            status=status,
        )

    def test_dashboard_status_counts_use_database_values(self):
        self.create_booking("pending")
        self.create_booking("confirmed")
        self.create_booking("completed")
        self.client.force_login(self.user)

        response = self.client.get(reverse("dashboard"))

        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.context["total_bookings"], 3)
        self.assertEqual(response.context["pending_bookings"], 1)
        self.assertEqual(response.context["confirmed_bookings"], 1)
        self.assertEqual(response.context["completed_bookings"], 1)
