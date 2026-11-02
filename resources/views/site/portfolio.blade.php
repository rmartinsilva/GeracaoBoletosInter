@extends('site.layout')

@section('title', 'Portfólio - De Lollo Agronegócios')
@section('description', 'Conheça alguns dos nossos projetos mais recentes e veja como transformamos o agronegócio através da tecnologia.')

@section('conteudo')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-green-50 to-green-100 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    Nosso <span class="text-green-600">Portfólio</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Conheça alguns dos nossos projetos mais recentes e veja como transformamos o agronegócio através da tecnologia.
                </p>
            </div>
        </div>
    </section>

    <!-- Filter Buttons -->
    <section class="py-12 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap justify-center gap-4 mb-12">
                <button class="portfolio-filter active bg-green-600 text-white px-6 py-3 rounded-lg font-semibold" data-filter="all">
                    Todos os Projetos
                </button>
                <button class="portfolio-filter bg-gray-100 text-gray-700 hover:bg-green-100 px-6 py-3 rounded-lg font-semibold" data-filter="agricultura">
                    Agricultura de Precisão
                </button>
                <button class="portfolio-filter bg-gray-100 text-gray-700 hover:bg-green-100 px-6 py-3 rounded-lg font-semibold" data-filter="iot">
                    IoT Agrícola
                </button>
                <button class="portfolio-filter bg-gray-100 text-gray-700 hover:bg-green-100 px-6 py-3 rounded-lg font-semibold" data-filter="automacao">
                    Automação
                </button>
                <button class="portfolio-filter bg-gray-100 text-gray-700 hover:bg-green-100 px-6 py-3 rounded-lg font-semibold" data-filter="analise">
                    Análise de Dados
                </button>
            </div>
        </div>
    </section>

    <!-- Portfolio Grid -->
    <section class="py-12 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 portfolio-container">
                <!-- Projeto 1 -->
                <div class="portfolio-item agricultura bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="relative">
                        <img src="{{ asset('assets/images/projeto-fazenda1.jpg') }}" alt="Projeto Fazenda Modelo" class="w-full h-48 object-cover" onerror="this.src='https://images.unsplash.com/photo-1500651230702-0e2d8a49d4ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'">
                        <div class="absolute inset-0 bg-green-600 bg-opacity-0 hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <button class="opacity-0 hover:opacity-100 bg-white text-green-600 p-3 rounded-full transform scale-75 hover:scale-100 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Agricultura de Precisão</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Fazenda Modelo - SP</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Implementação completa de agricultura de precisão em uma propriedade de 500 hectares,
                            resultando em 35% de aumento na produtividade.
                        </p>
                        <div class="flex items-center text-green-600 text-sm font-semibold">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Projeto Concluído
                        </div>
                    </div>
                </div>

                <!-- Projeto 2 -->
                <div class="portfolio-item iot bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="relative">
                        <img src="{{ asset('assets/images/projeto-vinhedo.jpg') }}" alt="Projeto Vinhedo Inteligente" class="w-full h-48 object-cover" onerror="this.src='https://images.unsplash.com/photo-1423483641154-5411ec9c0ddf?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'">
                        <div class="absolute inset-0 bg-green-600 bg-opacity-0 hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <button class="opacity-0 hover:opacity-100 bg-white text-green-600 p-3 rounded-full transform scale-75 hover:scale-100 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">IoT Agrícola</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Vinhedo Inteligente - RS</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Sistema completo de IoT para monitoramento de vinhedo, com controle automatizado de irrigação
                            e prevenção de doenças.
                        </p>
                        <div class="flex items-center text-green-600 text-sm font-semibold">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Projeto Concluído
                        </div>
                    </div>
                </div>

                <!-- Projeto 3 -->
                <div class="portfolio-item automacao bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="relative">
                        <img src="{{ asset('assets/images/projeto-estufa.jpg') }}" alt="Projeto Estufas Automatizadas" class="w-full h-48 object-cover" onerror="this.src='https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'">
                        <div class="absolute inset-0 bg-green-600 bg-opacity-0 hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <button class="opacity-0 hover:opacity-100 bg-white text-green-600 p-3 rounded-full transform scale-75 hover:scale-100 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <span class="bg-purple-100 text-purple-800 text-xs font-semibold px-2.5 py-0.5 rounded">Automação</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Estufas Automatizadas - MG</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Sistema completo de automação para estufas hidropônicas, com controle de temperatura,
                            umidade e nutrientes.
                        </p>
                        <div class="flex items-center text-green-600 text-sm font-semibold">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Projeto Concluído
                        </div>
                    </div>
                </div>

                <!-- Projeto 4 -->
                <div class="portfolio-item analise bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="relative">
                        <img src="{{ asset('assets/images/projeto-dados.jpg') }}" alt="Projeto Análise de Dados" class="w-full h-48 object-cover" onerror="this.src='https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'">
                        <div class="absolute inset-0 bg-green-600 bg-opacity-0 hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <button class="opacity-0 hover:opacity-100 bg-white text-green-600 p-3 rounded-full transform scale-75 hover:scale-100 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <span class="bg-orange-100 text-orange-800 text-xs font-semibold px-2.5 py-0.5 rounded">Análise de Dados</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Plataforma Analytics - Nacional</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Desenvolvimento de plataforma de análise de dados para grandes propriedades,
                            com dashboards personalizados e relatórios automáticos.
                        </p>
                        <div class="flex items-center text-green-600 text-sm font-semibold">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Projeto Concluído
                        </div>
                    </div>
                </div>

                <!-- Projeto 5 -->
                <div class="portfolio-item agricultura bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="relative">
                        <img src="{{ asset('assets/images/projeto-milho.jpg') }}" alt="Projeto Cultivo de Milho" class="w-full h-48 object-cover" onerror="this.src='https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'">
                        <div class="absolute inset-0 bg-green-600 bg-opacity-0 hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <button class="opacity-0 hover:opacity-100 bg-white text-green-600 p-3 rounded-full transform scale-75 hover:scale-100 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">Agricultura de Precisão</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Cultivo Inteligente - MT</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Projeto de agricultura de precisão para cultivo de milho, com monitoramento por satélite
                            e aplicação variável de insumos.
                        </p>
                        <div class="flex items-center text-green-600 text-sm font-semibold">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Projeto Concluído
                        </div>
                    </div>
                </div>

                <!-- Projeto 6 -->
                <div class="portfolio-item iot bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                    <div class="relative">
                        <img src="{{ asset('assets/images/projeto-pecuaria.jpg') }}" alt="Projeto Pecuária Inteligente" class="w-full h-48 object-cover" onerror="this.src='https://images.unsplash.com/photo-1605100804763-247f67b3557e?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80'">
                        <div class="absolute inset-0 bg-green-600 bg-opacity-0 hover:bg-opacity-20 transition-all duration-300 flex items-center justify-center">
                            <button class="opacity-0 hover:opacity-100 bg-white text-green-600 p-3 rounded-full transform scale-75 hover:scale-100 transition-all duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-center mb-3">
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">IoT Agrícola</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Pecuária Inteligente - GO</h3>
                        <p class="text-gray-600 text-sm mb-4">
                            Sistema de monitoramento de rebanho com IoT, controle de temperatura em currais
                            e rastreamento individual de animais.
                        </p>
                        <div class="flex items-center text-green-600 text-sm font-semibold">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Projeto Concluído
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    O que Nossos <span class="text-green-600">Clientes Dizem</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Depoimentos reais de produtores que transformaram seus negócios com nossas soluções.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            JS
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-900">João Silva</h4>
                            <p class="text-gray-600 text-sm">Fazenda Modelo - SP</p>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">
                        "As soluções da De Lollo aumentaram nossa produtividade em 40%. O suporte técnico é excepcional e sempre disponível quando precisamos."
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            MC
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-900">Maria Clara</h4>
                            <p class="text-gray-600 text-sm">Vinícola do Vale - RS</p>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">
                        "A implementação do sistema IoT revolucionou nossa produção. Conseguimos reduzir custos e aumentar a qualidade dos nossos vinhos."
                    </p>
                </div>

                <div class="bg-gray-50 rounded-xl p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                            RA
                        </div>
                        <div class="ml-4">
                            <h4 class="font-semibold text-gray-900">Roberto Alves</h4>
                            <p class="text-gray-600 text-sm">AgroTech Minas - MG</p>
                        </div>
                    </div>
                    <p class="text-gray-600 italic">
                        "O sistema de automação das estufas nos deu controle total sobre o ambiente de cultivo. Os resultados superaram nossas expectativas."
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-green-600">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                Seu Projeto Pode Ser o Próximo Sucesso
            </h2>
            <p class="text-xl text-green-100 mb-8 leading-relaxed">
                Entre em contato conosco e vamos discutir como podemos transformar seu negócio agrícola.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('site.contato') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-green-600 text-lg font-semibold rounded-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Fale Conosco
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </a>
                <a href="{{ route('site.servicos') }}" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white text-lg font-semibold rounded-lg hover:bg-white hover:text-green-600 transition-all duration-300">
                    Conheça Nossos Serviços
                </a>
            </div>
        </div>
    </section>

    <!-- Portfolio Modal (Hidden by default) -->
    <div id="portfolio-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-2xl font-bold text-gray-900" id="modal-title">Projeto</h3>
                        <button id="close-modal" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <div id="modal-content">
                        <img id="modal-image" src="" alt="" class="w-full h-64 object-cover rounded-lg mb-4">
                        <p id="modal-description" class="text-gray-600"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Portfolio Filter
        document.querySelectorAll('.portfolio-filter').forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.getAttribute('data-filter');

                // Update active button
                document.querySelectorAll('.portfolio-filter').forEach(btn => btn.classList.remove('active', 'bg-green-600', 'text-white'));
                this.classList.add('active', 'bg-green-600', 'text-white');

                // Filter items
                document.querySelectorAll('.portfolio-item').forEach(item => {
                    if (filter === 'all' || item.classList.contains(filter)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });

        // Modal functionality (placeholder for future implementation)
        document.querySelectorAll('.portfolio-item').forEach(item => {
            item.addEventListener('click', function() {
                // Future modal implementation
                console.log('Projeto clicado:', this.querySelector('h3').textContent);
            });
        });
    </script>
@endsection
