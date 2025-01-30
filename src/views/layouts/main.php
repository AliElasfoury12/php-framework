<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/index.css">
    <title>{{$title}}</title>
</head>
<body>
    <header class="mb-5">
        @include('header')
    </header>
    <main>
        @content
    </main>
</body>
</html>