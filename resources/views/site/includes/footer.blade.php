<footer class="relative overflow-hidden border-t border-slate-800 bg-slate-950 text-slate-300">
    <div class="pointer-events-none absolute inset-0 opacity-40">
        <div class="absolute -left-16 top-0 h-72 w-72 rounded-full bg-cyan-500/20 blur-3xl"></div>
        <div class="absolute -right-16 bottom-0 h-72 w-72 rounded-full bg-emerald-500/20 blur-3xl"></div>
    </div>

    <div class="relative mx-auto max-w-7xl px-6 py-14">
        <div class="grid gap-10 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <a href="/" class="inline-flex items-center gap-3">
                    <img src="{{ asset('images/logo.png') }}" alt="Champions Edge Logo" class="h-12 w-auto rounded-md bg-white/5 p-1">
                </a>
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-slate-400">
                    Premium sports facilities, flexible memberships, and seamless online reservations for every athlete.
                </p>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-[0.16em] text-white">Quick Links</h4>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="/" class="transition hover:text-cyan-300">Home</a></li>
                    <li><a href="/about-us" class="transition hover:text-cyan-300">About Us</a></li>
                    <li><a href="/facilities" class="transition hover:text-cyan-300">Facilities</a></li>
                    <li><a href="/training-sessions" class="transition hover:text-cyan-300">Training Sessions</a></li>
                    <li><a href="/booking" class="transition hover:text-cyan-300">Book Now</a></li>
                    <li><a href="/member/register" class="transition hover:text-cyan-300">Member Registration</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-[0.16em] text-white">Facilities</h4>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="/facilities" class="transition hover:text-cyan-300">Swimming Pool</a></li>
                    <li><a href="/facilities" class="transition hover:text-cyan-300">Indoor Stadium</a></li>
                    <li><a href="/facilities" class="transition hover:text-cyan-300">Outdoor Stadium</a></li>
                    <li><a href="/facilities" class="transition hover:text-cyan-300">Gym & Fitness</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-sm font-bold uppercase tracking-[0.16em] text-white">Contact</h4>
                <div class="mt-4 space-y-2 text-sm text-slate-400">
                    <p>Champions Edge Sports Complex</p>
                    <p>Colombo, Sri Lanka</p>
                    <p><a href="mailto:info@championsedge.com" class="transition hover:text-cyan-300">info@championsedge.com</a></p>
                    <p><a href="tel:+94112345678" class="transition hover:text-cyan-300">+94 11 234 5678</a></p>
                </div>

                <div class="mt-5 flex items-center gap-3 text-slate-300">
                    <a href="#" aria-label="Facebook" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 transition hover:border-cyan-400 hover:text-cyan-300">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" aria-label="Instagram" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 transition hover:border-cyan-400 hover:text-cyan-300">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" aria-label="YouTube" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 transition hover:border-cyan-400 hover:text-cyan-300">
                        <i class="fab fa-youtube"></i>
                    </a>
                    <a href="#" aria-label="X" class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700 bg-slate-900 transition hover:border-cyan-400 hover:text-cyan-300">
                        <i class="fab fa-x-twitter"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-10 border-t border-slate-800 pt-5">
            <div class="flex flex-col items-start justify-between gap-3 text-xs text-slate-500 sm:flex-row sm:items-center">
                <p>© {{ date('Y') }} Champions Edge. All rights reserved.</p>
                <div class="flex items-center gap-4">
                    <a href="/privacy-policy" class="transition hover:text-cyan-300">Privacy Policy</a>
                    <a href="/contact" class="transition hover:text-cyan-300">Support</a>
                </div>
            </div>
        </div>
    </div>
</footer>
