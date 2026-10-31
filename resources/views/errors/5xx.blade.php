{{-- Any other 5xx (502, 504, ...) --}}
@extends('errors.layout')

@section('code', $exception->getStatusCode())
@section('icon', 'server-off')
@section('title', 'Server Error')
@section('message', 'Something went wrong on our end. Please try again, or contact the MIS Office if it keeps happening.')
