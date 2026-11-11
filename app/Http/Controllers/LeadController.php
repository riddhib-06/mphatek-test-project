<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LeadController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            // only live product pages (not unpublished or redirected ones) can receive leads
            'page_id' => ['required', Rule::exists('pages', 'id')->where(fn ($q) => $q
                ->where('type', 'product')
                ->where('is_published', true)
                ->whereNull('redirect_url'))],
            'name' => 'required|string|max:100',
            'phone' => ['required', 'regex:/^[6-9][0-9]{9}$/'],
            'email' => 'nullable|email|max:150',
        ], [
            'page_id.exists' => 'This product is not available for enquiries.',
            'phone.regex' => 'Please enter a valid 10 digit mobile number.',
        ]);

        $product = Page::findOrFail($data['page_id']);
        $source = $request->session()->get('lead_source', []);

        // Same phone for the same product within 24 hours = duplicate. Don't save it
        // and don't send generate_lead again, so the counts stay correct.
        $duplicate = Lead::where('phone', $data['phone'])
            ->where('page_id', $product->id)
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        if ($duplicate) {
            return back()->with('success', 'We already have your enquiry for '.$product->title.'. Our team will contact you shortly.');
        }

        Lead::create($data + [
            'product' => $product->title,
            'source' => $source['source'] ?? 'direct',
            'medium' => $source['medium'] ?? null,
            'campaign' => $source['campaign'] ?? null,
            'referrer' => $source['referrer'] ?? null,
            'landing_page' => $source['landing_page'] ?? null,
        ]);

        // Picked up by the layout and sent to GA4 as a generate_lead event
        return back()
            ->with('success', 'Thank you! Our team will contact you shortly.')
            ->with('lead_event', [
                'product_name' => $product->title,
                'product_category' => $product->category,
                'lead_source' => $source['source'] ?? 'direct',
                'lead_medium' => $source['medium'] ?? '(none)',
                'lead_campaign' => $source['campaign'] ?? '(none)',
            ]);
    }

    // Internal tool: build UTM tracking links for the live product pages
    public function utmBuilder()
    {
        $products = Page::where('type', 'product')
            ->where('is_published', true)
            ->whereNull('redirect_url')
            ->whereNull('canonical_url')
            ->orderBy('title')
            ->get();

        return view('leads.utm-builder', ['products' => $products]);
    }

    public function report()
    {
        $byProduct = Lead::selectRaw('product, count(*) as total')
            ->groupBy('product')
            ->orderByDesc('total')
            ->get();

        $bySource = Lead::selectRaw('source, medium, count(*) as total')
            ->groupBy('source', 'medium')
            ->orderByDesc('total')
            ->get();

        $byProductAndSource = Lead::selectRaw('product, source, count(*) as total')
            ->groupBy('product', 'source')
            ->orderBy('product')
            ->get();

        return view('leads.report', compact('byProduct', 'bySource', 'byProductAndSource'));
    }
}
