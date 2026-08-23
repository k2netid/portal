<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Newsletter</title>
</head>
<body style="font-family: sans-serif; line-height: 1.5; color: #111;">
    <h1>Welcome!</h1>
    <p>Hi{{ $subscriber->name ? ' '.$subscriber->name : '' }},</p>
    <p>Thanks for subscribing to our newsletter. You'll receive updates at <strong>{{ $subscriber->email }}</strong>.</p>
</body>
</html>
