@extends('admin.layouts.admin')

@include('elements.markdown-editor')

@section('title', trans('tkt-announcements::admin.announcements.create'))

@section('content')
    <form action="{{ route('tkt-announcements.admin.announcements.store') }}" method="POST">
        @include('tkt-announcements::admin.announcements._form')

        <button type="submit" class="btn btn-primary mb-4">
            <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
        </button>
    </form>
@endsection
