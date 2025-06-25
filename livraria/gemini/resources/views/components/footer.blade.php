{{-- Componente de Footer sem parâmetros --}}
<footer class="site-footer bg-dark text-light py-5 mt-5">
    <div class="container">
        <div class="row">
            <!-- Informações da Livraria -->
            <div class="col-lg-4 col-md-6 mb-4">
                <h5 class="footer-title mb-3">
                    <i class="fas fa-book-open me-2 text-warning"></i>
                    Livraria Mil Páginas
                </h5>
                <p class="footer-description">
                    Sua livraria online de confiança. Descubra mundos infinitos através das páginas dos nossos livros.
                </p>
                <div class="footer-stats">
                    <small class="text-muted">
                        <i class="fas fa-book me-1"></i>
                        Mais de 1000 títulos disponíveis
                    </small>
                </div>
            </div>

            <!-- Links Rápidos -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="footer-subtitle mb-3">Links Rápidos</h6>
                <ul class="footer-links list-unstyled">
                    <li><a href="{{ route('loja.index') }}" class="footer-link">Início</a></li>
                    <li><a href="{{ route('loja.catalogo') }}" class="footer-link">Catálogo</a></li>
                    <li><a href="#" class="footer-link">Ofertas</a></li>
                    <li><a href="#" class="footer-link">Lançamentos</a></li>
                    <li><a href="#" class="footer-link">Mais Vendidos</a></li>
                </ul>
            </div>

            <!-- Categorias -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="footer-subtitle mb-3">Categorias</h6>
                <ul class="footer-links list-unstyled">
                    <li><a href="#" class="footer-link">Ficção</a></li>
                    <li><a href="#" class="footer-link">Romance</a></li>
                    <li><a href="#" class="footer-link">Técnicos</a></li>
                    <li><a href="#" class="footer-link">Infantil</a></li>
                    <li><a href="#" class="footer-link">Biografias</a></li>
                </ul>
            </div>

            <!-- Suporte -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="footer-subtitle mb-3">Suporte</h6>
                <ul class="footer-links list-unstyled">
                    <li><a href="#" class="footer-link">FAQ</a></li>
                    <li><a href="#" class="footer-link">Contato</a></li>
                    <li><a href="#" class="footer-link">Trocas e Devoluções</a></li>
                    <li><a href="#" class="footer-link">Política de Privacidade</a></li>
                    <li><a href="#" class="footer-link">Termos de Uso</a></li>
                </ul>
            </div>

            <!-- Redes Sociais e Contato -->
            <div class="col-lg-2 col-md-6 mb-4">
                <h6 class="footer-subtitle mb-3">Conecte-se</h6>
                <div class="social-links mb-3">
                    <a href="#" class="social-link me-2" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="social-link me-2" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="#" class="social-link me-2" title="Twitter">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a href="#" class="social-link me-2" title="YouTube">
                        <i class="fab fa-youtube"></i>
                    </a>
                </div>
                <div class="contact-info">
                    <small class="text-muted d-block mb-1">
                        <i class="fas fa-envelope me-1"></i>
                        contato@milpaginas.com
                    </small>
                    <small class="text-muted d-block">
                        <i class="fas fa-phone me-1"></i>
                        (11) 9999-9999
                    </small>
                </div>
            </div>
        </div>

        <!-- Newsletter -->
        <div class="row mt-4 pt-4 border-top border-secondary">
            <div class="col-lg-8">
                <h6 class="mb-3">📧 Receba nossas novidades</h6>
                <form class="newsletter-form d-flex" action="#" method="POST">
                    @csrf
                    <input type="email" 
                           class="form-control me-2" 
                           placeholder="Seu e-mail" 
                           name="email" 
                           required>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
                <small class="text-muted">
                    Seja o primeiro a saber sobre lançamentos e ofertas especiais.
                </small>
            </div>
            <div class="col-lg-4 text-lg-end">
                <div class="payment-methods mt-3">
                    <small class="text-muted d-block mb-2">Formas de Pagamento:</small>
                    <div class="payment-icons">
                        <i class="fab fa-cc-visa me-2 text-info" title="Visa"></i>
                        <i class="fab fa-cc-mastercard me-2 text-warning" title="Mastercard"></i>
                        <i class="fab fa-cc-paypal me-2 text-primary" title="PayPal"></i>
                        <i class="fas fa-barcode me-2 text-success" title="Boleto"></i>
                        <i class="fas fa-qrcode text-secondary" title="PIX"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Copyright -->
        <div class="row mt-4 pt-3 border-top border-secondary">
            <div class="col-md-6">
                <small class="text-muted">
                    © {{ date('Y') }} Livraria Mil Páginas. Todos os direitos reservados.
                </small>
            </div>
            <div class="col-md-6 text-md-end">
                <small class="text-muted">
                    Desenvolvido com <i class="fas fa-heart text-danger"></i> para os amantes da leitura
                </small>
            </div>
        </div>
    </div>

    <!-- Botão Voltar ao Topo -->
    <button id="backToTop" class="btn-back-to-top" title="Voltar ao topo">
        <i class="fas fa-chevron-up"></i>
    </button>
</footer>

<style>
.site-footer {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    position: relative;
}

.footer-title {
    color: #f8f9fa;
    font-weight: 600;
}

.footer-subtitle {
    color: #adb5bd;
    font-weight: 500;
    font-size: 1rem;
}

.footer-description {
    color: #ced4da;
    line-height: 1.6;
}

.footer-links {
    margin: 0;
    padding: 0;
}

.footer-link {
    color: #adb5bd;
    text-decoration: none;
    font-size: 0.9rem;
    line-height: 2;
    transition: color 0.3s ease;
}

.footer-link:hover {
    color: #ffc107;
    text-decoration: none;
}

.social-links {
    display: flex;
}

.social-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    color: #adb5bd;
    border-radius: 50%;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-link:hover {
    background: #ffc107;
    color: #212529;
    transform: translateY(-2px);
}

.contact-info {
    font-size: 0.85rem;
}

.newsletter-form .form-control {
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: #fff;
}

.newsletter-form .form-control::placeholder {
    color: #adb5bd;
}

.newsletter-form .form-control:focus {
    background: rgba(255, 255, 255, 0.15);
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    color: #fff;
}

.payment-icons i {
    font-size: 1.5rem;
    opacity: 0.7;
    transition: opacity 0.3s ease;
}

.payment-icons i:hover {
    opacity: 1;
}

.btn-back-to-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: #ffc107;
    color: #212529;
    border: none;
    border-radius: 50%;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    z-index: 1000;
}

.btn-back-to-top.show {
    opacity: 1;
    visibility: visible;
}

.btn-back-to-top:hover {
    background: #e0a800;
    transform: translateY(-2px);
}

/* Responsivo */
@media (max-width: 768px) {
    .site-footer {
        text-align: center;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
    
    .newsletter-form .form-control {
        margin-bottom: 10px;
    }
    
    .social-links {
        justify-content: center;
    }
    
    .btn-back-to-top {
        bottom: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
    }
}

/* Animações */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.site-footer .row > div {
    animation: fadeInUp 0.6s ease-out;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Botão voltar ao topo
    const backToTopBtn = document.getElementById('backToTop');
    
    if (backToTopBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('show');
            } else {
                backToTopBtn.classList.remove('show');
            }
        });
        
        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Newsletter form
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input[name="email"]').value;
            
            // Simular envio
            if (email) {
                alert('Obrigado! Você receberá nossas novidades em breve.');
                this.querySelector('input[name="email"]').value = '';
            }
        });
    }
});
</script>