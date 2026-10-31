@extends('errors.layout')

@section('code', '403')
@section('icon', 'lock')
@section('title', 'Access Denied')
{{-- abort(403, '...') messages (e.g. menu access) are shown as-is --}}
@section('message', $exception->getMessage() ?: 'You do not have permission to open this page.')
