@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1>Record land purchase</h1><p class="text-muted mb-0">Enter the land value, area, and deed document.</p></div>
        <a href="{{ route('investments.land.index') }}" class="btn btn-light border">Back to land</a>
    </div>
    @if ($errors->any())<div class="alert alert-danger">Please review the highlighted fields.</div>@endif
    <div class="card"><div class="card-body">
        <form action="{{ route('investments.land.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @include('investements.land._form')
            <button type="submit" class="btn btn-primary">Save land investment</button>
        </form>
    </div></div>
</div>
@endsection
