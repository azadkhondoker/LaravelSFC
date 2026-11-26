@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div><h1>Edit other investment</h1><p class="text-muted mb-0">Update the asset and category details.</p></div>
        <a href="{{ route('investments.others.index') }}" class="btn btn-light border">Back to investments</a>
    </div>
    @if ($errors->any())<div class="alert alert-danger">Please review the highlighted fields.</div>@endif
    <div class="card"><div class="card-body">
        <form action="{{ route('investments.others.update', $investment) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('investements.others._form')
            <button type="submit" class="btn btn-primary">Save changes</button>
        </form>
    </div></div>
</div>
@endsection
