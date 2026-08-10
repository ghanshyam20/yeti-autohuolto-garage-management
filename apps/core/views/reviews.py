from django.shortcuts import render


def reviews(request):
    return render(request, "core/reviews.html")