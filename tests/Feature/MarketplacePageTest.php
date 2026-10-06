<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MarketplacePageTest extends TestCase
{
    public function test_public_marketplace_renders_without_authentication_or_operational_queries(): void
    {
        config(['session.driver' => 'array', 'cache.default' => 'array', 'mail.default' => 'array']);
        Http::preventStrayRequests();
        Mail::fake();
        $response = $this->get(route('marketplace'));
        $response->assertOk()->assertSee('Encontre um despachante')->assertSee('Não há histórico público autorizado');
        $response->assertSee('Que mercadoria pretende importar ou exportar?');
        $response->assertSee(route('home').'#contactos', false);
        $response->assertSee(route('cliente.portal.login'), false);
        $response->assertSee(route('newsletter.subscribe'), false);
        $response->assertDontSee('/api/v1/marketplace')->assertDontSee('/marketplace/despachante/');
        $response->assertDontSee('Verificado')->assertDontSee('avaliacoes')->assertDontSee('href="#"', false);
        $response->assertDontSee('NIF:')->assertDontSee('Registo Comercial:');
        $this->assertGuest();
        Mail::assertNothingSent();
    }
}
