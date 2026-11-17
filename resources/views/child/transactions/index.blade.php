@extends('layouts.app')

@section('title', 'My history')

@section('content')
    <h2 class="h3 fw-bold mb-1">My money history</h2>
    <p class="text-secondary mb-4">See where your money came from and where it went.</p>

    @include('partials.transaction-history', [
        'indexUrl' => route('child.transactions.index'),
        'exportUrl' => route('child.transactions.export'),
    ])
@endsection