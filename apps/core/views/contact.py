import logging

from django.conf import settings
from django.contrib import messages
from django.core.mail import EmailMessage
from django.shortcuts import redirect, render

from apps.core.forms import ContactForm


logger = logging.getLogger(__name__)


def contact(request):
    if request.method == "POST":
        form = ContactForm(request.POST)

        if form.is_valid():
            data = form.cleaned_data
            subject = (data["subject"] or "Website contact request").replace(
                "\n", " "
            ).replace("\r", " ")
            body = (
                "A new contact request was submitted on yetiautohuolto.fi.\n\n"
                f"Name: {data['full_name']}\n"
                f"Phone: {data['phone_number'] or '-'}\n"
                f"Email: {data['email']}\n\n"
                f"Message:\n{data['message']}"
            )

            try:
                EmailMessage(
                    subject=f"Website contact: {subject}",
                    body=body,
                    from_email=settings.DEFAULT_FROM_EMAIL,
                    to=[settings.GARAGE_OWNER_EMAIL],
                    reply_to=[data["email"]],
                ).send(fail_silently=False)
            except Exception:
                logger.exception("A website contact email could not be sent.")
                messages.error(
                    request,
                    "We could not send your message. Please call +358 45 156 6199.",
                )
            else:
                messages.success(
                    request,
                    "Thank you! Your message has been sent.",
                )
                return redirect("contact")
    else:
        form = ContactForm()

    return render(request, "core/contact.html", {"form": form})
