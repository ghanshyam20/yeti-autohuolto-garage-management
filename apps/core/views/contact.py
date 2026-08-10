from django.shortcuts import render


def contact(request):
    return render(request, "core/contact.html")