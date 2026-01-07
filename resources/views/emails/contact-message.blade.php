<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<h2>New Contact Message</h2>

<p><strong>Name:</strong> {{ $contact->name }}</p>
<p><strong>Email:</strong> {{ $contact->email }}</p>
<p><strong>Subject:</strong> {{ $contact->subject }}</p>

<p><strong>Message:</strong></p>
<p>{{ $contact->message }}</p>

    
</body>
</html>