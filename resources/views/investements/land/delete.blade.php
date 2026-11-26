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

    <div class="card land-page-card">
        <div class="card-header">
            <i class="ti-map me-2" aria-hidden="true"></i> Land information
        </div>
        <div class="card-body">
            <form action="{{ route('lands.destroy', $land->id) }}" method="POST">
                @csrf
                @method('DELETE')

                <p>Are you sure you want to delete the land: <strong>{{ $land->name }}</strong>?</p>

                <div class="land-form-footer">
                    <a href="{{ route('lands.index') }}" class="btn btn-light border">Cancel</a>
                    <button type="submit" class="btn btn-danger">
                        <i class="ti-trash me-1" aria-hidden="true"></i> Delete land
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection




