@extends('layouts.master')

@section('content')
<div class="container-fluid">
    <div class="land-page-header">
        <div>
            <h1>Delete land</h1>
            <p>Are you sure you want to delete this land? This action cannot be undone.</p>
        </div>
        <a href="{{ route('lands.index') }}" class="btn btn-light border">
            <i class="ti-arrow-left me-1" aria-hidden="true"></i> Back to lands
        </a>
    </div>


    <form action="{{ route('lands.destroy', $land->id) }}" method="POST">
        @csrf
        @method('DELETE')

        <div class="form-group">
            <label for="name">Land Name</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $land->name }}" readonly>
        </div>

        <div class="form-group">
            <label for="location">Location</label>
            <input type="text" class="form-control" id="location" name="location" value="{{ $land->location }}" readonly>
        </div>

        <button type="submit" class="btn btn-danger">Delete Land</button>
        <a href="{{ route('lands.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection







