// ========================================
// DOM Elements
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    
    if (hamburger) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            hamburger.classList.toggle('active');
        });
    }
    
    // Close mobile menu when clicking on a link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
        });
    });
    
    // ========================================
    // Billing Toggle (Monthly/Yearly) - يدعم عدة أماكن ويربط بالحقول أدناه
    // ========================================
    const billingToggleContainers = document.querySelectorAll('.billing-toggle');
    const monthlyPrices = document.querySelectorAll('.monthly-price');
    const yearlyPrices = document.querySelectorAll('.yearly-price');
    const monthlyPeriods = document.querySelectorAll('.monthly-period');
    const yearlyPeriods = document.querySelectorAll('.yearly-period');
    let isYearly = false; // global pricing mode for page

    function setPricingMode(yearly) {
        isYearly = !!yearly;

        if (isYearly) {
            monthlyPrices.forEach(price => price.style.display = 'none');
            yearlyPrices.forEach(price => price.style.display = 'inline');
            monthlyPeriods.forEach(period => period.style.display = 'none');
            yearlyPeriods.forEach(period => period.style.display = 'inline');
        } else {
            monthlyPrices.forEach(price => price.style.display = 'inline');
            yearlyPrices.forEach(price => price.style.display = 'none');
            monthlyPeriods.forEach(period => period.style.display = 'inline');
            yearlyPeriods.forEach(period => period.style.display = 'none');
        }

        // update visuals for every billing-toggle on the page
        billingToggleContainers.forEach(container => {
            const switchEl = container.querySelector('.toggle-switch');
            const monthlyLabel = container.querySelector('.toggle-label.monthly');
            const yearlyLabel = container.querySelector('.toggle-label.yearly');
            if (!switchEl) return;
            if (isYearly) {
                switchEl.classList.add('yearly');
                if (monthlyLabel) monthlyLabel.classList.remove('active');
                if (yearlyLabel) yearlyLabel.classList.add('active');
            } else {
                switchEl.classList.remove('yearly');
                if (monthlyLabel) monthlyLabel.classList.add('active');
                if (yearlyLabel) yearlyLabel.classList.remove('active');
            }
        });
    }

    // attach handlers to each billing-toggle container
    billingToggleContainers.forEach(container => {
        const switchEl = container.querySelector('.toggle-switch');
        const monthlyLabel = container.querySelector('.toggle-label.monthly');
        const yearlyLabel = container.querySelector('.toggle-label.yearly');

        // initialize state based on label classes if present
        const initialYearly = yearlyLabel && yearlyLabel.classList.contains('active');
        setPricingMode(initialYearly);

        if (switchEl) {
            switchEl.addEventListener('click', () => setPricingMode(!isYearly));
        }
        if (monthlyLabel) monthlyLabel.addEventListener('click', () => setPricingMode(false));
        if (yearlyLabel) yearlyLabel.addEventListener('click', () => setPricingMode(true));
    });
    
    // ========================================
    // FAQ Accordion
    // ========================================
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        question.addEventListener('click', () => {
            // Close other items
            faqItems.forEach(otherItem => {
                if (otherItem !== item && otherItem.classList.contains('active')) {
                    otherItem.classList.remove('active');
                }
            });
            // Toggle current item
            item.classList.toggle('active');
        });
    });
    
    // ========================================
    // Animated Counters (للصفحة الرئيسية)
    // ========================================
    const counters = document.querySelectorAll('.stat-value');
    let animated = false;
    
    function animateCounters() {
        if (animated) return;
        
        counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            if (isNaN(target)) return;
            
            let current = 0;
            const increment = target / 50;
            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    if (target >= 1000) {
                        counter.innerText = Math.floor(current).toLocaleString();
                    } else {
                        counter.innerText = Math.floor(current);
                    }
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.innerText = target.toLocaleString();
                }
            };
            updateCounter();
        });
        animated = true;
    }
    
    // Trigger counters when stats section is visible
    const statsSection = document.querySelector('.stats-bar');
    if (statsSection) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounters();
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.3 });
        observer.observe(statsSection);
    }
    
 
   
    // ========================================
    // Smooth Scroll for Anchor Links
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = this.getAttribute('href');
            if (target !== '#' && target !== '') {
                e.preventDefault();
                const element = document.querySelector(target);
                if (element) {
                    element.scrollIntoView({ behavior: 'smooth' });
                }
            }
        });
    });
    
    // ========================================
    // Navbar Scroll Effect
    // ========================================
    let lastScroll = 0;
    window.addEventListener('scroll', function() {
        const navbar = document.querySelector('.navbar');
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 100) {
            navbar.style.background = 'rgba(254, 247, 255, 0.98)';
            navbar.style.boxShadow = '0px 4px 10px rgba(0, 0, 0, 0.1)';
        } else {
            navbar.style.background = 'rgba(254, 247, 255, 0.95)';
            navbar.style.boxShadow = '0px 1px 2px rgba(0, 0, 0, 0.05)';
        }
        
        lastScroll = currentScroll;
    });
    
    // ========================================
    // Intersection Observer for Fade-in Animations
    // ========================================
    const fadeElements = document.querySelectorAll('.pricing-card, .feature-box, .faq-item');
    
    const fadeObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                fadeObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    
    fadeElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        fadeObserver.observe(el);
    });
    
    console.log('MatjarCarts Pricing Page loaded successfully!');
});