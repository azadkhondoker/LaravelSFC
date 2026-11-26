@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1>Enroll a loan</h1><p class="text-muted mb-0">Choose a member or a previously registered client.</p></div>
        <a href="{{ route('investments.loans.index') }}" class="btn btn-light border">Back to loans</a>
    </div>
    @if ($errors->any())<div class="alert alert-danger">Please review the highlighted fields.</div>@endif
    <div class="card"><div class="card-body">
        <form action="{{ route('investments.loans.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('investements.loans._form')
            <button type="submit" class="btn btn-primary">Enroll loan</button>
        </form>
    </div></div>
</div>
@endsection
