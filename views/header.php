<!DOCTYPE html>
<html>
<head>
    <title><?= isset($title) ? $title : 'Mon site'; ?></title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.5/css/bootstrap.min.css"
          integrity="sha384-AysaV+vQoT3kOAXZkl02PThvDr8HYKPZhNT5h/CXfBThSRXQ6jW5DO2ekP5ViFdi" crossorigin="anonymous">
    <style>
        body {
            padding-top: 5rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-fixed-top navbar-dark bg-inverse">
    <a class="navbar-brand" href="#">Title</a>
    <ul class="nav navbar-nav">
        <li class="nav-item active">
            <a class="nav-link" href="{{ path('blog.index' }}">Blog <span class="sr-only">(current)</span></a>
        </li>
    </ul>
</nav>

<div class="container">
