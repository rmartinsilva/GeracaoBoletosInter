@extends('site.layout')

@section('title', 'Geração de Cobranças')

@section('conteudo')
    <section class="max-w-2xl">
        <p class="text-sm font-medium uppercase tracking-[0.2em] text-lime-700">Banco Inter</p>
        <h1 class="mt-4 text-4xl font-semibold tracking-tight text-stone-900 sm:text-5xl">
            Cobranças mensais em um só lugar
        </h1>
        <p class="mt-6 text-lg leading-relaxed text-stone-600">
            Este é o ponto de partida da integração para gerar cobranças mensais pelo Banco Inter.
        </p>
        <a href="{{ route('login') }}" class="mt-10 inline-flex items-center gap-2 rounded-full bg-lime-700 px-5 py-3 text-sm font-semibold text-white transition hover:bg-lime-800">
            Acessar o painel
            <span aria-hidden="true">→</span>
        </a>
    </section>
@endsection
