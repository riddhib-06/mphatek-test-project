<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            // Products
            ['type' => 'product', 'slug' => 'fixed-deposit', 'title' => 'Fixed Deposit', 'category' => 'Deposits',
                'summary' => 'Earn assured returns with flexible tenures from 7 days to 10 years.'],
            ['type' => 'product', 'slug' => 'savings-account', 'title' => 'Savings Account', 'category' => 'Accounts',
                'summary' => 'Zero balance savings account with free digital banking.'],
            ['type' => 'product', 'slug' => 'home-loan', 'title' => 'Home Loan', 'category' => 'Loans',
                'summary' => 'Home loans at attractive interest rates with quick approval.'],
            ['type' => 'product', 'slug' => 'personal-loan', 'title' => 'Personal Loan', 'category' => 'Loans',
                'summary' => 'Instant personal loans with minimal documentation.'],
            ['type' => 'product', 'slug' => 'gold-loan', 'title' => 'Gold Loan', 'category' => 'Loans',
                'summary' => 'Get funds against your gold in under 30 minutes.'],

            // Blogs
            ['type' => 'blog', 'slug' => 'fd-vs-sip', 'title' => 'FD vs SIP: Which one is right for you?',
                'summary' => 'A simple comparison of safety, returns and liquidity.'],
            ['type' => 'blog', 'slug' => 'tips-to-improve-credit-score', 'title' => '5 Tips to Improve Your Credit Score',
                'summary' => 'Small habits that make a big difference to your loan eligibility.'],

            // Branches
            ['type' => 'branch', 'slug' => 'pune-shivajinagar', 'title' => 'Pune - Shivajinagar Branch',
                'summary' => 'FC Road, Shivajinagar, Pune 411005. Mon-Sat, 10 AM - 4 PM.'],
            ['type' => 'branch', 'slug' => 'mumbai-andheri', 'title' => 'Mumbai - Andheri East Branch',
                'summary' => 'Near Andheri Station, Mumbai 400069. Mon-Sat, 10 AM - 4 PM.'],

            // Static pages
            ['type' => 'static', 'slug' => 'home', 'title' => 'Home'],
            ['type' => 'static', 'slug' => 'about-us', 'title' => 'About Us',
                'summary' => 'Serving customers for over 25 years.'],
            ['type' => 'static', 'slug' => 'contact-us', 'title' => 'Contact Us',
                'summary' => 'Call us at 1800-123-4567 or write to care@example.com.'],
            ['type' => 'static', 'slug' => 'privacy-policy', 'title' => 'Privacy Policy'],

            // Calculators
            ['type' => 'calculator', 'slug' => 'fd-calculator', 'title' => 'FD Calculator',
                'summary' => 'Find out the maturity value of your fixed deposit.'],
            ['type' => 'calculator', 'slug' => 'sip-calculator', 'title' => 'SIP Calculator',
                'summary' => 'Estimate the future value of your SIP investments.'],

            // These should NOT show up in the sitemap
            ['type' => 'static', 'slug' => 'thank-you', 'title' => 'Thank You', 'noindex' => true],
            ['type' => 'product', 'slug' => 'car-loan-old', 'title' => 'Car Loan (old page)',
                'redirect_url' => '/products/personal-loan', 'redirect_code' => 301],
            ['type' => 'blog', 'slug' => 'diwali-offer', 'title' => 'Diwali Offer (temporary)',
                'redirect_url' => '/products/home-loan', 'redirect_code' => 302],
            ['type' => 'blog', 'slug' => 'festive-offer-2024', 'title' => 'Festive Offer 2024', 'is_published' => false],
            ['type' => 'product', 'slug' => 'fd', 'title' => 'FD',
                'canonical_url' => '/products/fixed-deposit', 'category' => 'Deposits'],
        ];

        // canonical_url is stored as a path (/products/...) so it works on any domain
        foreach ($pages as $page) {
            $page['content'] ??= '<p>'.($page['summary'] ?? $page['title']).'</p>';

            Page::updateOrCreate(['type' => $page['type'], 'slug' => $page['slug']], $page);
        }
    }
}
