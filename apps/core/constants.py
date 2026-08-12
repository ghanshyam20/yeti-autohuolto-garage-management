# ==========================================
# BOOKING STATUS
# ==========================================

BOOKING_STATUS = (
    ("pending", "Pending"),
    ("confirmed", "Confirmed"),
    ("completed", "Completed"),
    ("cancelled", "Cancelled"),
)


# ==========================================
# SERVICE TYPES
# ==========================================

SERVICE_CHOICES = (
    ("routine_maintenance", "Routine Maintenance"),
    ("engine_diagnostics", "Engine Diagnostics"),
    ("brake_service", "Brake Service"),
    ("tire_service", "Tire Service"),
    ("battery_service", "Battery Service"),
    ("air_conditioning", "Air Conditioning"),
    ("suspension_steering", "Suspension & Steering"),
    ("other", "Other"),
)


# ==========================================
# PREFERRED TIME
# ==========================================

TIME_SLOT_CHOICES = (
    ("morning", "Morning (08:00–12:00)"),
    ("afternoon", "Afternoon (12:00–16:00)"),
    ("evening", "Evening (16:00–18:00)"),
    ("no_preference", "No Preference"),
)