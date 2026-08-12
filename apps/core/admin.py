from django.contrib import admin

from .models import Booking


@admin.register(Booking)
class BookingAdmin(admin.ModelAdmin):

    list_display = (
        "full_name",
        "vehicle_make",
        "vehicle_model",
        "service_required",
        "preferred_date",
        "status",
        "created_at",
    )

    list_filter = (
        "status",
        "service_required",
        "preferred_date",
    )

    search_fields = (
        "full_name",
        "phone_number",
        "vehicle_make",
        "vehicle_model",
        "registration_number",
    )

    ordering = ("-created_at",)

    readonly_fields = (
        "created_at",
        "updated_at",
    )

    list_per_page = 20

    fieldsets = (
        (
            "Customer Information",
            {
                "fields": (
                    "full_name",
                    "phone_number",
                    "email",
                )
            },
        ),
        (
            "Vehicle Information",
            {
                "fields": (
                    "vehicle_make",
                    "vehicle_model",
                    "registration_number",
                )
            },
        ),
        (
            "Booking Information",
            {
                "fields": (
                    "service_required",
                    "problem_description",
                    "preferred_date",
                    "preferred_time",
                    "status",
                )
            },
        ),
        (
            "System Information",
            {
                "fields": (
                    "created_at",
                    "updated_at",
                )
            },
        ),
    )