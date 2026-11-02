@php
    $slides = [
        [
            'title' => 'Agricultura de Precisão para Produtividade Máxima',
            'text' => 'Mapeamento, taxa variável e monitoramento inteligente para reduzir custos e aumentar rendimento.',
            'primary' => ['label' => 'Fale com um especialista', 'href' => route('site.contato')],
            'secondary' => ['label' => 'Ver serviços', 'href' => route('site.servicos')],
            'image' => 'https://images.unsplash.com/photo-1574323347407-f5e1ad6d020b?auto=format&fit=crop&w=1600&q=80',
            'alt' => 'Maquinário agrícola com sensores realizando agricultura de precisão',
        ],
        [
            'title' => 'Diagnóstico, Plano e Acompanhamento em Campo',
            'text' => 'Consultoria end‑to‑end para algodão, milho, soja e feijão em todo o Brasil.',
            'primary' => ['label' => 'Solicitar proposta', 'href' => route('site.contato')],
            'secondary' => ['label' => 'Casos de sucesso', 'href' => route('site.portfolio')],
            'image' => 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=1600&q=80',
            'alt' => 'Consultoria agronômica em campo com lavoura ao fundo',
        ],
        [
            'title' => 'Pesquisa Aplicada para Decisão com Evidência',
            'text' => 'Ensaios comparativos e validação de tecnologias com parceiros e instituições.',
            'primary' => ['label' => 'Conheça nossa estrutura', 'href' => route('site.sobre')],
            'secondary' => ['label' => 'Notícias & Blog', 'href' => route('site.servicos')],
            'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1600&q=80',
            'alt' => 'Análise de dados e pesquisa aplicada em agronegócio',
        ],
    ];
@endphp

<section id="hero" class="hero-wrapper relative overflow-hidden z-10">
    <div class="swiper hero-swiper relative z-0" role="region" aria-roledescription="carousel" aria-label="Destaques De Lollo">
        <div class="swiper-wrapper">
            @foreach ($slides as $slide)
                <div class="swiper-slide bg-cover bg-center"
                     style="background-image: url('{{ $slide['image'] }}');"
                     role="img"
                     aria-label="{{ $slide['alt'] }}">

                    <!-- Gradiente de overlay para legibilidade -->
                    <div class="absolute inset-0 bg-gradient-to-r from-gray-900/80 via-gray-900/60 to-transparent"></div>

                    <!-- Conteúdo centralizado -->
                    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
                        <div class="flex items-center min-h-[450px] md:min-h-[550px] lg:min-h-[650px]">
                            <div class="hero-text-content space-y-6 max-w-2xl">
                                <h2 class="hero-heading text-4xl md:text-5xl font-bold text-white" data-swiper-parallax="-300">{{ $slide['title'] }}</h2>
                                <p class="hero-subtext text-lg md:text-xl text-gray-200" data-swiper-parallax="-200">{{ $slide['text'] }}</p>

                                <div class="flex flex-col sm:flex-row gap-4 pt-2" data-swiper-parallax="-100">
                                    <a href="{{ $slide['primary']['href'] }}" class="btn-primary inline-flex items-center justify-center">
                                        {{ $slide['primary']['label'] }}
                                    </a>
                                    <a href="{{ $slide['secondary']['href'] }}" class="btn-secondary-outline inline-flex items-center justify-center">
                                        {{ $slide['secondary']['label'] }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Controles -->
        <div class="hero-controls pointer-events-none">
            <div class="swiper-pagination hidden"></div>
        </div>
        <button class="swiper-button-prev hero-nav" aria-label="Slide anterior"></button>
        <button class="swiper-button-next hero-nav" aria-label="Próximo slide"></button>
    </div>

    
</section>

@push('scripts-topo')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
@endpush

@push('scripts-rodape')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        (function() {
            const swiper = new Swiper('.hero-swiper', {
                loop: true,
                speed: 1000,
                parallax: true,
                autoplay: { delay: 6000, disableOnInteraction: false },
                watchSlidesProgress: true,
                pagination: { el: '.swiper-pagination', clickable: true, dynamicBullets: true },
                navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                a11y: { enabled: true },

                on: {
                    init: function () {
                        this.slides[this.activeIndex].classList.add('is-animated');
                    },
                    slideChangeTransitionStart: function () {
                        this.slides.forEach(s => s.classList.remove('is-animated'));
                        this.slides[this.activeIndex].classList.add('is-animated');
                    }
                }
            });

            // Pausar autoplay em focus/hover por acessibilidade
            const el = document.querySelector('.hero-swiper');
            if (el) {
                el.addEventListener('mouseenter', () => swiper.autoplay.stop());
                el.addEventListener('mouseleave', () => swiper.autoplay.start());
                el.addEventListener('focusin',   () => swiper.autoplay.stop());
                el.addEventListener('focusout',  () => swiper.autoplay.start());
            }

            // Navegação por teclado
            document.addEventListener('keydown', (e) => {
                if (document.activeElement.closest('.hero-swiper')) {
                    if (e.key === 'ArrowLeft') swiper.slidePrev();
                    if (e.key === 'ArrowRight') swiper.slideNext();
                }
            });
        })();
    </script>
@endpush


