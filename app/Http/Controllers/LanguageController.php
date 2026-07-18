<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LanguageController extends Controller
{
    public function switch(Request $request)
    {
        $locale = $request->validate([
            'locale' => 'required|in:fr,en,ar',
        ])['locale'];

        $request->session()->put('locale', $locale);

        if ($request->user()) {
            $request->user()->update(['language' => $locale]);
        }

        return back();
    }
}
