@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="member-page-header">
        <div>
            <h1>Edit member</h1>
            <p>Update this member's profile and membership details.</p>
        </div>
        <a href="{{ route('members.index') }}" class="btn btn-light border">
            <i class="ti-arrow-left me-1" aria-hidden="true"></i> Back to members
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <strong>Please review the form.</strong> Some details need your attention.
        </div>
    @endif

    <div class="card member-page-card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span><i class="ti-user me-2" aria-hidden="true"></i> Member information</span>
            <span class="badge bg-light text-secondary">{{ $member->member_code }}</span>
        </div>
        <div class="card-body">
            <form action="{{ route('members.update', $member) }}" method="POST">
                @method('PUT')
                @include('members.member_form')

                <div class="member-form-footer">
                    <a href="{{ route('members.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="ti-check me-1" aria-hidden="true"></i> Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection