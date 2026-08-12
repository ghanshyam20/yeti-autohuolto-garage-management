from django import forms

from .models import Booking


class BookingForm(forms.ModelForm):

    email=forms.EmailField(
        required=True,
        widget=forms.EmailInput(
            attrs={
                "class": "form-control",
                "placeholder": "name@email.com",
            }
        )
    )

    class Meta:
        model = Booking

        fields = [
            "full_name",
            "phone_number",
            "email",
            "vehicle_make",
            "vehicle_model",
            "registration_number",
            "service_required",
            "problem_description",
            "preferred_date",
            "preferred_time",
        ]

        widgets = {

            "preferred_date": forms.DateInput(
                attrs={
                    "type": "date",
                }
            ),

            "problem_description": forms.Textarea(
                attrs={
                    "rows": 5,
                }
            ),
        }