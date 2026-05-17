@extends('layouts.master')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-xxl-8 mb-6 order-0">
        <div class="card">
            <div class="d-flex align-items-start row">
                <div class="col-sm-7">
                    <div class="card-body">
                        <h5 class="card-title text-primary mb-3">Selamat Datang! 🎉</h5>
                        <p class="mb-6">
                            Sistem Maklumat Geografi MBPJ. Anda telah berjaya log masuk ke dalam sistem.
                        </p>
                        <a href="javascript:;" class="btn btn-sm btn-outline-primary">Lihat Peta</a>
                    </div>
                </div>
                <div class="col-sm-5 text-center text-sm-left">
                    <div class="card-body pb-0 px-0 px-md-6">
                        <img src="{{ asset('assets/img/illustrations/man-with-laptop.png') }}" height="175"
                            class="scaleX-n1-rtl" alt="Dashboard illustration" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="col-xxl-4 col-lg-6 col-md-6 order-1">
        <div class="row">
            <div class="col-lg-6 col-md-12 col-6 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-primary">
                                    <i class="icon-base ti tabler-map icon-28px"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mb-1">Lapisan Peta</p>
                        <h4 class="card-title mb-0">0</h4>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-12 col-6 mb-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-4">
                            <div class="avatar flex-shrink-0">
                                <span class="avatar-initial rounded bg-label-success">
                                    <i class="icon-base ti tabler-alert-triangle icon-28px"></i>
                                </span>
                            </div>
                        </div>
                        <p class="mb-1">Insiden</p>
                        <h4 class="card-title mb-0">0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
