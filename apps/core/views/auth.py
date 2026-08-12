from django.contrib.auth import authenticate, login, logout
from django.shortcuts import render, redirect


def owner_login(request):

    if request.user.is_authenticated:
        return redirect("dashboard")

    if request.method == "POST":

        username = request.POST.get("username")
        password = request.POST.get("password")

        user = authenticate(
            request,
            username=username,
            password=password,
        )

        if user is not None:
            login(request, user)
            return redirect("dashboard")

        return render(
            request,
            "dashboard/login.html",
            {
                "error": "Invalid email or password.",
            },
        )

    return render(
        request,
        "dashboard/login.html",
    )


def owner_logout(request):

    logout(request)

    return redirect("owner_login")