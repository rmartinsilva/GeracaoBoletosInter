@extends('site.layout')

@section('title', 'Contato - De Lollo Agronegócios')
@section('description', 'Entre em contato conosco para conhecer nossas soluções em tecnologia agrícola e agendar uma consultoria especializada.')

@section('conteudo')
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-green-50 to-green-100 py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                    Entre em <span class="text-green-600">Contato</span>
                </h1>
                <p class="text-lg md:text-xl text-gray-600 mb-8 max-w-3xl mx-auto">
                    Estamos prontos para ouvir suas necessidades e apresentar as melhores soluções tecnológicas para seu negócio agrícola.
                </p>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
                <!-- Contact Form -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Envie sua Mensagem</h2>
                    <p class="text-gray-600 mb-8">
                        Preencha o formulário abaixo e nossa equipe entrará em contato em até 24 horas.
                    </p>

                    <form class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="nome" class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
                                <input type="text" id="nome" name="nome" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">E-mail *</label>
                                <input type="email" id="email" name="email" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="telefone" class="block text-sm font-medium text-gray-700 mb-2">Telefone</label>
                                <input type="tel" id="telefone" name="telefone"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200">
                            </div>
                            <div>
                                <label for="empresa" class="block text-sm font-medium text-gray-700 mb-2">Empresa</label>
                                <input type="text" id="empresa" name="empresa"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200">
                            </div>
                        </div>

                        <div>
                            <label for="assunto" class="block text-sm font-medium text-gray-700 mb-2">Assunto *</label>
                            <select id="assunto" name="assunto" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200">
                                <option value="">Selecione um assunto</option>
                                <option value="consulta">Consulta Técnica</option>
                                <option value="orcamento">Solicitar Orçamento</option>
                                <option value="suporte">Suporte Técnico</option>
                                <option value="parceria">Parcerias</option>
                                <option value="outros">Outros</option>
                            </select>
                        </div>

                        <div>
                            <label for="mensagem" class="block text-sm font-medium text-gray-700 mb-2">Mensagem *</label>
                            <textarea id="mensagem" name="mensagem" rows="6" required
                                      placeholder="Descreva sua necessidade ou dúvida..."
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 resize-none"></textarea>
                        </div>

                        <button type="submit"
                                class="w-full bg-green-600 text-white py-4 px-8 rounded-lg text-lg font-semibold hover:bg-green-700 transition-all duration-300 transform hover:scale-105 shadow-lg">
                            Enviar Mensagem
                            <svg class="inline-block ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </form>
                </div>

                <!-- Contact Info -->
                <div>
                    <h2 class="text-3xl font-bold text-gray-900 mb-6">Informações de Contato</h2>
                    <p class="text-gray-600 mb-8">
                        Nossa equipe está pronta para atendê-lo. Entre em contato através dos canais abaixo ou utilize nosso formulário.
                    </p>

                    <div class="space-y-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">Endereço</h3>
                                <p class="text-gray-600">São Paulo, SP - Brasil</p>
                                <p class="text-gray-600">CEP: 01310-100</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">E-mail</h3>
                                <p class="text-gray-600">contato@delollo.com.br</p>
                                <p class="text-gray-600">comercial@delollo.com.br</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">Telefone</h3>
                                <p class="text-gray-600">(11) 99999-9999</p>
                                <p class="text-gray-600">(11) 8888-8888</p>
                            </div>
                        </div>

                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">Horário de Atendimento</h3>
                                <p class="text-gray-600">Segunda a Sexta: 8h às 18h</p>
                                <p class="text-gray-600">Sábado: 8h às 12h</p>
                                <p class="text-gray-600">Suporte 24/7 para clientes</p>
                            </div>
                        </div>
                    </div>

                    <!-- Map Placeholder -->
                    <div class="mt-8">
                        <div class="bg-gray-200 rounded-lg h-64 flex items-center justify-center">
                            <div class="text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                </svg>
                                <p>Mapa interativo será integrado aqui</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">
                    Perguntas <span class="text-green-600">Frequentes</span>
                </h2>
                <p class="text-gray-600">
                    Esclareça suas dúvidas sobre nossos serviços e soluções.
                </p>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Quanto tempo leva para implementar as soluções?
                    </h3>
                    <p class="text-gray-600">
                        O tempo de implementação varia de acordo com a complexidade da solução e tamanho da propriedade.
                        Projetos simples podem ser implementados em 2-4 semanas, enquanto soluções mais complexas podem levar 2-3 meses.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Vocês oferecem treinamento para uso das tecnologias?
                    </h3>
                    <p class="text-gray-600">
                        Sim! Oferecemos treinamento completo para todos os nossos clientes, incluindo manuais detalhados,
                        vídeos tutoriais e suporte técnico durante o período de adaptação.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Qual é o retorno sobre o investimento?
                    </h3>
                    <p class="text-gray-600">
                        Nossos clientes relatam em média 25-40% de aumento na produtividade e 15-30% de redução nos custos operacionais
                        nos primeiros 12 meses de implementação das nossas soluções.
                    </p>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                        Atuam em todo o território brasileiro?
                    </h3>
                    <p class="text-gray-600">
                        Sim, atendemos clientes em todo o Brasil. Temos equipes técnicas regionais e oferecemos suporte remoto
                        para instalação e manutenção de equipamentos em qualquer localização.
                    </p>
                </div>
            </div>
        </div>
    </section>
@endsection
