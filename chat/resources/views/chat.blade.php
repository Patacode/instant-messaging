@extends('layouts.app')
@section('content')
<div class="container">
    <div class="card" id="contact-{{ $sclient['id'] }}">
        <div class="card-header">Chatting with {{ $sclient['name'] }} - <em>{{ $sclient['email'] }}</em></div>
        <div class="card-body">
            @foreach ($messages as $message)
                <ul class="chat">
                    <li class="left clearfix">
                        <div class="clearfix">
                            <div class="header"><strong>{{ $message['name'] }}</strong> <em>{{ $message['email'] }}</em></div>
                            <p id="{{ $message['id'] }}">{{ $message['message'] }}</p>
                        </div>
                    </li> 
                </ul>
            @endforeach     
        </div>
        <div class="card-footer">
            <chat-form v-on:messagesent="addMessage" :user="{{ Auth::user() }}"></chat-form>
        </div>
    </div>
</div>
@endsection

