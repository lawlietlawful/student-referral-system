<!DOCTYPE html>
<html>
<head><title>Counselor Dashboard</title></head>
<body>
    <h1>Welcome, Guidance Counselor!</h1>
    <p>Logged in as: {{ auth()->user()->name }}</p>
    <a href="/logout">Logout</a>
</body>
</html>
