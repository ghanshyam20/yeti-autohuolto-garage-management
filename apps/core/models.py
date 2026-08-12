from django.db import models

# Create your models here.
from django.db import models

from .constants import (
    BOOKING_STATUS,
    SERVICE_CHOICES,
    TIME_SLOT_CHOICES,
)


class Booking(models.Model):
    """
    Stores a customer's booking request.
    """

    full_name = models.CharField(max_length=100)

    phone_number = models.CharField(max_length=25)

    email = models.EmailField()
    

    vehicle_make = models.CharField(max_length=100)

    vehicle_model = models.CharField(max_length=100)

    registration_number = models.CharField(
        max_length=20,
        blank=True,
    )

    service_required = models.CharField(
        max_length=50,
        choices=SERVICE_CHOICES,
    )

    problem_description = models.TextField(
        blank=True,
    )

    preferred_date = models.DateField()

    preferred_time = models.CharField(
        max_length=30,
        choices=TIME_SLOT_CHOICES,
        default="no_preference",
    )

    status = models.CharField(
        max_length=20,
        choices=BOOKING_STATUS,
        default="pending",
    )

    created_at = models.DateTimeField(
        auto_now_add=True,
    )

    updated_at = models.DateTimeField(
        auto_now=True,
    )

    class Meta:
        ordering = ["-created_at"]

    def __str__(self):
        return (
            f"{self.full_name} - "
            f"{self.vehicle_make} "
            f"{self.vehicle_model}"
        )