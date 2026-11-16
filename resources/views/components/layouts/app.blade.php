@props([
    'title' => 'Dashboard',
    'breadcrumbs' => [],
])
@include('layouts.app', ['title' => $title, 'breadcrumbs' => $breadcrumbs, 'slot' => $slot])
