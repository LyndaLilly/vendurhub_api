<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $product->name }} | {{ $product->user->profile->business_name ?? 'Vendor' }}</title>

    <!-- Open Graph tags -->
    <meta property="og:title" content="{{ $product->name }}" />
    <meta property="og:description" content="{{ Str::limit($product->description, 120) }} 
Vendor: {{ $product->user->profile->business_name ?? 'Vendor' }}" />
    
    @if($product->images->first())
        <meta property="og:image" content="{{ url('uploads/' . $product->images->first()->image_path) }}" />
    @endif

    <meta property="og:url" content="{{ url('/product/'.$product->slug) }}" />
    <meta property="og:type" content="product" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $product->name }}" />
    <meta name="twitter:description" content="{{ Str::limit($product->description, 120) }}" />
    @if($product->images->first())
        <meta name="twitter:image" content="{{ url('uploads/' . $product->images->first()->image_path) }}" />
    @endif

    <!-- Redirect to React SPA -->
    <meta http-equiv="refresh" content="0;url={{ url('/product/'.$product->slug) }}">
</head>
<body>
    <p>Redirecting… If you are not redirected, <a href="{{ url('/product/'.$product->slug) }}">click here</a>.</p>
</body>
</html>
