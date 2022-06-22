@extends('layouts.app')
@section('content')
<div class="container">
    <contact-cards :contacts="contacts" v-on:contactdelete="sendRemoveContact"></contact-cards>
    <user-cards :users="users" v-on:contactrequest="sendContactRequest"></user-cards>
</div>
@endsection
