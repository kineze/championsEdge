@extends('layouts.site.app')

@section('content')
    <training-session-purchase-page session-id="{{ $trainingSession->id }}"></training-session-purchase-page>
@endsection
