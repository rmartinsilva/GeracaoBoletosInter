<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'De Lollo Agronegócios - Tecnologia e Inovação no Campo')</title>

    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('description', 'De Lollo Agronegócios oferece soluções tecnológicas inovadoras para o agronegócio, com foco em eficiência e sustentabilidade.')">
    <meta name="keywords" content="@yield('keywords', 'agronegócio, tecnologia agrícola, inovação rural, soluções para o campo')">
    <meta name="author" content="De Lollo Agronegócios">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="@yield('og_url', request()->url())">
    <meta property="og:title" content="@yield('og_title', 'De Lollo Agronegócios')">
    <meta property="og:description" content="@yield('og_description', 'Soluções tecnológicas inovadoras para o agronegócio')">
    <meta property="og:image" content="@yield('og_image', asset('assets/images/og-image.jpg'))">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="@yield('og_url', request()->url())">
    <meta property="twitter:title" content="@yield('og_title', 'De Lollo Agronegócios')">
    <meta property="twitter:description" content="@yield('og_description', 'Soluções tecnológicas inovadoras para o agronegócio')">
    <meta property="twitter:image" content="@yield('og_image', asset('assets/images/og-image.jpg'))">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="57x57" href="{{ asset('assets/images/favicon/apple-icon-57x57.png') }}">
    <link rel="apple-touch-icon" sizes="60x60" href="{{ asset('assets/images/favicon/apple-icon-60x60.png') }}">
    <link rel="apple-touch-icon" sizes="72x72" href="{{ asset('assets/images/favicon/apple-icon-72x72.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/images/favicon/apple-icon-76x76.png') }}">
    <link rel="apple-touch-icon" sizes="114x114" href="{{ asset('assets/images/favicon/apple-icon-114x114.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/images/favicon/apple-icon-120x120.png') }}">
    <link rel="apple-touch-icon" sizes="144x144" href="{{ asset('assets/images/favicon/apple-icon-144x144.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/images/favicon/apple-icon-152x152.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/favicon/apple-icon-180x180.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('assets/images/favicon/android-icon-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/images/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="96x96" href="{{ asset('assets/images/favicon/favicon-96x96.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/images/favicon/favicon-16x16.png') }}">
    <meta name="msapplication-TileColor" content="#88B04B">
    <meta name="msapplication-TileImage" content="{{ asset('assets/images/favicon/ms-icon-144x144.png') }}">

    <!-- Stylesheets -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        #main-nav.scrolled {
            background-color: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">

    <!-- Scripts do topo -->
    @stack('scripts-topo')

    <!-- Framer Motion via CDN -->
    <script src="https://unpkg.com/framer-motion@10.16.4/dist/framer-motion.js" defer></script>
</head>

<body class="font-['Inter'] bg-gradient-to-br from-slate-50 via-white to-green-50 text-gray-900 antialiased overflow-x-hidden">
    <!-- Navigation -->
    <nav id="main-nav" class="fixed top-0 left-0 right-0 z-50 bg-white/75 backdrop-blur-sm transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="flex-shrink-0 flex items-center">
                        <img class="h-10 w-auto" src="{{ asset('assets/imagens/logo-cliente-painel.png') }}" alt="De Lollo Agronegócios">
                    </div>

                    <!-- Desktop Navigation -->
                    <div class="hidden md:ml-10 md:flex md:space-x-8">
                        <a href="{{ route('site.index') }}" class="nav-link text-gray-800 hover:text-green-600 px-3 py-2 text-sm font-medium transition-colors duration-200 border-b-2 {{ request()->routeIs('site.index') ? 'border-green-600' : 'border-transparent' }}">
                            Início
                        </a>
                        <a href="{{ route('site.sobre') }}" class="nav-link text-gray-800 hover:text-green-600 px-3 py-2 text-sm font-medium transition-colors duration-200 border-b-2 {{ request()->routeIs('site.sobre') ? 'border-green-600' : 'border-transparent' }}">
                            Sobre
                        </a>
                        <a href="{{ route('site.servicos') }}" class="nav-link text-gray-800 hover:text-green-600 px-3 py-2 text-sm font-medium transition-colors duration-200 border-b-2 {{ request()->routeIs('site.servicos') ? 'border-green-600' : 'border-transparent' }}">
                            Serviços
                        </a>
                        <a href="{{ route('site.portfolio') }}" class="nav-link text-gray-800 hover:text-green-600 px-3 py-2 text-sm font-medium transition-colors duration-200 border-b-2 {{ request()->routeIs('site.portfolio') ? 'border-green-600' : 'border-transparent' }}">
                            Portfólio
                        </a>
                        <a href="{{ route('site.contato') }}" class="nav-link text-gray-800 hover:text-green-600 px-3 py-2 text-sm font-medium transition-colors duration-200 border-b-2 {{ request()->routeIs('site.contato') ? 'border-green-600' : 'border-transparent' }}">
                            Contato
                        </a>
                    </div>
                </div>

                <!-- CTA Button -->
                <div class="flex items-center space-x-4">
                    <a href="/painel/login" class="cta-button hidden md:inline-flex items-center px-4 py-2 border border-green-600 text-sm font-medium rounded-md text-green-600 hover:bg-green-600 hover:text-white transition-colors duration-200">
                        Área do Cliente
                    </a>

                    <!-- Mobile menu button -->
                    <button type="button" class="md:hidden bg-transparent rounded-md p-2 inline-flex items-center justify-center text-gray-800 hover:text-black focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500" id="mobile-menu-button">
                        <span class="sr-only">Abrir menu principal</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 bg-gray-900/90 backdrop-blur-sm shadow-lg">
                <a href="{{ route('site.index') }}" class="text-gray-200 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('site.index') ? 'bg-gray-800 text-white' : '' }}">
                    Início
                </a>
                <a href="{{ route('site.sobre') }}" class="text-gray-200 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('site.sobre') ? 'bg-gray-800 text-white' : '' }}">
                    Sobre
                </a>
                <a href="{{ route('site.servicos') }}" class="text-gray-200 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('site.servicos') ? 'bg-gray-800 text-white' : '' }}">
                    Serviços
                </a>
                <a href="{{ route('site.portfolio') }}" class="text-gray-200 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('site.portfolio') ? 'bg-gray-800 text-white' : '' }}">
                    Portfólio
                </a>
                <a href="{{ route('site.contato') }}" class="text-gray-200 hover:text-white block px-3 py-2 rounded-md text-base font-medium {{ request()->routeIs('site.contato') ? 'bg-gray-800 text-white' : '' }}">
                    Contato
                </a>
                <a href="/painel/login" class="text-green-400 hover:text-green-300 block px-3 py-2 rounded-md text-base font-medium">
                    Área do Cliente
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        @yield('conteudo')
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center mb-4">
                        <img class="h-8 w-auto" src="{{ asset('assets/imagens/logo-cliente-painel.png') }}" alt="De Lollo Agronegócios">
                    </div>
                    <p class="text-gray-300 mb-4">
                        Inovação e tecnologia no agronegócio brasileiro. Oferecemos soluções completas para modernizar e otimizar sua produção rural.
                    </p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-300 hover:text-green-400 transition-colors duration-200">
                            <span class="sr-only">Facebook</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-green-400 transition-colors duration-200">
                            <span class="sr-only">Instagram</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.62 5.367 11.987 11.988 11.987 6.62 0 11.987-5.367 11.987-11.987C24.004 5.367 18.637.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.337-1.297-.889-.807-1.337-1.807-1.337-3.001 0-1.193.448-2.193 1.337-3.001.889-.807 2.04-1.297 3.337-1.297 1.297 0 2.448.49 3.337 1.297.889.808 1.337 1.808 1.337 3.001 0 1.194-.448 2.194-1.337 3.001-.889.807-2.04 1.297-3.337 1.297z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-gray-300 hover:text-green-400 transition-colors duration-200">
                            <span class="sr-only">LinkedIn</span>
                            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold text-green-400 mb-4">Links Rápidos</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('site.sobre') }}" class="text-gray-300 hover:text-green-400 transition-colors duration-200">Sobre Nós</a></li>
                        <li><a href="{{ route('site.servicos') }}" class="text-gray-300 hover:text-green-400 transition-colors duration-200">Serviços</a></li>
                        <li><a href="{{ route('site.portfolio') }}" class="text-gray-300 hover:text-green-400 transition-colors duration-200">Portfólio</a></li>
                        <li><a href="{{ route('site.contato') }}" class="text-gray-300 hover:text-green-400 transition-colors duration-200">Contato</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div>
                    <h3 class="text-lg font-semibold text-green-400 mb-4">Contato</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li class="flex items-center">
                            <svg class="h-5 w-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            São Paulo, Brasil
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            contato@delollo.com.br
                        </li>
                        <li class="flex items-center">
                            <svg class="h-5 w-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            (11) 99999-9999
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; {{ date('Y') }} De Lollo Agronegócios. Todos os direitos reservados. | Desenvolvido por <a href="https://usinaweb.com.br" class="text-green-400 hover:text-green-300 transition-colors duration-200">UsinaWeb</a></p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Animation variants for Framer Motion
        const fadeIn = {
            hidden: { opacity: 0, y: 20 },
            visible: {
                opacity: 1,
                y: 0,
                transition: { duration: 0.6 }
            }
        };

        const slideInLeft = {
            hidden: { opacity: 0, x: -50 },
            visible: {
                opacity: 1,
                x: 0,
                transition: { duration: 0.7 }
            }
        };

        const navItem = {
            hover: {
                scale: 1.05,
                transition: { duration: 0.2 }
            }
        };

        const logoHover = {
            hover: {
                scale: 1.1,
                rotate: 5,
                transition: { duration: 0.3 }
            }
        };

        const buttonHover = {
            hover: {
                scale: 1.05,
                y: -2,
                transition: { duration: 0.2 }
            }
        };

        const buttonScale = {
            hover: {
                scale: 1.05,
                transition: { duration: 0.2 }
            }
        };

        const underline = {
            hidden: { scaleX: 0 },
            visible: {
                scaleX: 1,
                transition: { duration: 0.3 }
            }
        };

        const mobileNavItem = {
            hidden: { opacity: 0, x: -20 },
            visible: {
                opacity: 1,
                x: 0,
                transition: { duration: 0.3 }
            }
        };

        const pulseAnimation = {
            scale: [1, 1.2, 1],
            transition: {
                duration: 2,
                repeat: Infinity,
                ease: "easeInOut"
            }
        };

        const menuIcon = {
            animate: {
                rotate: [0, 90, 0],
                transition: { duration: 0.3 }
            }
        };

        const tap = {
            scale: 0.95,
            transition: { duration: 0.1 }
        };

        const float = {
            y: [0, -10, 0],
            transition: {
                duration: 3,
                repeat: Infinity,
                ease: "easeInOut"
            }
        };

        const floatReverse = {
            y: [0, 10, 0],
            transition: {
                duration: 4,
                repeat: Infinity,
                ease: "easeInOut"
            }
        };

        const imageHover = {
            rotate: [0, 2, -2, 0],
            transition: { duration: 0.5 }
        };

        const bounce = {
            y: [0, -10, 0],
            transition: {
                duration: 1.5,
                repeat: Infinity,
                ease: "easeInOut"
            }
        };

        const scrollIndicator = {
            y: [0, 10, 0],
            opacity: [0.5, 1, 0.5],
            transition: {
                duration: 2,
                repeat: Infinity,
                ease: "easeInOut"
            }
        };

        const cardHover = {
            y: -10,
            scale: 1.02,
            transition: { duration: 0.3 }
        };

        const iconHover = {
            scale: 1.1,
            rotate: 5,
            transition: { duration: 0.3 }
        };

        const ring = {
            scale: [1, 1.2, 1],
            opacity: [0.7, 0.3, 0.7],
            transition: {
                duration: 2,
                repeat: Infinity,
                ease: "easeInOut"
            }
        };

        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');

            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });

                // Close mobile menu when clicking outside
                document.addEventListener('click', function(event) {
                    if (!mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                        mobileMenu.classList.add('hidden');
                    }
                });
            }

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Intersection Observer for scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fadeIn');
                    }
                });
            }, observerOptions);

            // Observe elements with animation classes
            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });

            // Handle nav scroll
            const nav = document.getElementById('main-nav');
            if (nav) {
                window.addEventListener('scroll', () => {
                    if (window.scrollY > 50) {
                        nav.classList.add('scrolled');
                    } else {
                        nav.classList.remove('scrolled');
                    }
                });
            }
        });
    </script>

    <!-- Scripts do rodapé -->
    @stack('scripts-rodape')
</body>
</html>
