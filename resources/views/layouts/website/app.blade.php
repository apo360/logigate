<!-- Layout App para Website -->
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <!-- Canonical SEO -->
  <link rel="canonical" href="https://www.logigate.ao"/>

  <!-- Meta Tags -->
  <meta name="keywords" content="logigate, sistema de gestão aduaneira, gestão financeira, gestão contabilística, automação aduaneira, hongayetu lda, software aduaneiro, controle logístico, contabilidade aduaneira, despacho aduaneiro, gestão de operações, Angola, África">
  <meta name="description" content="Logigate: Solução completa para gestão aduaneira, financeira e contabilística. Automatize processos, reduza custos e aumente a eficiência dos seus despachos com a Hongayetu Lda.">

  <!-- Schema.org markup -->
  <meta itemprop="name" content="Logigate - Gestão Aduaneira, Financeira e Contabilística">
  <meta itemprop="description" content="Logigate oferece uma solução robusta e integrada para automação e controle de processos aduaneiros, financeiros e contabilísticos, garantindo eficiência e precisão.">
  <meta itemprop="image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg">
  <meta itemprop="datePublished" content="2023-10-01">
  <meta itemprop="ratingValue" content="4.9">
  <meta itemprop="reviewCount" content="150">

  <!-- Twitter Card data -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:site" content="@hongayetu">
  <meta name="twitter:title" content="Logigate - Sistema de Gestão Aduaneira, Financeira e Contabilística">
  <meta name="twitter:description" content="Aumente a eficiência dos seus processos com o Logigate, desenvolvido pela Hongayetu Lda. Automatize despachos e gestão financeira com uma plataforma avançada. #Logística #Angola">
  <meta name="twitter:creator" content="@hongayetu">
  <meta name="twitter:image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg">
  <meta name="twitter:image:alt" content="Logigate - Sistema de Gestão Aduaneira">

  <!-- Open Graph data -->
  <meta property="og:title" content="Logigate | Sistema de Gestão Aduaneira e Financeira" />
  <meta property="og:type" content="website" />
  <meta property="og:url" content="https://www.logigate.ao" />
  <meta property="og:image" content="https://www.logigate.ao/images/logigate-thumbnail.jpg" />
  <meta property="og:image:width" content="1200" />
  <meta property="og:image:height" content="628" />
  <meta property="og:description" content="Logigate é uma solução desenvolvida pela Hongayetu Lda para gestão aduaneira, financeira e contabilística, garantindo automação e eficiência nos processos de despacho e controle financeiro." />
  <meta property="og:site_name" content="Logigate" />
  <meta property="og:locale" content="pt_AO" />
  <meta property="og:updated_time" content="2023-10-01T00:00:00+01:00" />

  <!-- Favicon-->
  <link rel="icon" type="image/png" href="{{ asset('images/favicon.png') }}" />
  <title>Logigate - Gestão Aduaneira Simplificada</title>
  <!-- Font Awesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <!-- Tailwind CSS -->
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans&family=Poppins:wght@500&display=swap" rel="stylesheet">
  <!-- AOS (Animate On Scroll) -->
  <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
  <!-- Estilos Personalizados -->
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700&family=Open+Sans&family=Poppins:wght@500&display=swap" rel="stylesheet">
  <!-- Estilos Personalizados -->
   <!-- Particles.js -->
   <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
  <!-- Estilos Personalizados -->
  <style>
    /* Efeito de vidro nos botões */
    .glass-button {
      background: rgba(255, 255, 255, 0.1); /* Fundo semi-transparente */
      backdrop-filter: blur(10px); /* Efeito de desfoque */
      border: 1px solid rgba(255, 255, 255, 0.2); /* Borda sutil */
      border-radius: 8px; /* Bordas arredondadas */
      padding: 12px 24px;
      color: white;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    .glass-button:hover {
      background: rgba(255, 255, 255, 0.2); /* Fundo mais claro no hover */
      transform: scale(1.05); /* Efeito de zoom */
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra suave */
    }

    /* Container do Hero Section */
    .hero-section {
      position: relative;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      color: white;
      overflow: hidden;
    }

    /* Fundo animado com particles.js */
    #particles-js {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
    }

    /* Imagem no Hero Section */
    .hero-image {
      max-width: 400px;
      margin: 0 auto 2rem;
    }
  </style>
  <style>
    body {
      font-family: 'Open Sans', sans-serif;
    }
    h1, h2, h3 {
      font-family: 'Montserrat', sans-serif;
    }
    .menu-hamburguer {
      display: none;
    }

    /* Responsividade: Ocultar em telas maiores, exibir em telas menores *//* Responsividade: Ocultar em telas maiores, exibir em telas menores */
    @media (min-width: 768px) {
      .floating-button {
        display: none; /* Oculta em telas maiores */
      }
    }
    @media (max-width: 767px) {
      .floating-button {
        display: flex; /* Exibe em telas menores */
      }
    }
    .glass-card {
      background: linear-gradient(135deg, rgba(0, 71, 171, 0.8), rgba(128, 196, 255, 0.8)); /* Gradiente azul com transparência */
      backdrop-filter: blur(10px); /* Efeito de desfoque */
      border: 1px solid rgba(255, 255, 255, 0.2); /* Borda sutil */
      border-radius: 12px; /* Bordas arredondadas */
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra suave */
      transition: transform 0.3s ease, box-shadow 0.3s ease; /* Transição suave */
    }
    .glass-card:hover {
      transform: scale(1.05); /* Efeito de zoom no hover */
      box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2); /* Sombra mais intensa no hover */
    }

    /* Botão flutuante */
    .floating-button {
      position: fixed;
      bottom: 3rem;
      right: 1rem;
      width: 50px;
      height: 50px;
      background-color: #0047AB;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      z-index: 1000;
    }
    .floating-button:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 8px rgba(0, 0, 0, 0.2);
    }

    /* Ícone do menu */
    .menu-icon {
      width: 24px;
      height: 24px;
      fill: white;
    }

    /* Menu circular */
    .circular-menu {
      position: fixed;
      bottom: 3rem;
      right: 1rem;
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-color: rgba(0, 71, 171, 0.9);
      backdrop-filter: blur(10px);
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      transform: scale(0);
      transition: transform 0.3s ease;
      z-index: 999;
    }
    .circular-menu.active {
      display: flex;
      transform: scale(1);
    }

    /* Itens do menu */
    .menu-item {
      position: absolute;
      width: 45px;
      height: 45px;
      background-color: black;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #0047AB;
      font-size: 14px;
      text-decoration: none;
      box-shadow: 0 2px 4px rgba(225, 6, 6, 0.1);
      transition: transform 0.3s ease, background-color 0.3s ease;
    }
    .menu-item:hover {
      background-color: #f0f0f0;
      transform: scale(1.1);
    }
  </style>
  <style>
    /* Efeito de vidro */
    .glass-effect {
        background: rgba(255, 255, 255, 0.1); /* Fundo semi-transparente */
        backdrop-filter: blur(10px); /* Efeito de desfoque */
        border-bottom: 1px solid rgba(255, 255, 255, 0.2); /* Borda sutil */
      }

      /* Gradiente para o header */
      .gradient-bg {
        background: linear-gradient(135deg, rgba(0, 71, 171, 0.9), rgba(128, 196, 255, 0.9));
      }

      /* Efeito de sublinhado animado */
      .underline-effect {
        position: relative;
      }
      .underline-effect::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: #1E88E5;
        transition: width 0.3s ease;
      }
      .underline-effect:hover::after {
        width: 100%;
      }

      /* Botão de login com gradiente */
      .login-button {
        background: linear-gradient(135deg, #1E88E5, #0D47A1);
        color: white;
        padding: 8px 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
      }
      .login-button:hover {
        background: linear-gradient(135deg, #0D47A1, #1E88E5);
        transform: scale(1.05);
      }
  </style>

</head>
<body class="bg-gray-900">
    @include('layouts.website.menu')
    <div class="container">
        @yield('content')
    </div>
</body>
</html>
@extends('layouts.website.app')