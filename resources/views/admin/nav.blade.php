<nav class="admin-nav">
    <span>Admin</span>
    <a href="{{ route('admin.pages.index') }}" @class(['active' => request()->routeIs('admin.pages.*')])>Pages</a>
    <a href="{{ route('admin.leads') }}" @class(['active' => request()->routeIs('admin.leads')])>Lead Report</a>
    <a href="{{ route('admin.utm') }}" @class(['active' => request()->routeIs('admin.utm')])>UTM Builder</a>
    <a href="{{ route('sitemap') }}" target="_blank" rel="noopener">View sitemap &rarr;</a>
</nav>
