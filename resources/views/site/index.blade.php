@extends('site.layout')

@section('title', 'De Lollo Agronegócios - Tecnologia e Inovação no Campo')
@section('description', 'De Lollo Agronegócios oferece soluções tecnológicas inovadoras para o agronegócio, com foco em eficiência e sustentabilidade.')
@section('keywords', 'agronegócio, tecnologia agrícola, inovação rural, soluções para o campo, agricultura de precisão')

@section('conteudo')
    @include('site.partials.hero-banner')

    <!-- Services Section -->
    <section class="pt-20 pb-24 bg-gradient-to-br from-gray-50 to-white relative overflow-hidden">
        <!-- Curved top divider -->
        <div class="absolute -top-[100px] left-0 w-full overflow-hidden leading-none pointer-events-none z-30">
            <svg viewBox="0 0 1200 120" preserveAspectRatio="none" class="relative block w-full h-[100px]">
                <path d="M985.66,92.83C906.67,72,823.78,31,743.84,14.19c-82.26-17.34-168.06-16.33-250.45.39-57.84,11.73-114,31.07-172,41.86A600.21,600.21,0,0,1,0,27.35V120H1200V95.8C1132.19,118.92,1055.71,111.31,985.66,92.83Z" class="fill-current text-gray-50"></path>
            </svg>
        </div>
        <!-- Background Elements -->
        <div class="absolute inset-0 opacity-5">
            <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#22c55e" stroke-width="1"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)"/>
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center mb-20">
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900">
                    Soluções Tecnológicas
                    <span class="bg-gradient-to-r from-green-600 to-green-500 bg-clip-text text-transparent">
                        para o Campo
                    </span>
                </h2>

                <p class="text-xl text-gray-600 max-w-4xl mx-auto leading-relaxed mt-6">
                    Oferecemos uma gama completa de soluções tecnológicas para modernizar e otimizar sua produção agrícola,
                    desde agricultura de precisão até análise avançada de dados.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @php
                    $services = [
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                            'title' => 'Agricultura de Precisão',
                            'description' => 'Sistemas avançados de monitoramento e controle para otimizar o uso de recursos e maximizar a produtividade com tecnologia de ponta.',
                            'features' => [
                                'Monitoramento GPS de máquinas',
                                'Controle automatizado de irrigação',
                                'Análise de solo em tempo real',
                            ]
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
                            'title' => 'Soluções IoT',
                            'description' => 'Dispositivos conectados para monitoramento em tempo real de temperatura, umidade e condições do solo.',
                            'features' => []
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                            'title' => 'Análise de Dados',
                            'description' => 'Inteligência artificial para análise de dados agrícolas e tomada de decisões baseada em métricas reais.',
                            'features' => []
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>',
                            'title' => 'Automação',
                            'description' => 'Sistemas automatizados de irrigação, controle de pragas e monitoramento de equipamentos.',
                            'features' => []
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
                            'title' => 'Consultoria Técnica',
                            'description' => 'Assessoria especializada para implementação e otimização de tecnologias no campo.',
                            'features' => []
                        ],
                        [
                            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
                            'title' => 'Suporte 24/7',
                            'description' => 'Equipe técnica especializada disponível para suporte remoto e presencial sempre que necessário.',
                            'features' => []
                        ],
                    ];
                @endphp

                @foreach ($services as $service)
                    @include('site.components.service-card', $service)
                @endforeach
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-6">
                        Sobre a <span class="text-green-600">De Lollo</span>
                    </h2>
                    <p class="text-lg text-gray-600 mb-6 leading-relaxed">
                        Somos especialistas em tecnologia agrícola com mais de uma década de experiência no mercado brasileiro.
                        Nossa missão é transformar o agronegócio através da inovação, conectividade e práticas sustentáveis.
                    </p>
                    <p class="text-lg text-gray-600 mb-8 leading-relaxed">
                        Desenvolvemos soluções personalizadas que se adaptam às necessidades específicas de cada produtor,
                        desde pequenas propriedades familiares até grandes corporações agrícolas.
                    </p>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600 mb-2">10+</div>
                            <div class="text-gray-600">Anos de Experiência</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600 mb-2">500+</div>
                            <div class="text-gray-600">Projetos Realizados</div>
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <img src="{{ asset('assets/images/sobre-agricultura.jpg') }}" alt="Equipe De Lollo" class="w-full h-96 object-cover rounded-2xl shadow-xl" onerror="this.src='https://images.unsplash.com/photo-1500651230702-0e2d8a49d4ad?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'">
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-green-600">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                Pronto para Revolucionar sua Produção?
            </h2>
            <p class="text-xl text-green-100 mb-8 leading-relaxed">
                Entre em contato conosco e descubra como nossas soluções podem transformar seu negócio agrícola.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('site.contato') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-green-600 text-lg font-semibold rounded-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Fale Conosco
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </a>
                <a href="{{ route('site.portfolio') }}" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white text-lg font-semibold rounded-lg hover:bg-white hover:text-green-600 transition-all duration-300">
                    Veja Nosso Portfólio
                </a>
            </div>
        </div>
    </section>
@endsection
