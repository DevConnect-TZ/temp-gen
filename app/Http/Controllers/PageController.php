<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Display a listing of pages (admin dashboard).
     */
    public function index()
    {
        $pages = Page::all();
        return view('dashboard.pages.index', ['pages' => $pages]);
    }

    /**
     * Show the form for creating a new page.
     */
    public function create()
    {
        return view('dashboard.pages.create');
    }

    /**
     * Store a newly created page in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'template' => 'required|in:template1,template2',
            'price' => 'nullable|numeric|min:0',
            'payment_gateway' => 'nullable|string',
        ]);

        // Generate unique slug
        $baseSlug = Str::slug($request->title);
        $slug = $baseSlug;
        $counter = 1;
        
        while (Page::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        $validated['slug'] = $slug;

        Page::create($validated);

        return redirect('/pages')->with('success', 'Page created successfully! Access it at: /' . $slug);
    }

    /**
     * Display the specified page (public route).
     */
    public function show(Page $page)
    {
        if (!$page->is_active) {
            abort(404);
        }

        $template = $page->template;
        $templatePath = resource_path("views/templates/{$template}.html");

        if (!file_exists($templatePath)) {
            abort(404, 'Template not found');
        }

        $html = file_get_contents($templatePath);

        return response($html)
            ->header('Content-Type', 'text/html; charset=utf-8');
    }
}
