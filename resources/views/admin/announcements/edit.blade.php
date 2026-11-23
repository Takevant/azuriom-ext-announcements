@extends('admin.layouts.admin')

@include('elements.markdown-editor')

@section('title', trans('tkt-announcements::admin.announcements.edit', ['title' => $announcement->title]))

@section('content')
    <form action="{{ route('tkt-announcements.admin.announcements.update', $announcement) }}" method="POST">
        @method('PUT')

        @include('tkt-announcements::admin.announcements._form')

        <div class="mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> {{ trans('messages.actions.save') }}
            </button>
            <a href="{{ route('tkt-announcements.admin.announcements.destroy', $announcement) }}" class="btn btn-danger" data-confirm="delete">
                <i class="bi bi-trash"></i> {{ trans('messages.actions.delete') }}
            </a>
        </div>
    </form>
@endsection
