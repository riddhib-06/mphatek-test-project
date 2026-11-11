@extends('layouts.app')

@section('title', 'UTM Builder | '.config('app.name'))
@section('body_class', 'admin')

@section('meta')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    @include('admin.nav')

    <h1>UTM Link Builder</h1>
    <p class="lead">Make a tracking link for a product. Put it in an ad, email or post, and every enquiry
        from that link is saved with its source in the <a href="{{ route('admin.leads') }}">Lead Report</a>.</p>

    <div class="calculator">
        <form class="box" id="utm-form" onsubmit="return false">
            <label>
                Product
                <select name="product">
                    @foreach ($products as $product)
                        <option value="{{ $product->url() }}">{{ $product->title }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                Source <span class="note">(where the visitor comes from)</span>
                <select name="source">
                    <option value="google" data-medium="cpc">Google</option>
                    <option value="facebook" data-medium="social">Facebook</option>
                    <option value="instagram" data-medium="social">Instagram</option>
                    <option value="youtube" data-medium="video">YouTube</option>
                    <option value="whatsapp" data-medium="sms">WhatsApp</option>
                    <option value="newsletter" data-medium="email">Email newsletter</option>
                    <option value="other">Other...</option>
                </select>
            </label>
            <input type="text" name="source_other" placeholder="e.g. times-of-india" hidden>

            <label>
                Medium <span class="note">(type of traffic)</span>
                <select name="medium">
                    <option value="cpc">cpc - paid ad</option>
                    <option value="social">social - social media post</option>
                    <option value="video">video</option>
                    <option value="email">email</option>
                    <option value="sms">sms / message</option>
                    <option value="banner">banner - display ad</option>
                    <option value="">(none)</option>
                </select>
            </label>

            <label>
                Campaign name <span class="note">(optional)</span>
                <input type="text" name="campaign" placeholder="e.g. diwali-offer">
            </label>
        </form>

        <div class="box result">
            <label for="utm-url">Your tracking link</label>
            <textarea id="utm-url" rows="3" readonly></textarea>
            <p class="error" id="utm-error"></p>
            <div class="row actions">
                <button type="button" id="utm-copy">Copy link</button>
                <a id="utm-open" href="#" target="_blank" rel="noopener">Open link &rarr;</a>
            </div>
            <p class="note">Values are saved in lowercase, so "Facebook" and "facebook" count as one source.</p>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            var form = document.getElementById('utm-form');
            var output = document.getElementById('utm-url');
            var openLink = document.getElementById('utm-open');
            var error = document.getElementById('utm-error');

            // "Diwali Offer!" -> "diwali-offer"
            function clean(value) {
                return value.trim().toLowerCase().replace(/[^a-z0-9._-]+/g, '-').replace(/^-+|-+$/g, '').slice(0, 100);
            }

            function tags() {
                var source = form.source.value === 'other' ? clean(form.source_other.value) : form.source.value;
                var params = new URLSearchParams();
                if (source) params.set('utm_source', source);
                if (form.medium.value) params.set('utm_medium', form.medium.value);
                if (clean(form.campaign.value)) params.set('utm_campaign', clean(form.campaign.value));
                return source ? params.toString() : '';
            }

            function update() {
                form.source_other.hidden = form.source.value !== 'other';
                var query = tags();
                error.textContent = query ? '' : 'Enter a source.';

                var url = query ? form.product.value + '?' + query : '';
                output.value = url;
                openLink.href = url || '#';
            }

            function copy(text) {
                if (!text) {
                    toastr.warning('Enter a source first.');
                    return;
                }
                if (!navigator.clipboard) {
                    toastr.error('Copy is not available here. Select the link and press Ctrl+C.');
                    return;
                }
                navigator.clipboard.writeText(text).then(function () {
                    toastr.success('Link copied to clipboard.');
                }, function () {
                    toastr.error('Could not copy. Select the link and press Ctrl+C.');
                });
            }

            // picking a source suggests the usual medium for it
            form.source.addEventListener('change', function () {
                var medium = form.source.selectedOptions[0].dataset.medium;
                if (medium) form.medium.value = medium;
            });

            form.addEventListener('input', update);
            form.addEventListener('change', update);

            document.getElementById('utm-copy').addEventListener('click', function () {
                copy(output.value);
            });

            update();
        })();
    </script>
@endpush
