// Main JavaScript for Portfolio Website

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all features
    initializeMobileMenu();
    initializeNavigation();
    initializeTypingAnimation();
    initializeScrollEffects();
    initializeParticleSystem();
    initializeAnimations();
    
    // Start dynamic effects
    createDynamicBackground();
    observeElements();
    addScrollProgress();
    initializeEasterEggs();
});

// Initialize enhanced animations
function initializeAnimations() {
    const projectCards = document.querySelectorAll('.project-card');
    projectCards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1 + 0.3}s`;
        
        // Enhanced hover effects
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-12px) scale(1.02)';
            this.style.boxShadow = '0 20px 60px rgba(88, 166, 255, 0.3)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
}

// Mobile menu functionality
function initializeMobileMenu() {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    const mobileMenuLinks = document.querySelectorAll('.mobile-menu-link');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });

        mobileMenuLinks.forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.add('hidden');
            });
        });

        document.addEventListener('click', function(e) {
            if (!mobileMenuBtn.contains(e.target) && !mobileMenu.contains(e.target)) {
                mobileMenu.classList.add('hidden');
            }
        });
    }
}

// Enhanced navigation
function initializeNavigation() {
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

    const navbar = document.querySelector('nav');
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', function() {
        const currentScrollY = window.scrollY;
        
        if (currentScrollY > 100) {
            navbar.style.background = 'rgba(0, 0, 0, 0.9)';
            navbar.style.backdropFilter = 'blur(20px)';
        } else {
            navbar.style.background = 'rgba(0, 0, 0, 0.2)';
        }
        
        if (currentScrollY > lastScrollY && currentScrollY > 100) {
            navbar.style.transform = 'translateY(-100%)';
        } else {
            navbar.style.transform = 'translateY(0)';
        }
        
        lastScrollY = currentScrollY;
    });
}

// Enhanced typing animation
function initializeTypingAnimation() {
    const typingText = document.querySelector('.typing-text');
    if (typingText) {
        const words = ['Developer', 'Engineer', 'Programmer', 'Innovator', 'Creator'];
        let wordIndex = 0;
        let charIndex = 0;
        let isDeleting = false;

        function typeWriter() {
            const currentWord = words[wordIndex];
            
            if (isDeleting) {
                typingText.textContent = currentWord.substring(0, charIndex - 1);
                charIndex--;
            } else {
                typingText.textContent = currentWord.substring(0, charIndex + 1);
                charIndex++;
            }

            // Glitch effect
            if (Math.random() > 0.95) {
                typingText.style.textShadow = '2px 0 #ff0000, -2px 0 #00ff00';
                setTimeout(() => typingText.style.textShadow = '', 50);
            }

            let typeSpeed = isDeleting ? 100 : 200;

            if (!isDeleting && charIndex === currentWord.length) {
                typeSpeed = 2000;
                isDeleting = true;
            } else if (isDeleting && charIndex === 0) {
                isDeleting = false;
                wordIndex = (wordIndex + 1) % words.length;
                typeSpeed = 500;
            }

            setTimeout(typeWriter, typeSpeed);
        }

        typeWriter();
    }
}

// Scroll effects and particle system
function initializeScrollEffects() {
    window.addEventListener('scroll', function() {
        updateScrollProgress();
        
        // Parallax effect
        const scrolled = window.pageYOffset;
        const background = document.querySelector('.code-matrix');
        if (background) {
            background.style.transform = `translateY(${scrolled * 0.3}px)`;
        }
    });
}

// Enhanced particle system
function initializeParticleSystem() {
    document.addEventListener('mousemove', function(e) {
        if (Math.random() > 0.97) {
            createAdvancedParticle(e.clientX, e.clientY);
        }
    });
    
    // Auto particles
    setInterval(() => {
        const x = Math.random() * window.innerWidth;
        const y = Math.random() * window.innerHeight;
        createAdvancedParticle(x, y, true);
    }, 3000);
}

function createAdvancedParticle(x, y, isAuto = false) {
    const particle = document.createElement('div');
    const colors = ['#58a6ff', '#7c3aed', '#10b981', '#f59e0b'];
    const color = colors[Math.floor(Math.random() * colors.length)];
    
    particle.style.position = 'fixed';
    particle.style.left = x + 'px';
    particle.style.top = y + 'px';
    particle.style.width = (isAuto ? 2 : 4) + 'px';
    particle.style.height = (isAuto ? 2 : 4) + 'px';
    particle.style.backgroundColor = color;
    particle.style.borderRadius = '50%';
    particle.style.pointerEvents = 'none';
    particle.style.zIndex = '1000';
    particle.style.boxShadow = `0 0 8px ${color}`;
    
    document.body.appendChild(particle);
    
    // Animate
    let opacity = 1;
    const dx = (Math.random() - 0.5) * 100;
    const dy = (Math.random() - 0.5) * 100;
    
    const animate = () => {
        opacity -= 0.02;
        particle.style.opacity = opacity;
        particle.style.transform = `translate(${dx}px, ${dy}px) scale(${opacity})`;
        
        if (opacity > 0) {
            requestAnimationFrame(animate);
        } else {
            particle.remove();
        }
    };
    
    requestAnimationFrame(animate);
}

// Dynamic background
function createDynamicBackground() {
    const container = document.querySelector('.floating-elements');
    if (!container) return;
    
    const symbols = ['0', '1', '{', '}', '<', '>', '/', '(', ')', '[', ']', ';'];
    
    setInterval(() => {
        const element = document.createElement('div');
        element.textContent = symbols[Math.floor(Math.random() * symbols.length)];
        element.style.position = 'absolute';
        element.style.left = Math.random() * 100 + '%';
        element.style.top = '-20px';
        element.style.color = `rgba(88, 166, 255, ${Math.random() * 0.3 + 0.1})`;
        element.style.fontSize = Math.random() * 8 + 12 + 'px';
        element.style.fontFamily = 'Courier New, monospace';
        element.style.animation = `fall ${Math.random() * 8 + 8}s linear forwards`;
        element.style.pointerEvents = 'none';
        
        container.appendChild(element);
        
        setTimeout(() => {
            if (element.parentNode) {
                element.parentNode.removeChild(element);
            }
        }, 16000);
    }, 800);
}

// Intersection Observer
function observeElements() {
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
                
                if (entry.target.classList.contains('project-card')) {
                    const delay = Array.from(entry.target.parentNode.children).indexOf(entry.target) * 100;
                    setTimeout(() => {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }, delay);
                }
            }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

    document.querySelectorAll('.project-card, .terminal-window, section').forEach(el => {
        observer.observe(el);
    });
}

// Scroll progress
function addScrollProgress() {
    const progressBar = document.createElement('div');
    progressBar.className = 'scroll-progress';
    progressBar.style.position = 'fixed';
    progressBar.style.top = '0';
    progressBar.style.left = '0';
    progressBar.style.width = '0%';
    progressBar.style.height = '3px';
    progressBar.style.background = 'linear-gradient(90deg, #58a6ff, #7c3aed)';
    progressBar.style.zIndex = '9999';
    progressBar.style.transition = 'width 0.1s ease';
    
    document.body.appendChild(progressBar);
}

function updateScrollProgress() {
    const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
    const progressBar = document.querySelector('.scroll-progress');
    if (progressBar) {
        progressBar.style.width = scrollPercent + '%';
    }
}

// Easter eggs
function initializeEasterEggs() {
    let konamiCode = [];
    const konamiSequence = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65];

    document.addEventListener('keydown', function(e) {
        konamiCode.push(e.keyCode);
        
        if (konamiCode.length > konamiSequence.length) {
            konamiCode.shift();
        }
        
        if (konamiCode.join(',') === konamiSequence.join(',')) {
            activateMatrix();
            konamiCode = [];
        }
    });
}

function activateMatrix() {
    document.body.style.backgroundColor = '#000';
    document.body.style.color = '#00ff00';
    
    const message = document.createElement('div');
    message.style.position = 'fixed';
    message.style.top = '50%';
    message.style.left = '50%';
    message.style.transform = 'translate(-50%, -50%)';
    message.style.fontSize = '2rem';
    message.style.color = '#00ff00';
    message.style.zIndex = '10000';
    message.style.fontFamily = 'Courier New, monospace';
    message.textContent = 'SYSTEM HACKED! 🚀';
    message.style.animation = 'pulse 1s infinite';
    
    document.body.appendChild(message);
    
    setTimeout(() => {
        document.body.removeChild(message);
        document.body.style.backgroundColor = '';
        document.body.style.color = '';
    }, 3000);

    // Intersection Observer for animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
            }
        });
    }, observerOptions);

    // Observe elements for animation
    document.querySelectorAll('.project-card, .terminal-window, section').forEach(el => {
        observer.observe(el);
    });

    // Add dynamic code rain effect
    createCodeRain();

    // Parallax effect for background
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const background = document.querySelector('.code-matrix');
        if (background) {
            background.style.transform = `translateY(${scrolled * 0.5}px)`;
        }
    });

    // Add glitch effect to hero title
    addGlitchEffect();

    // Terminal cursor blinking
    animateTerminalCursor();
};

// Create dynamic code rain effect
function createCodeRain() {
    const codeRainContainer = document.createElement('div');
    codeRainContainer.className = 'fixed inset-0 pointer-events-none z-0';
    codeRainContainer.style.overflow = 'hidden';
    
    const codeSymbols = ['0', '1', '{', '}', '<', '>', '/', '\\', '(', ')', '[', ']', ';', ':', '=', '+', '-', '*', '&', '|', '!', '?'];
    
    function createRainDrop() {
        const drop = document.createElement('div');
        drop.textContent = codeSymbols[Math.floor(Math.random() * codeSymbols.length)];
        drop.style.position = 'absolute';
        drop.style.left = Math.random() * 100 + '%';
        drop.style.top = '-20px';
        drop.style.color = `rgba(88, 166, 255, ${Math.random() * 0.3 + 0.1})`;
        drop.style.fontSize = Math.random() * 10 + 12 + 'px';
        drop.style.fontFamily = 'Courier New, monospace';
        drop.style.animation = `fall ${Math.random() * 3 + 2}s linear forwards`;
        
        codeRainContainer.appendChild(drop);
        
        // Remove drop after animation
        setTimeout(() => {
            if (drop.parentNode) {
                drop.parentNode.removeChild(drop);
            }
        }, 5000);
    }

    // Add CSS for falling animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fall {
            to {
                transform: translateY(100vh);
                opacity: 0;
            }
        }
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-fade-in {
            animation: fade-in 0.8s ease-out forwards;
        }
    `;
    document.head.appendChild(style);
    
    document.body.appendChild(codeRainContainer);
    
    // Create drops periodically
    setInterval(createRainDrop, 300);
}

// Add glitch effect to hero title
function addGlitchEffect() {
    const title = document.querySelector('h1');
    if (title) {
        let glitchTimeout;
        
        function triggerGlitch() {
            title.style.textShadow = `
                2px 0 #ff0000,
                -2px 0 #00ff00,
                0 2px #0000ff
            `;
            title.style.transform = 'skew(2deg)';
            
            setTimeout(() => {
                title.style.textShadow = 'none';
                title.style.transform = 'skew(0deg)';
            }, 100);
            
            glitchTimeout = setTimeout(triggerGlitch, Math.random() * 5000 + 3000);
        }
        
        // Start glitch effect
        glitchTimeout = setTimeout(triggerGlitch, 2000);
    }
}

// Animate terminal cursor
function animateTerminalCursor() {
    const cursors = document.querySelectorAll('.cursor');
    cursors.forEach(cursor => {
        setInterval(() => {
            cursor.style.opacity = cursor.style.opacity === '0' ? '1' : '0';
        }, 500);
    });
}

// Add particle effect on mouse move
document.addEventListener('mousemove', function(e) {
    if (Math.random() > 0.98) { // Occasional particles
        createParticle(e.clientX, e.clientY);
    }
});

function createParticle(x, y) {
    const particle = document.createElement('div');
    particle.style.position = 'fixed';
    particle.style.left = x + 'px';
    particle.style.top = y + 'px';
    particle.style.width = '4px';
    particle.style.height = '4px';
    particle.style.backgroundColor = '#58a6ff';
    particle.style.borderRadius = '50%';
    particle.style.pointerEvents = 'none';
    particle.style.zIndex = '1000';
    particle.style.animation = 'particle-fade 1s ease-out forwards';
    
    document.body.appendChild(particle);
    
    setTimeout(() => {
        if (particle.parentNode) {
            particle.parentNode.removeChild(particle);
        }
    }, 1000);
}

// Add particle fade animation
const particleStyle = document.createElement('style');
particleStyle.textContent = `
    @keyframes particle-fade {
        0% {
            opacity: 1;
            transform: scale(1);
        }
        100% {
            opacity: 0;
            transform: scale(0) translateY(-20px);
        }
    }
`;
document.head.appendChild(particleStyle);

// Add scroll progress indicator
function addScrollProgress() {
    const progressBar = document.createElement('div');
    progressBar.style.position = 'fixed';
    progressBar.style.top = '0';
    progressBar.style.left = '0';
    progressBar.style.width = '0%';
    progressBar.style.height = '3px';
    progressBar.style.backgroundColor = '#58a6ff';
    progressBar.style.zIndex = '9999';
    progressBar.style.transition = 'width 0.1s ease';
    
    document.body.appendChild(progressBar);
    
    window.addEventListener('scroll', () => {
        const scrollPercent = (window.scrollY / (document.documentElement.scrollHeight - window.innerHeight)) * 100;
        progressBar.style.width = scrollPercent + '%';
    });
}

// Initialize scroll progress
addScrollProgress();

// Add easter egg - Konami code
let konamiCode = [];
const konamiSequence = [38, 38, 40, 40, 37, 39, 37, 39, 66, 65]; // Up, Up, Down, Down, Left, Right, Left, Right, B, A

document.addEventListener('keydown', function(e) {
    konamiCode.push(e.keyCode);
    
    if (konamiCode.length > konamiSequence.length) {
        konamiCode.shift();
    }
    
    if (konamiCode.join(',') === konamiSequence.join(',')) {
        activateEasterEgg();
        konamiCode = [];
    }
});

function activateEasterEgg() {
    // Matrix effect
    document.body.style.backgroundColor = '#000';
    document.body.style.color = '#00ff00';
    
    const message = document.createElement('div');
    message.style.position = 'fixed';
    message.style.top = '50%';
    message.style.left = '50%';
    message.style.transform = 'translate(-50%, -50%)';
    message.style.fontSize = '2rem';
    message.style.color = '#00ff00';
    message.style.zIndex = '10000';
    message.style.fontFamily = 'Courier New, monospace';
    message.textContent = 'SYSTEM HACKED! 🚀';
    message.style.animation = 'pulse 1s infinite';
    
    document.body.appendChild(message);
    
    setTimeout(() => {
        document.body.removeChild(message);
        document.body.style.backgroundColor = '';
        document.body.style.color = '';
    }, 3000);
}