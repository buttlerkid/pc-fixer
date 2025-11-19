// Mobile Menu Toggle
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const navList = document.querySelector('.nav-list');

if (mobileMenuBtn) {
    mobileMenuBtn.addEventListener('click', () => {
        navList.classList.toggle('active');

        // Change icon
        const icon = mobileMenuBtn.querySelector('i');
        if (navList.classList.contains('active')) {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-xmark');
        } else {
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars');
        }
    });
}

// Close mobile menu when clicking a link
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        if (navList.classList.contains('active')) {
            navList.classList.remove('active');
            const icon = mobileMenuBtn.querySelector('i');
            icon.classList.remove('fa-xmark');
            icon.classList.add('fa-bars');
        }
    });
});

// Dark Mode Toggle
const themeToggle = document.querySelector('.theme-toggle');
const body = document.body;

// Check for saved theme preference or default to light mode
const currentTheme = localStorage.getItem('theme') || 'light';
if (currentTheme === 'dark') {
    body.classList.add('dark-mode');
}

if (themeToggle) {
    themeToggle.addEventListener('click', () => {
        body.classList.toggle('dark-mode');

        // Save theme preference
        const theme = body.classList.contains('dark-mode') ? 'dark' : 'light';
        localStorage.setItem('theme', theme);
    });
}

// FAQ Accordion
const faqQuestions = document.querySelectorAll('.faq-question');

faqQuestions.forEach(question => {
    question.addEventListener('click', () => {
        // Close other open FAQs
        const currentlyActive = document.querySelector('.faq-question.active');
        if (currentlyActive && currentlyActive !== question) {
            currentlyActive.classList.remove('active');
        }

        // Toggle current FAQ
        question.classList.toggle('active');
        const answer = question.nextElementSibling;

        if (question.classList.contains('active')) {
            answer.style.maxHeight = answer.scrollHeight + "px";
        } else {
            answer.style.maxHeight = null;
        }
    });
});

// Scroll Animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
        }
    });
}, observerOptions);

// Add fade-in class to elements and observe them
const animateElements = document.querySelectorAll('.service-card, .testimonial-card, .pricing-card, .step, .faq-item');
animateElements.forEach(el => {
    el.classList.add('fade-in');
    observer.observe(el);
});

// File Upload Interaction
const fileInput = document.getElementById('file-upload');
const fileText = document.querySelector('.file-upload-text');

if (fileInput) {
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            const count = e.target.files.length;
            fileText.textContent = `${count} file${count > 1 ? 's' : ''} selected`;
            fileText.style.color = 'var(--primary-color)';
            fileText.style.fontWeight = '600';
        } else {
            fileText.textContent = 'Choose files or drag here';
            fileText.style.color = 'var(--light-text)';
            fileText.style.fontWeight = 'normal';
        }
    });
}
