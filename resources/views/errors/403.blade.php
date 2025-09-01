@extends('layouts.app')

@section('content')
<div class="dashboard-main-body">

    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
        <h6 class="fw-semibold mb-0">403</h6>
        <ul class="d-flex align-items-center gap-2">
            <li class="fw-medium">
                <a href="{{ url('/') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                    <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                    Dashboard
                </a>
            </li>
            <li>-</li>
            <li class="fw-medium">403</li>
        </ul>
    </div>

    <div class="card basic-data-table">
        <div class="card-body py-80 px-32 text-center">
            <img src="{{ asset('assets/images/error-img.png') }}" alt="Error Image" class="mb-24">
            <h6 class="mb-16">Access Forbidden</h6>
            <p class="text-secondary-light">You don't have permission to access this resource.</p>
            <a href="{{ url('/') }}" class="btn btn-primary-600 radius-8 px-20 py-11">Back to Home</a>
        </div>
    </div>
</div>
@endsection
