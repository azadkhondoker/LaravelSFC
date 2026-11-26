@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="mb-4"><h1>Edit loan client</h1><p class="text-muted mb-0">Update registered non-member client details.</p></div>
    @if ($errors->any())<div class="alert alert-danger">Please review the highlighted fields.</div>@endif
    <div class="card"><div class="card-body">
        <form action="{{ route('investments.clients.update', $client) }}" method="POST">
            @method('PUT')
            @include('investements.clients._form')
            <a href="{{ route('investments.clients.index') }}" class="btn btn-light border">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </form>
    </div></div>
</div>
@endsection
