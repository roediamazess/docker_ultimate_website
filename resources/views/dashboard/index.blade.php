@extends('layouts.app')

@section('content')
<div class="dashboard-main-body">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-24">
    <h6 class="fw-semibold mb-0">Dashboard</h6>
    <ul class="d-flex align-items-center gap-2">
        <li class="fw-medium">
            <a href="{{ url('/') }}" class="d-flex align-items-center gap-1 hover-text-primary">
                <iconify-icon icon="solar:home-smile-angle-outline" class="icon text-lg"></iconify-icon>
                Home
            </a>
        </li>
        <li>-</li>
        <li class="fw-medium">Dashboard</li>
    </ul>
</div>

<div class="row row-cols-xxxl-5 row-cols-lg-3 row-cols-sm-2 row-cols-1 gy-4">
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-1 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Users</p>
                        <h6 class="mb-0">{{ number_format($user_count) }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-cyan rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="gridicons:multiple-users" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
                <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center gap-1 text-success-main">
                        <iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon> +{{ number_format($user_count * 0.1) }}
                    </span>
                    Last 30 days users
                </p>
            </div>
        </div><!-- card end -->
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-2 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Customers</p>
                        <h6 class="mb-0">{{ number_format($customer_count) }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-purple rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="fa-solid:award" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
                <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center gap-1 text-success-main">
                        <iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon> +{{ number_format($customer_count * 0.1) }}
                    </span>
                    Last 30 days customers
                </p>
            </div>
        </div><!-- card end -->
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-3 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Projects</p>
                        <h6 class="mb-0">{{ number_format($project_count) }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-info rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="fluent:people-20-filled" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
                <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center gap-1 text-success-main">
                        <iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon> +{{ number_format($project_count * 0.1) }}
                    </span>
                    Last 30 days projects
                </p>
            </div>
        </div><!-- card end -->
    </div>
    <div class="col">
        <div class="card shadow-none border bg-gradient-start-4 h-100">
            <div class="card-body p-20">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <p class="fw-medium text-primary-light mb-1">Total Activities</p>
                        <h6 class="mb-0">{{ number_format($activity_count) }}</h6>
                    </div>
                    <div class="w-50-px h-50-px bg-success-main rounded-circle d-flex justify-content-center align-items-center">
                        <iconify-icon icon="solar:wallet-bold" class="text-white text-2xl mb-0"></iconify-icon>
                    </div>
                </div>
                <p class="fw-medium text-sm text-primary-light mt-12 mb-0 d-flex align-items-center gap-2">
                    <span class="d-inline-flex align-items-center gap-1 text-success-main">
                        <iconify-icon icon="bxs:up-arrow" class="text-xs"></iconify-icon> +{{ number_format($activity_count * 0.1) }}
                    </span>
                    Last 30 days activities
                </p>
            </div>
        </div><!-- card end -->
    </div>
</div>

<!-- Recent Activities Section -->
@if($recent_activities->count() > 0)
<div class="row gy-4 mt-4">
    <div class="col-xxl-6 col-xl-12">
        <div class="card h-100">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">Recent Activities</h6>
            </div>
            <div class="card-body p-24">
                @foreach($recent_activities as $activity)
                <div class="d-flex align-items-center justify-content-between gap-3 mb-12 pb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="w-40-px h-40-px rounded-circle d-flex justify-content-center align-items-center bg-success-focus text-success-main radius-8 flex-shrink-0">
                            <iconify-icon icon="solar:calendar-outline" class="icon text-lg"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="text-md fw-semibold mb-0">{{ $activity->activity_name ?? 'Activity' }}</h6>
                            <span class="text-sm text-secondary-light fw-medium">{{ $activity->project_name ?? 'Project' }}</span>
                        </div>
                    </div>
                    <span class="text-sm text-secondary-light fw-normal">{{ $activity->created_at ?? now() }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Recent Users Section -->
    <div class="col-xxl-6 col-xl-12">
        <div class="card h-100">
            <div class="card-header border-bottom bg-base py-16 px-24">
                <h6 class="text-lg fw-semibold mb-0">Recent Users</h6>
            </div>
            <div class="card-body p-24">
                @foreach($recent_users as $user)
                <div class="d-flex align-items-center justify-content-between gap-3 mb-12 pb-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="w-40-px h-40-px rounded-circle d-flex justify-content-center align-items-center bg-info-focus text-info-main radius-8 flex-shrink-0">
                            <iconify-icon icon="solar:user-outline" class="icon text-lg"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="text-md fw-semibold mb-0">{{ $user->name ?? $user->display_name ?? 'User' }}</h6>
                            <span class="text-sm text-secondary-light fw-medium">{{ $user->email ?? '' }}</span>
                        </div>
                    </div>
                    <span class="text-sm text-secondary-light fw-normal">{{ $user->created_at ?? now() }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/homeOneChart.js') }}"></script>
@endsection
