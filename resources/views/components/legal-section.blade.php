@props(['number', 'title'])

<section id="section-{{ $number }}"
         data-legal-section
         data-title="{{ $number }}. {{ $title }}"
         class="scroll-mt-24 rounded-xl border border-ink-100 bg-cream-50 p-6 md:p-8">
    <div class="flex items-start gap-4 mb-5">
        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-terracotta-50 text-sm font-bold text-terracotta-700">{{ $number }}</span>
        <h2 class="pt-0.5 font-serif text-2xl font-medium leading-snug text-ink-900">{{ $title }}</h2>
    </div>
    <div class="legal-content md:pl-[52px]">
        {{ $slot }}
    </div>
</section>
