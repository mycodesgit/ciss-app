{{-- Any other 4xx (405, 413, 422, ...) --}}
@extends('errors.layout')

@section('code', $exception->getStatusCode())
@section('icon', 'alert-triangle')
@section('title', 'Request Error')
@section('message', 'This request could not be completed. Go back and try again.')
