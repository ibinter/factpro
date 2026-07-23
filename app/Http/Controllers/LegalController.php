<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LegalController extends Controller
{
    private function sharedProps(): array
    {
        return [
            'canLogin'    => \Illuminate\Support\Facades\Route::has('login'),
            'canRegister' => \Illuminate\Support\Facades\Route::has('register'),
        ];
    }

    public function mentions(): Response
    {
        return Inertia::render('Legal/Mentions', $this->sharedProps());
    }

    public function cgu(): Response
    {
        return Inertia::render('Legal/Cgu', $this->sharedProps());
    }

    public function confidentialite(): Response
    {
        return Inertia::render('Legal/Confidentialite', $this->sharedProps());
    }

    public function cookies(): Response
    {
        return Inertia::render('Legal/Cookies', $this->sharedProps());
    }

    public function pi(): Response
    {
        return Inertia::render('Legal/Pi', $this->sharedProps());
    }

    public function resiliation(): Response
    {
        return Inertia::render('Legal/Resiliation', $this->sharedProps());
    }

    public function sla(): Response
    {
        return Inertia::render('Legal/Sla', $this->sharedProps());
    }

    public function securite(): Response
    {
        return Inertia::render('Legal/Securite', $this->sharedProps());
    }

    public function accessibilite(): Response
    {
        return Inertia::render('Legal/Accessibilite', $this->sharedProps());
    }

    public function remboursement(): Response
    {
        return Inertia::render('Legal/Remboursement', $this->sharedProps());
    }

    public function antiSpam(): Response
    {
        return Inertia::render('Legal/AntiSpam', $this->sharedProps());
    }

    public function conditionsApi(): Response
    {
        return Inertia::render('Legal/ConditionsApi', $this->sharedProps());
    }

    public function partenaires(): Response
    {
        return Inertia::render('Legal/Partenaires', $this->sharedProps());
    }

    public function utilisationAcceptable(): Response
    {
        return Inertia::render('Legal/UtilisationAcceptable', $this->sharedProps());
    }

    public function rgpdDetails(): Response
    {
        return Inertia::render('Legal/RgpdDetails', $this->sharedProps());
    }

    public function dpa(): Response
    {
        return Inertia::render('Legal/Dpa', $this->sharedProps());
    }

    public function planContinuite(): Response
    {
        return Inertia::render('Legal/PlanContinuite', $this->sharedProps());
    }

    public function charteEthique(): Response
    {
        return Inertia::render('Legal/CharteEthique', $this->sharedProps());
    }
}
