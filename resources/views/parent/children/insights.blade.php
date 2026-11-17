@extends('layouts.app')

@section('title', $child->name.' · Insights')

@section('content')
    <a href="{{ route('parent.children.show', $child) }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to {{ \Illuminate\Support\Str::before($child->name, ' ') }}'s wallet
    </a>

    <h2 class="h3 fw-bold mt-3 mb-1">{{ $child->name }}'s spending insights</h2>
    <p class="text-secondary mb-4">Patterns, trends and suggestions based on {{ \Illuminate\Support\Str::before($child->name, ' ') }}'s real spending.</p>

    @include('partials.insights', ['baseUrl' => route('parent.children.insights', $child)])
@endsection