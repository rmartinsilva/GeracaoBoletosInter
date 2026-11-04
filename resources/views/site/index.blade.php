@extends('site.layout')

@section('title', 'Geração de Cobranças')

@section('conteudo')
    <section class="site-hero">
        <p class="site-selo">Banco Inter</p>
        <h1>Cobranças mensais em um só lugar</h1>
        <p>
            Este é o ponto de partida da integração para gerar cobranças mensais pelo Banco Inter.
        </p>
        <a href="{{ route('login') }}" class="site-cta">
            Acessar o painel
            <span aria-hidden="true">→</span>
        </a>
    </section>
@endsection
