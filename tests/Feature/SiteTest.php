<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\Page;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_pages_load(): void
    {
        $this->get('/')->assertOk();
        $this->get('/products/home-loan')->assertOk()->assertSee('Submit Enquiry');
        $this->get('/calculators/fd-calculator')->assertOk();
        $this->get('/calculators/sip-calculator')->assertOk();
        $this->get('/about-us')->assertOk();
        $this->get('/blog/festive-offer-2024')->assertNotFound();
        $this->get('/products/car-loan-old')->assertRedirect('/products/personal-loan');
    }

    public function test_fd_rate_is_picked_from_rate_table(): void
    {
        $this->postJson('/calculators/fd-calculator', [
            'amount' => 100000,
            'tenure' => 1,
            'tenure_unit' => 'years',
            'customer_type' => 'senior',
            'rate_mode' => 'auto',
            'payout' => 'cumulative',
        ])->assertOk()->assertJson(['rate' => 7.30, 'days' => 365]);
    }

    public function test_fd_quarterly_payout_needs_three_months(): void
    {
        $this->postJson('/calculators/fd-calculator', [
            'amount' => 100000,
            'tenure' => 30,
            'tenure_unit' => 'days',
            'customer_type' => 'general',
            'rate_mode' => 'manual',
            'rate' => 7,
            'payout' => 'quarterly',
        ])->assertStatus(422);
    }

    public function test_sip_calculation_endpoint(): void
    {
        $this->postJson('/calculators/sip-calculator', [
            'amount' => 5000,
            'frequency' => 'monthly',
            'duration' => 1,
            'duration_unit' => 'years',
            'rate' => 12,
        ])->assertOk()->assertJson(['future_value' => 64046.64]);
    }

    public function test_lead_is_saved_with_product_and_utm_source(): void
    {
        $product = Page::where('slug', 'home-loan')->first();

        $this->get('/products/home-loan?utm_source=google&utm_medium=cpc&utm_campaign=diwali');

        $this->post('/enquiry', [
            'page_id' => $product->id,
            'name' => 'Rahul Patil',
            'phone' => '9876543210',
        ])->assertRedirect()->assertSessionHas('lead_event.product_name', 'Home Loan');

        $lead = Lead::first();
        $this->assertEquals('Home Loan', $lead->product);
        $this->assertEquals('google', $lead->source);
        $this->assertEquals('cpc', $lead->medium);
        $this->assertEquals('diwali', $lead->campaign);
    }

    private function enquire(string $phone = '9876543210', string $slug = 'home-loan')
    {
        return $this->post('/enquiry', [
            'page_id' => Page::where('slug', $slug)->value('id'),
            'name' => 'Rahul Patil',
            'phone' => $phone,
        ]);
    }

    public function test_long_or_mixed_case_utm_tags_are_cleaned(): void
    {
        $this->get('/products/home-loan?utm_source=%20FaceBook%20&utm_campaign='.str_repeat('a', 300));
        $this->enquire()->assertRedirect();

        $lead = Lead::first();
        $this->assertEquals('facebook', $lead->source);
        $this->assertEquals(100, strlen($lead->campaign));
        $this->assertLessThanOrEqual(255, strlen($lead->landing_page));
    }

    public function test_duplicate_enquiry_is_not_saved_twice(): void
    {
        $this->enquire()->assertSessionHas('lead_event');
        $this->enquire()->assertSessionMissing('lead_event');
        $this->enquire('9123456780');   // different person is fine

        $this->assertEquals(2, Lead::count());
    }

    public function test_enquiries_are_rate_limited(): void
    {
        foreach (['9000000001', '9000000002', '9000000003', '9000000004', '9000000005'] as $phone) {
            $this->enquire($phone)->assertRedirect();
        }

        $this->enquire('9000000006')->assertStatus(429);
        $this->assertEquals(5, Lead::count());
    }

    public function test_redirected_product_cannot_receive_leads(): void
    {
        $this->enquire('9876543210', 'car-loan-old')->assertSessionHasErrors('page_id');
        $this->assertEquals(0, Lead::count());
    }

    public function test_uppercase_and_trailing_slash_urls_redirect_to_one_url(): void
    {
        $this->get('/products/HOME-LOAN?utm_source=google')
            ->assertStatus(301)
            ->assertRedirect('/products/home-loan?utm_source=google');
        // (trailing slash can't be tested here: the test client strips it before sending)
        $this->get('/')->assertOk();
    }

    public function test_lead_report_needs_login(): void
    {
        config(['services.admin.user' => 'admin', 'services.admin.password' => 'secret']);

        $this->get('/admin/leads')->assertStatus(401);
        $this->withBasicAuth('admin', 'wrong')->get('/admin/leads')->assertStatus(401);
        $this->withBasicAuth('admin', 'secret')->get('/admin/leads')->assertOk();
    }

    public function test_utm_builder_lists_only_live_products(): void
    {
        config(['services.admin.user' => 'admin', 'services.admin.password' => 'secret']);

        $this->get('/admin/utm-builder')->assertStatus(401);
        $this->withBasicAuth('admin', 'secret')->get('/admin/utm-builder')
            ->assertOk()
            ->assertSee('/products/home-loan')
            ->assertDontSee('/products/car-loan-old')   // redirected
            ->assertDontSee('/products/fd"', false);    // duplicate (canonical elsewhere)
    }

    public function test_sitemap_index_lists_all_sections(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('sitemap-products.xml')
            ->assertSee('sitemap-blogs.xml')
            ->assertSee('sitemap-branches.xml')
            ->assertSee('sitemap-static.xml')
            ->assertSee('sitemap-calculators.xml');
    }

    public function test_sitemap_skips_pages_that_should_not_be_indexed(): void
    {
        $this->get('/sitemap-products.xml')
            ->assertOk()
            ->assertSee('/products/fixed-deposit')
            ->assertDontSee('/products/car-loan-old')   // redirected
            ->assertDontSee('/products/fd<');           // canonical points elsewhere

        $this->get('/sitemap-static.xml')->assertDontSee('thank-you');          // noindex
        $this->get('/sitemap-blogs.xml')->assertDontSee('festive-offer-2024');  // unpublished
        $this->get('/sitemap-blogs.xml')->assertDontSee('diwali-offer');        // 302 redirect
        $this->get('/blog/diwali-offer')->assertStatus(302);
    }

    private function asAdmin(): static
    {
        config(['services.admin.user' => 'admin', 'services.admin.password' => 'secret']);

        return $this->withBasicAuth('admin', 'secret');
    }

    public function test_admin_needs_login(): void
    {
        config(['services.admin.password' => 'secret']);

        $this->get('/admin/pages')->assertStatus(401);
        $this->post('/admin/pages', ['type' => 'blog', 'title' => 'X', 'slug' => 'x'])->assertStatus(401);
        $this->assertDatabaseMissing('pages', ['slug' => 'x']);
    }

    public function test_page_added_edited_and_deleted_in_admin_updates_sitemap(): void
    {
        $base = ['type' => 'blog', 'title' => 'Home Loan Tips', 'slug' => 'home-loan-tips',
            'is_published' => '1', 'redirect_code' => 301];

        // add -> appears
        $this->asAdmin()->post('/admin/pages', $base)->assertRedirect('/admin/pages');
        $this->get('/sitemap-blogs.xml')->assertSee('/blog/home-loan-tips');
        $this->get('/blog/home-loan-tips')->assertOk();

        $page = Page::where('slug', 'home-loan-tips')->first();

        // edit: noindex -> disappears
        $this->asAdmin()->put("/admin/pages/{$page->id}", $base + ['noindex' => '1']);
        $this->get('/sitemap-blogs.xml')->assertDontSee('/blog/home-loan-tips');

        // edit: back to normal, then unpublish -> 404 and not in sitemap
        $this->asAdmin()->put("/admin/pages/{$page->id}", ['is_published' => '0'] + $base);
        $this->get('/blog/home-loan-tips')->assertNotFound();
        $this->get('/sitemap-blogs.xml')->assertDontSee('/blog/home-loan-tips');

        // edit: 302 redirect -> not in sitemap
        $this->asAdmin()->put("/admin/pages/{$page->id}", ['redirect_url' => '/blog/fd-vs-sip', 'redirect_code' => 302] + $base);
        $this->get('/blog/home-loan-tips')->assertRedirect('/blog/fd-vs-sip')->assertStatus(302);
        $this->get('/sitemap-blogs.xml')->assertDontSee('/blog/home-loan-tips');

        // delete -> gone
        $this->asAdmin()->delete("/admin/pages/{$page->id}")->assertRedirect('/admin/pages');
        $this->assertDatabaseMissing('pages', ['id' => $page->id]);
    }

    public function test_admin_blocks_duplicate_and_invalid_slugs(): void
    {
        $this->asAdmin()->post('/admin/pages', ['type' => 'product', 'title' => 'Home Loan 2', 'slug' => 'home-loan', 'redirect_code' => 301])
            ->assertSessionHasErrors('slug');   // already used
        $this->asAdmin()->post('/admin/pages', ['type' => 'calculator', 'title' => 'EMI', 'slug' => 'emi-calculator', 'redirect_code' => 301])
            ->assertSessionHasErrors('slug');   // no such calculator exists
        $this->asAdmin()->post('/admin/pages', ['type' => 'static', 'title' => 'Admin', 'slug' => 'admin', 'redirect_code' => 301])
            ->assertSessionHasErrors('slug');   // clashes with a real route
    }

    public function test_slug_is_made_automatically(): void
    {
        $new = ['type' => 'product', 'is_published' => '1', 'redirect_code' => 301];

        // empty slug -> made from the title
        $this->asAdmin()->post('/admin/pages', $new + ['title' => 'Car Loan', 'slug' => '']);
        $this->assertDatabaseHas('pages', ['type' => 'product', 'slug' => 'car-loan']);
        $this->get('/sitemap-products.xml')->assertSee('/products/car-loan');

        // same title again -> -2 added, no duplicate URL
        $this->asAdmin()->post('/admin/pages', $new + ['title' => 'Car Loan']);
        $this->assertDatabaseHas('pages', ['type' => 'product', 'slug' => 'car-loan-2']);

        // typed slug is cleaned up
        $this->asAdmin()->post('/admin/pages', $new + ['title' => 'X', 'slug' => 'Two Wheeler Loan']);
        $this->assertDatabaseHas('pages', ['type' => 'product', 'slug' => 'two-wheeler-loan']);

        // editing with empty slug keeps the current URL even if the title changes
        $page = Page::where('slug', 'car-loan')->first();
        $this->asAdmin()->put("/admin/pages/{$page->id}", $new + ['title' => 'Vehicle Loan', 'slug' => '']);
        $this->assertEquals('car-loan', $page->fresh()->slug);

        // calculators must be picked from the list
        $this->asAdmin()->post('/admin/pages', ['type' => 'calculator', 'title' => 'EMI', 'redirect_code' => 301])
            ->assertSessionHasErrors('slug');
    }

    public function test_admin_strips_scripts_from_content(): void
    {
        $this->asAdmin()->post('/admin/pages', [
            'type' => 'blog', 'title' => 'Safe', 'slug' => 'safe', 'is_published' => '1', 'redirect_code' => 301,
            'content' => '<p onclick="alert(1)">Hi</p><script>alert(2)</script><a href="javascript:alert(3)">x</a>',
        ]);

        $content = Page::where('slug', 'safe')->value('content');
        $this->assertStringNotContainsString('<script', $content);
        $this->assertStringNotContainsString('onclick', $content);
        $this->assertStringNotContainsString('javascript:', $content);
        $this->assertStringContainsString('<p>Hi</p>', $content);
    }

    public function test_sitemap_updates_when_pages_change(): void
    {
        Page::create(['type' => 'blog', 'title' => 'New Post', 'slug' => 'new-post']);
        $this->get('/sitemap-blogs.xml')->assertSee('/blog/new-post');

        Page::where('slug', 'new-post')->delete();
        $this->get('/sitemap-blogs.xml')->assertDontSee('/blog/new-post');
    }
}
