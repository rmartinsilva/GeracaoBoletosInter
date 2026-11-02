@extends('site.layout')

@section('title', 'Serviços - De Lollo Agronegócios')
@section('description', 'Conheça nossos serviços especializados em tecnologia agrícola: agricultura de precisão, IoT, análise de dados e muito mais.')

@section('conteudo')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-green-50 to-green-100 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    Nossos <span class="text-green-600">Serviços</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Soluções tecnológicas completas para transformar seu negócio agrícola e maximizar sua produtividade.
                </p>
            </div>
        </div>
    </section>

    <!-- Services Grid -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Agricultura de Precisão -->
                <div class="bg-white rounded-xl shadow-lg p-8 border-t-4 border-green-600 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">Agricultura de Precisão</h3>
                    <p class="text-gray-600 text-center mb-6">
                        Sistemas avançados de monitoramento e controle para otimizar o uso de recursos e maximizar a produtividade.
                    </p>
                    <ul class="text-sm text-gray-600 space-y-2 mb-6">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Monitoramento GPS de máquinas
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Controle automatizado de irrigação
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                                Análise de solo em tempo real
                        </li>
                    </ul>
                    <a href="#contato" class="block w-full bg-green-600 text-white text-center py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200">
                        Saiba Mais
                    </a>
                </div>

                <!-- IoT Agrícola -->
                <div class="bg-white rounded-xl shadow-lg p-8 border-t-4 border-green-600 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">Soluções IoT</h3>
                    <p class="text-gray-600 text-center mb-6">
                        Dispositivos conectados para monitoramento em tempo real de temperatura, umidade e condições do solo.
                    </p>
                    <ul class="text-sm text-gray-600 space-y-2 mb-6">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Sensores inteligentes de solo
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Estações meteorológicas conectadas
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Sistema de alerta automatizado
                        </li>
                    </ul>
                    <a href="#contato" class="block w-full bg-green-600 text-white text-center py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200">
                        Saiba Mais
                    </a>
                </div>

                <!-- Análise de Dados -->
                <div class="bg-white rounded-xl shadow-lg p-8 border-t-4 border-green-600 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">Análise de Dados</h3>
                    <p class="text-gray-600 text-center mb-6">
                        Inteligência artificial para análise de dados agrícolas e tomada de decisões baseada em métricas reais.
                    </p>
                    <ul class="text-sm text-gray-600 space-y-2 mb-6">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Dashboards personalizados
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Previsões de produtividade
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Relatórios automatizados
                        </li>
                    </ul>
                    <a href="#contato" class="block w-full bg-green-600 text-white text-center py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200">
                        Saiba Mais
                    </a>
                </div>

                <!-- Automação -->
                <div class="bg-white rounded-xl shadow-lg p-8 border-t-4 border-green-600 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">Automação</h3>
                    <p class="text-gray-600 text-center mb-6">
                        Sistemas automatizados de irrigação, controle de pragas e monitoramento de equipamentos.
                    </p>
                    <ul class="text-sm text-gray-600 space-y-2 mb-6">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Controle automatizado de irrigação
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Sistemas de ventilação inteligente
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Monitoramento de equipamentos
                        </li>
                    </ul>
                    <a href="#contato" class="block w-full bg-green-600 text-white text-center py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200">
                        Saiba Mais
                    </a>
                </div>

                <!-- Consultoria -->
                <div class="bg-white rounded-xl shadow-lg p-8 border-t-4 border-green-600 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">Consultoria Técnica</h3>
                    <p class="text-gray-600 text-center mb-6">
                        Assessoria especializada para implementação e otimização de tecnologias no campo.
                    </p>
                    <ul class="text-sm text-gray-600 space-y-2 mb-6">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Diagnóstico personalizado
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Plano de implementação
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Acompanhamento de resultados
                        </li>
                    </ul>
                    <a href="#contato" class="block w-full bg-green-600 text-white text-center py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200">
                        Saiba Mais
                    </a>
                </div>

                <!-- Suporte -->
                <div class="bg-white rounded-xl shadow-lg p-8 border-t-4 border-green-600 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-6 mx-auto">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4 text-center">Suporte 24/7</h3>
                    <p class="text-gray-600 text-center mb-6">
                        Equipe técnica especializada disponível para suporte remoto e presencial sempre que necessário.
                    </p>
                    <ul class="text-sm text-gray-600 space-y-2 mb-6">
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Suporte técnico remoto
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Visitas técnicas presenciais
                        </li>
                        <li class="flex items-center">
                            <svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Manutenção preventiva
                        </li>
                    </ul>
                    <a href="#contato" class="block w-full bg-green-600 text-white text-center py-3 px-4 rounded-lg font-semibold hover:bg-green-700 transition-colors duration-200">
                        Saiba Mais
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Process Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Como <span class="text-green-600">Trabalhamos</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Processo completo desde a análise inicial até a implementação e acompanhamento dos resultados.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-xl font-bold">1</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Análise</h3>
                    <p class="text-gray-600">
                        Avaliação completa da propriedade e identificação das necessidades específicas.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-xl font-bold">2</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Planejamento</h3>
                    <p class="text-gray-600">
                        Desenvolvimento de um plano personalizado com cronograma e orçamento detalhados.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-xl font-bold">3</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Implementação</h3>
                    <p class="text-gray-600">
                        Instalação e configuração das soluções tecnológicas com treinamento da equipe.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <span class="text-white text-xl font-bold">4</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Acompanhamento</h3>
                    <p class="text-gray-600">
                        Monitoramento contínuo dos resultados e suporte técnico especializado.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-green-600">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl lg:text-4xl font-bold text-white mb-6">
                Pronto para Começar sua Transformação Digital?
            </h2>
            <p class="text-xl text-green-100 mb-8 leading-relaxed">
                Entre em contato conosco e agende uma consulta gratuita para conhecer as melhores soluções para seu negócio.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('site.contato') }}" class="inline-flex items-center justify-center px-8 py-4 bg-white text-green-600 text-lg font-semibold rounded-lg hover:bg-gray-100 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Agendar Consulta
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </a>
                <a href="tel:+5511999999999" class="inline-flex items-center justify-center px-8 py-4 border-2 border-white text-white text-lg font-semibold rounded-lg hover:bg-white hover:text-green-600 transition-all duration-300">
                    Ligue Agora: (11) 99999-9999
                </a>
            </div>
        </div>
    </section>
@endsection
