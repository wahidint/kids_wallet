@extends('layouts.app')

@section('title', $child->name.' · History')

@section('content')
    <a href="{{ route('parent.children.show', $child) }}" class="text-secondary text-decoration-none small">
        <i class="bi bi-arrow-left"></i> Back to {{ \Illuminate\Support\Str::before($child->name, ' ') }}'s wallet
    </a>

    <h2 class="h3 fw-bold mt-3 mb-1">{{ $child->name }}'s history</h2>
    <p class="text-secondary mb-4">Filter by type, category and date, or download it as a spreadsheet.</p>

    @include('partials.transaction-history', [
        'indexUrl' => route('parent.children.transactions.index', $child),
        'exportUrl' => route('parent.children.transactions.export', $child),
    ])
@endsection