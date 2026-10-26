@extends('layouts.app')
@section('content')
    <h1>Place an Order</h1>
    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif
    <form method="POST" action="/order">
        @csrf
        <p><label>Name: <input name="name" value="{{ old('name') }}" required></label></p>
        <p><label>Item:
            <select name="item">
                <option>Pizza</option>
                <option>Burger</option>
                <option>Pasta</option>
            </select>
        </label></p>
        <p><label>Quantity: <input type="number" name="qty" value="1" min="1" max="20" required></label></p>
        <button type="submit">Place Order</button>
    </form>
@endsection