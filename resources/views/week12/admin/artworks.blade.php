@extends('layouts.week12')

@section('title', 'Artwork management')

@section('content')
<section class="container py-4 py-lg-5">
    <a class="w12-back-link" href="{{ route('week12.admin') }}"><i class="bi bi-arrow-left"></i> Back to admin overview</a>
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3 mt-3 mb-4">
        <div>
            <p class="w12-eyebrow mb-2">ADMIN / CONTENT</p>
            <h1 class="w12-section-title">Artwork management</h1>
            <p class="text-secondary mb-0">Review sample listings and demonstrate admin controls.</p>
        </div>
        <button type="button" class="btn w12-btn-primary" data-demo-action="Create listing is simulated; no listing was added."><i class="bi bi-plus-lg"></i> Add listing (demo)</button>
    </div>
    <div class="alert w12-alert" role="note">
        <i class="bi bi-info-circle-fill"></i>
        <span>Every row is sample data. Review and remove buttons do not modify a database.</span>
    </div>
    <section class="w12-panel">
        <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 mb-3">
            <h2 class="h5 mb-0">All sample listings <span class="w12-count">{{ count($artworks) }}</span></h2>
            <button type="button" class="btn w12-btn-outline btn-sm align-self-start" data-demo-action="Search is not connected to a data source."><i class="bi bi-search"></i> Search (demo)</button>
        </div>
        <div class="table-responsive">
            <table class="table w12-table align-middle mb-0">
                <thead><tr><th>Artwork</th><th>Creator</th><th>Category</th><th>Price</th><th>Status</th><th><span class="visually-hidden">Actions</span></th></tr></thead>
                <tbody>
                    @foreach ($artworks as $artwork)
                        <tr>
                            <td><div class="d-flex align-items-center gap-2"><img class="w12-table-thumb" src="{{ asset($artwork['image']) }}" alt=""><span class="fw-semibold">{{ $artwork['title'] }}</span></div></td>
                            <td>{{ $artwork['creator'] }}</td>
                            <td>{{ $artwork['category'] }}</td>
                            <td>{{ $artwork['price'] }} ETH</td>
                            <td><span class="w12-status @if($artwork['status'] === 'Review') w12-status-review @endif">{{ $artwork['status'] }}</span></td>
                            <td><div class="d-flex gap-2">
                                <a class="btn w12-btn-outline btn-sm" href="{{ route('week12.client.artwork', ['id' => $artwork['id']]) }}">Preview</a>
                                <button class="btn w12-btn-danger btn-sm" type="button" data-demo-action="Review action for {{ $artwork['title'] }} is simulated only.">Review</button>
                            </div></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</section>
@endsection
