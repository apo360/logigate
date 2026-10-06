<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\Support\LandingFixtures;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array']);
        Http::preventStrayRequests();
        Mail::fake();
    }

    public function test_landing_renders_existing_navigation_and_plan_contracts_without_database_access(): void
    {
        $html = view('welcome', ['planos' => LandingFixtures::plans()])->render();
        foreach (['marketplace', 'consultar.pauta', 'login', 'cliente.portal.login', 'contact.send', 'newsletter.subscribe', 'register'] as $route) {
            $this->assertStringContainsString(route($route), $html);
        }
        $this->assertStringContainsString('name="plano" value="902"', $html);
        $this->assertStringContainsString('name="modalidade" value="monthly"', $html);
        $this->assertStringContainsString('data-monthly="12500.5"', $html);
        $this->assertStringContainsString('data-semestral=""', $html);
        $this->assertStringContainsString('12.500,50 AOA', $html);
        $this->assertStringContainsString('dados fictícios', $html);
        $this->assertStringNotContainsString('href="#"', $html);
        $this->assertStringNotContainsString('Mais Popular', $html);
        $this->assertStringNotContainsString('cdn.jsdelivr.net', $html);
        Mail::assertNothingSent();
    }

    public function test_empty_plans_have_a_useful_contact_destination(): void
    {
        $html = view('welcome', ['planos' => collect()])->render();
        $this->assertStringContainsString('Planos indisponíveis de momento', $html);
        $this->assertStringContainsString('href="#contactos"', $html);
        $this->assertStringNotContainsString('name="plano"', $html);
    }

    public function test_plan_content_is_escaped(): void
    {
        $plans = LandingFixtures::plans();
        $plans[0]->nome = '<script>alert(1)</script>';
        $html = view('welcome', ['planos' => $plans])->render();
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }
}
