@props([
    'icon' => '',
    'title' => '',
    'description' => '',
    'features' => [],
])

<motion.div
    initial="hidden"
    whileInView="visible"
    variants="fadeIn"
    whileHover="cardHover"
    class="group relative bg-white rounded-2xl shadow-xl p-8 hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-3 border border-gray-100 hover:border-green-200"
>
    <!-- Gradient Background -->
    <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 rounded-2xl"></div>

    <!-- Icon with animated background -->
    <motion.div
        whileHover="iconHover"
        class="relative w-20 h-20 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center mb-8 mx-auto shadow-lg group-hover:shadow-xl transition-all duration-300"
    >
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $icon !!}
        </svg>

        <!-- Animated ring -->
        <motion.div
            animate="ring"
            class="absolute inset-0 rounded-2xl border-2 border-green-300 opacity-0 group-hover:opacity-100"
        ></motion.div>
    </motion.div>

    <div class="relative z-10">
        <h3 class="text-2xl font-bold text-gray-900 mb-4 text-center group-hover:text-green-700 transition-colors duration-300">
            {{ $title }}
        </h3>
        <p class="text-gray-600 text-center leading-relaxed mb-6">
            {{ $description }}
        </p>

        @if (!empty($features))
            <div class="space-y-3 mb-8">
                @foreach ($features as $feature)
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $feature }}
                    </div>
                @endforeach
            </div>
        @endif

        <motion.a
            href="{{ route('site.servicos') }}"
            whileHover="buttonHover"
            class="block w-full bg-gradient-to-r from-green-600 to-green-500 text-white text-center py-4 px-6 rounded-xl font-semibold hover:from-green-700 hover:to-green-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:-translate-y-1"
        >
            Saiba Mais
            <svg class="inline-block ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </motion.a>
    </div>
</motion.div>
