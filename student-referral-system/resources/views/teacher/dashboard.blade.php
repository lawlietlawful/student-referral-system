<!DOCTYPE html>
<html>
<head><title>Teacher Dashboard</title></head>
<body>
    <h1>Welcome, Teacher!</h1>
    <p>Logged in as: {{ auth()->user()->name }}</p>
    <a href="/logout">Logout</a>
</body>
</html>
