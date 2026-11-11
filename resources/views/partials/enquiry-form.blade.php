<section class="box enquiry">
    <h2>Interested in {{ $product->title }}?</h2>
    <p>Leave your details and we will call you back.</p>

    <form method="POST" action="{{ route('leads.store') }}">
        @csrf
        <input type="hidden" name="page_id" value="{{ $product->id }}">

        <label>
            Full Name
            <input type="text" name="name" value="{{ old('name') }}" required>
        </label>
        @error('name') <p class="error">{{ $message }}</p> @enderror

        <label>
            Mobile Number
            <input type="tel" name="phone" value="{{ old('phone') }}" maxlength="10" required>
        </label>
        @error('phone') <p class="error">{{ $message }}</p> @enderror

        <label>
            Email (optional)
            <input type="email" name="email" value="{{ old('email') }}">
        </label>
        @error('email') <p class="error">{{ $message }}</p> @enderror

        <button type="submit">Submit Enquiry</button>
    </form>
</section>
