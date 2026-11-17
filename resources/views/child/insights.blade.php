@extends('layouts.app')

@section('title', 'Insights')

@section('content')
    <h2 class="h3 fw-bold mb-1">My money insights</h2>
    <p class="text-secondary mb-4">See your money habits and get tips to spend smarter.</p>

    @include('partials.insights', ['baseUrl' => route('child.insights')])
@endsection