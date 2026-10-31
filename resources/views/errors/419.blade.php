@extends('errors.layout')

@section('code', '419')
@section('icon', 'clock-hour-4')
@section('title', 'Page Expired')
@section('message', 'Your session has expired. Reload the page and try again.')

@section('actions')
    <button type="button" class="btn btn-success btn-sm" onclick="location.reload()">
        <i class="ti ti-refresh"></i> Reload
    </button>
    @parent
@endsection
