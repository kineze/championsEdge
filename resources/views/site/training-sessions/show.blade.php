@extends('layouts.site.app')

@section('content')
    <training-session-detail session-id="{{ $trainingSession->id }}"></training-session-detail>
@endsection
