@extends('layouts.app')
@section('content')
<div class="container">
    <h1>An error occured</h1>
    <p><span class="text-danger">Cause</span>: {{ $message }}</p>
</div>
@endsection

