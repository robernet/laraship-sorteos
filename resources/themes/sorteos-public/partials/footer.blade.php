<footer class="srt-footer">
    <div class="srt-footer__links">
        <a href="{{ route('sorteos.boletos.resend-form') }}">Reenviar mis boletos</a>
        <a href="mailto:{{ \Settings::get('site_email', 'contacto@itson.mx') }}">Contacto</a>
    </div>
    <div>Sorteos ITSON &copy; {{ date('Y') }} &mdash; Instituto Tecnológico de Sonora</div>
</footer>
