@extends('site.layout')

@section('title', 'Sobre Nós - De Lollo Agronegócios')
@section('description', 'Conheça a história da De Lollo Agronegócios, nossa missão e os valores que nos guiam no desenvolvimento de soluções tecnológicas para o campo.')

@section('conteudo')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-green-50 to-green-100 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    Sobre a <span class="text-green-600">De Lollo</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Somos especialistas em tecnologia agrícola com mais de uma década de experiência no mercado brasileiro.
                </p>
            </div>
        </div>
    </section>

    <!-- Story Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Nossa História</h2>
                    <div class="space-y-4 text-gray-600 leading-relaxed">
                        <p>
                            Fundada em 2013, a De Lollo Agronegócios nasceu da paixão pela inovação e do compromisso em transformar o agronegócio brasileiro.
                            Iniciamos nossa jornada identificando as principais necessidades dos produtores rurais e desenvolvendo soluções tecnológicas acessíveis e eficientes.
                        </p>
                        <p>
                            Ao longo dos anos, expandimos nossa atuação para todo o território nacional, atendendo desde pequenas propriedades familiares até grandes corporações agrícolas.
                            Nossa missão sempre foi clara: democratizar o acesso à tecnologia no campo e contribuir para uma agricultura mais sustentável e produtiva.
                        </p>
                        <p>
                            Hoje, somos referência no mercado de tecnologia agrícola, com mais de 500 projetos implementados e uma taxa de satisfação de 98% entre nossos clientes.
                            Continuamos investindo em pesquisa e desenvolvimento para oferecer sempre as melhores soluções do mercado.
                        </p>
                    </div>
                </div>
                <div class="relative">
                    <img src="{{ asset('assets/images/equipe-trabalho.jpg') }}" alt="Equipe De Lollo trabalhando" class="w-full h-96 object-cover rounded-2xl shadow-xl" onerror="this.src='https://images.unsplash.com/photo-1522071820081-009f0129c71c?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80'">
                </div>
            </div>
        </div>
    </section>

    <!-- Mission Vision Values -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Missão, Visão e Valores</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Os princípios que nos guiam e definem nossa forma de trabalhar com os clientes.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Missão -->
                <div class="text-center bg-white p-8 rounded-xl shadow-lg">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Missão</h3>
                    <p class="text-gray-600">
                        Revolucionar o agronegócio brasileiro através da inovação tecnológica, oferecendo soluções acessíveis e eficientes que aumentem a produtividade e sustentabilidade do produtor rural.
                    </p>
                </div>

                <!-- Visão -->
                <div class="text-center bg-white p-8 rounded-xl shadow-lg">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5h5m-1-5v5m-5-5V7a4 4 0 00-8 0v4H3l5-5v4h5z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Visão</h3>
                    <p class="text-gray-600">
                        Ser a principal referência em tecnologia agrícola no Brasil, reconhecida pela excelência em soluções inovadoras e pelo compromisso com o desenvolvimento sustentável do agronegócio nacional.
                    </p>
                </div>

                <!-- Valores -->
                <div class="text-center bg-white p-8 rounded-xl shadow-lg">
                    <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-4">Valores</h3>
                    <p class="text-gray-600">
                        Inovação, sustentabilidade, compromisso com o cliente, ética e responsabilidade social são os pilares que fundamentam todas as nossas ações e decisões empresariais.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">Nossa Equipe</h2>
                <p class="text-lg text-gray-600 max-w-3xl mx-auto">
                    Profissionais especializados e comprometidos com a excelência em tecnologia agrícola.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Team Member 1 -->
                <div class="text-center">
                    <div class="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center text-gray-600 text-4xl font-bold">
                        RM
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Rogério Martins</h3>
                    <p class="text-green-600 mb-2">CEO & Fundador</p>
                    <p class="text-gray-600 text-sm">
                        Especialista em tecnologia agrícola com mais de 15 anos de experiência no desenvolvimento de soluções inovadoras para o campo.
                    </p>
                </div>

                <!-- Team Member 2 -->
                <div class="text-center">
                    <div class="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center text-gray-600 text-4xl font-bold">
                        AL
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Ana Luiza</h3>
                    <p class="text-green-600 mb-2">Diretora Técnica</p>
                    <p class="text-gray-600 text-sm">
                        Engenheira agrônoma especializada em agricultura de precisão e sistemas de monitoramento inteligente.
                    </p>
                </div>

                <!-- Team Member 3 -->
                <div class="text-center">
                    <div class="w-32 h-32 bg-gray-300 rounded-full mx-auto mb-4 flex items-center justify-center text-gray-600 text-4xl font-bold">
                        CS
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">Carlos Silva</h3>
                    <p class="text-green-600 mb-2">Gerente de Projetos</p>
                    <p class="text-gray-600 text-sm">
                        Responsável pela coordenação e implementação de projetos, garantindo a entrega de soluções personalizadas.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Achievements -->
    <section class="py-20 bg-green-600">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl font-bold text-white mb-4">Nossos Números</h2>
                <p class="text-green-100 text-lg">
                    Resultados que comprovam nossa excelência e compromisso com os clientes.
                </p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="text-4xl lg:text-5xl font-bold text-white mb-2">500+</div>
                    <div class="text-green-100">Projetos Implementados</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl lg:text-5xl font-bold text-white mb-2">98%</div>
                    <div class="text-green-100">Taxa de Satisfação</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl lg:text-5xl font-bold text-white mb-2">10+</div>
                    <div class="text-green-100">Anos de Experiência</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl lg:text-5xl font-bold text-white mb-2">24/7</div>
                    <div class="text-green-100">Suporte Disponível</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 mb-6">
                Vamos Conversar Sobre Seu Projeto?
            </h2>
            <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                Entre em contato conosco e descubra como podemos ajudar a transformar seu negócio agrícola com tecnologia de ponta.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('site.contato') }}" class="inline-flex items-center justify-center px-8 py-4 bg-green-600 text-white text-lg font-semibold rounded-lg hover:bg-green-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                    Entre em Contato
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </a>
                <a href="{{ route('site.servicos') }}" class="inline-flex items-center justify-center px-8 py-4 border-2 border-green-600 text-green-600 text-lg font-semibold rounded-lg hover:bg-green-50 transition-all duration-300">
                    Conheça Nossos Serviços
                </a>
            </div>
        </div>
    </section>
@endsection
