from django import forms
from django.utils import timezone

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

    def __init__(self, *args, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields["preferred_date"].widget.attrs["min"] = (
            timezone.localdate().isoformat()
        )

    def clean_preferred_date(self):
        preferred_date = self.cleaned_data["preferred_date"]

        if preferred_date < timezone.localdate():
            raise forms.ValidationError("Please choose today or a future date.")

        return preferred_date


class ContactForm(forms.Form):
    full_name = forms.CharField(max_length=100)
    phone_number = forms.CharField(max_length=25, required=False)
    email = forms.EmailField()
    subject = forms.CharField(max_length=150, required=False)
    message = forms.CharField(max_length=3000, widget=forms.Textarea)
