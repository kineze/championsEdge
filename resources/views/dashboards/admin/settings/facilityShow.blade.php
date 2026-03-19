@extends('layouts.admin.app')

@section('content')
    <admin-facility-detail facility-id="{{ $facility->id }}"></admin-facility-detail>
@endsection
