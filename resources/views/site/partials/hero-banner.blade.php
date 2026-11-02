@php
    // Defina aqui os slides do banner
    $slides = [
       
        [
            'mediaType' => 'image',
            'imageUrl' => 'https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=1600&q=80',
            'imageAlt' => 'Consultoria agronômica em campo com lavoura ao fundo',
            'title' => 'Consultoria Especializada em Campo',
            'text' => 'Diagnóstico, plano e acompanhamento para as principais culturas em todo o Brasil.',
            'primary' => ['label' => 'Solicitar Proposta', 'href' => route('site.contato')],
            'secondary' => ['label' => 'Nossos Projetos', 'href' => route('site.portfolio')],
        ],
        [
            'mediaType' => 'video',
            'videoUrl' => 'https://videos.pexels.com/video-files/856943/856943-hd_1920_1080_25fps.mp4',
            'title' => 'Tecnologia e Inovação para o Agronegócio',
            'text' => 'Soluções completas para transformar sua produção, do plantio à colheita, com dados e precisão.',
            'primary' => ['label' => 'Fale com um especialista', 'href' => route('site.contato')],
            'secondary' => ['label' => 'Ver serviços', 'href' => route('site.servicos')],
        ]
    ];
@endphp

<section class="relative h-screen text-white overflow-hidden">
    <div class="swiper hero-banner-swiper absolute top-0 left-0 w-full h-full z-0">
        <div class="swiper-wrapper">
            @foreach ($slides as $slide)
                <div class="swiper-slide">
                    <!-- Media Background -->
                    <div class="absolute top-0 left-0 w-full h-full">
                        @if ($slide['mediaType'] === 'video')
                            <video class="w-full h-full object-cover" autoplay muted loop playsinline>
                                <source src="{{ $slide['videoUrl'] }}" type="video/mp4">
                                Seu navegador não suporta vídeos HTML5.
                            </video>
                        @else
                            @php
                                $path_parts = pathinfo($slide['imageUrl']);
                            @endphp

                            @if (isset($path_parts['extension']))
                                @php
                                    // Extrai o caminho e a extensão para montar os nomes das imagens responsivas
                                    $basename = $path_parts['dirname'] . '/' . $path_parts['filename'];
                                    $extension = $path_parts['extension'];
                                @endphp
                                <picture>
                                    <source srcset="{{ $basename }}_576.{{ $extension }}" media="(max-width: 576px)">
                                    <source srcset="{{ $basename }}_1024.{{ $extension }}" media="(max-width: 1024px)">
                                    <source srcset="{{ $basename }}_1440.{{ $extension }}" media="(max-width: 1440px)">
                                    <img src="{{ $slide['imageUrl'] }}" alt="{{ $slide['imageAlt'] }}" class="w-full h-full object-cover">
                                </picture>
                            @else
                                {{-- Fallback para URLs sem uma extensão clara (como as do Unsplash) --}}
                                <img src="{{ $slide['imageUrl'] }}" alt="{{ $slide['imageAlt'] }}" class="w-full h-full object-cover">
                            @endif
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Overlay Gradient -->
    <div class="absolute top-0 left-0 w-full h-full bg-black/50 z-10"></div>

    <!-- Content -->
    <div class="relative z-20 flex items-center justify-center h-full">
        <div class="swiper-content-swiper w-full">
            <div class="swiper-wrapper">
                @foreach ($slides as $slide)
                <div class="swiper-slide">
                    <div class="text-center px-4">
                        <div class="max-w-4xl mx-auto">
                            <h1 class="text-4xl md:text-6xl font-extrabold mb-4 leading-tight" style="text-shadow: 2px 2px 8px rgba(0,0,0,0.7);">
                                {{ $slide['title'] }}
                            </h1>
                            <p class="text-lg md:text-xl mb-8 max-w-2xl mx-auto" style="text-shadow: 1px 1px 4px rgba(0,0,0,0.7);">
                                {{ $slide['text'] }}
                            </p>
                            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                                <a href="{{ $slide['primary']['href'] }}" class="inline-block py-3 px-8 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg transition-transform transform hover:scale-105 shadow-lg">
                                    {{ $slide['primary']['label'] }}
                                </a>
                                <a href="{{ $slide['secondary']['href'] }}" class="inline-block py-3 px-8 bg-white/20 hover:bg-white/30 backdrop-blur-sm border border-white/50 text-white font-bold rounded-lg transition-transform transform hover:scale-105 shadow-lg">
                                    {{ $slide['secondary']['label'] }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    
    <!-- Navigation Buttons -->
    <div class="swiper-button-prev text-white z-30"></div>
    <div class="swiper-button-next text-white z-30"></div>
</section>

@push('scripts-topo')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
@endpush

@push('scripts-rodape')
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const contentSwiper = new Swiper('.swiper-content-swiper', {
                loop: true,
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
                allowTouchMove: false,
            });

            const bannerSwiper = new Swiper('.hero-banner-swiper', {
                loop: true,
                autoplay: {
                    delay: 8000,
                    disableOnInteraction: false,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                thumbs: {
                    swiper: contentSwiper,
                },
            });
        });
    </script>
@endpush
