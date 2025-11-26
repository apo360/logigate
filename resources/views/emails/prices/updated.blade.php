@component('mail::message')

# Atualização de Preço

O preço do produto abaixo foi atualizado:

**{{ $produto->ProductDescription }}**  
Código: **{{ $produto->ProductCode }}**

---

## 📊 Detalhes da Alteração

- **Preço Anterior:** {{ number_format($oldPrice, 2) }} AOA  
- **Novo Preço:** {{ number_format($newPrice, 2) }} AOA  

---

## 🧠 Análise da IA

**{{ $impacto }}**

---

Este ajuste foi registrado e está agora no histórico de auditoria fiscal.

@component('mail::button', ['url' => route('produtos.show', $produto->id)])
Ver Produto
@endcomponent

Obrigado,  
{{ config('app.name') }}

@endcomponent
