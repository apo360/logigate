@component('mail::message')

# Reavaliação Automática de Preço

O sistema realizou a reavaliação automática do preço do produto:

**{{ $produto->ProductDescription }}**  
Código: **{{ $produto->ProductCode }}**

---

## 📊 Dados da Reavaliação

- **Preço Inicial:** {{ number_format($precoAntigo, 2) }} AOA  
- **Preço Atual:** {{ number_format($precoAtual, 2) }} AOA  
- **Variação:** {{ number_format($variacao, 2) }}%

---

## 🧠 Recomendação da IA

**{{ $recomendacao }}**

---

@component('mail::button', ['url' => route('produtos.show', $produto->id)])
Ver Produto
@endcomponent

Obrigado,  
{{ config('app.name') }}

@endcomponent
