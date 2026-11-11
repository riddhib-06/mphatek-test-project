@php($gaId = config('services.ga4.measurement_id'))

@if ($gaId)
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
@endif
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }
    gtag('js', new Date());
    @if ($gaId)
        gtag('config', @json($gaId));
    @endif

    @if (isset($page) && $page->type === 'product')
        gtag('event', 'view_product', @json(['product_name' => $page->title, 'product_category' => $page->category]));
    @endif

    @if (session('lead_event'))
        gtag('event', 'generate_lead', @json(session('lead_event')));
    @endif
</script>
