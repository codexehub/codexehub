# CodexeHub Contact Us Page Development Prompt

## Project Overview

Create a **premium, enterprise-grade Contact Us page** for **CodexeHub**, a modern software development and digital transformation company.

The Contact page must follow the exact design language, spacing, typography, components, color palette, and UI system used throughout the CodexeHub website (Homepage, Services, Portfolio, Portfolio Details, and About Us).

The page should make it easy for potential clients to contact the company through multiple communication channels while maintaining a professional SaaS appearance.

The final result must be **production-ready**, **fully responsive**, **SEO optimized**, **performance optimized**, **accessible**, **reusable**, and **future-ready for Admin CMS integration**.

---

# Technology Stack

Develop using

* Latest Laravel
* Blade Template
* Bootstrap 5.x
* HTML5
* SCSS
* Vanilla JavaScript (ES6)
* MySQL (future dynamic content)
* Bootstrap Icons
* AOS Animation
* Google Maps API (placeholder integration)
* Google reCAPTCHA (placeholder)
* Swiper.js (optional)

---

# Development Requirements

* Latest Laravel project structure
* Blade layouts and partials
* Reusable Blade components
* Bootstrap utility classes
* Semantic HTML5
* Mobile-first responsive design
* SCSS modular architecture
* No inline CSS
* No inline JavaScript
* WCAG accessibility support
* SEO-ready markup
* Optimized assets
* Clean and maintainable code
* Admin CMS ready
* Future API integration ready

---

# Folder Structure

```text
resources/
└── views/
    ├── layouts/
    │   ├── app.blade.php
    │   ├── head.blade.php
    │   ├── header.blade.php
    │   ├── footer.blade.php
    │   └── scripts.blade.php
    │
    ├── pages/
    │   └── contact.blade.php
    │
    ├── partials/
    │   ├── hero.blade.php
    │   ├── contact-info.blade.php
    │   ├── contact-form.blade.php
    │   ├── map.blade.php
    │   ├── quick-contact.blade.php
    │   ├── why-us.blade.php
    │   ├── faq.blade.php
    │   ├── office-hours.blade.php
    │   ├── social.blade.php
    │   └── cta.blade.php
    │
    └── components/
```

---

# SCSS Architecture

```text
scss/

abstracts/
    _variables.scss
    _mixins.scss
    _functions.scss

base/
    _reset.scss
    _typography.scss
    _utilities.scss

layout/
    _header.scss
    _footer.scss
    _sections.scss
    _hero.scss

components/
    _buttons.scss
    _cards.scss
    _forms.scss
    _contact.scss
    _accordion.scss
    _icons.scss
    _map.scss

pages/
    _contact.scss

vendors/

app.scss
```

---

# Brand Identity

Primary

```text
#165E53
```

Accent

```text
#9FC149
```

White

```text
#FFFFFF
```

Dark

```text
#0F172A
```

Background

```text
#F8FAFC
```

Border

```text
#E5E7EB
```

Text

```text
#334155
```

---

# Typography

Heading

* Manrope

Body

* Inter

Container Width

```text
1320px
```

Border Radius

```text
16px
```

---

# Page Structure

```text
Top Announcement Bar

↓

Sticky Header

↓

Hero Banner

↓

Quick Contact Cards

↓

Contact Form + Office Map

↓

Why Contact CodexeHub

↓

Quick Communication Channels

↓

Office Information

↓

Business Hours

↓

Frequently Asked Questions

↓

Call To Action

↓

Footer
```

---

# 1. Top Announcement Bar

Include

* Company tagline
* Phone number
* Email address
* LinkedIn
* Upwork
* Fiverr

---

# 2. Sticky Header

Navigation

* Home
* Services
* Projects
* Portfolio
* About Us
* Contact Us (Active)

CTA Button

```text
Get Free Consultation
```

Features

* Sticky navigation
* Mobile hamburger menu
* Dropdown ready
* Mega menu ready

---

# 3. Hero Banner

Premium full-width hero section.

Left Side

* Breadcrumb
* Small badge
* H1 heading
* Supporting description
* Primary CTA
* Secondary CTA

Highlight features

* Quick Response
* Expert Consultation
* Confidential Projects

Right Side

Professional illustration containing

* Laptop
* Contact dashboard
* Location pin
* Email
* Live chat
* Mobile device

Background

* Dark gradient
* Abstract digital graphics
* Glassmorphism
* Floating UI elements

---

# 4. Quick Contact Cards

Display responsive cards for

* Call Us
* Email Us
* Visit Office
* Business Hours

Each card includes

* Icon
* Title
* Information
* Small description

Hover animation

---

# 5. Contact Form

Modern enterprise form.

Fields

* Full Name
* Company Name
* Email Address
* Phone Number
* Country
* Service Required
* Budget Range
* Project Timeline
* Subject
* Message
* File Upload (proposal, requirements, documents)
* Privacy Policy checkbox
* Google reCAPTCHA placeholder

Buttons

```text
Send Message

Schedule Consultation
```

Include

* Client-side validation
* Success/error placeholders
* Character counter
* Loading state
* Accessible labels

---

# 6. Google Map

Responsive embedded map placeholder.

Display

* Office location
* Marker
* Address card
* Get Directions button
* Open in Google Maps button

Prepare for future Google Maps API integration.

---

# 7. Why Contact CodexeHub

Feature cards

* Experienced Team
* Transparent Communication
* Custom Software Solutions
* On-Time Delivery
* Affordable Pricing
* Long-Term Partnership
* Secure Development
* Dedicated Support

---

# 8. Quick Communication

Professional contact cards.

Include

* WhatsApp
* Telegram
* Skype
* LinkedIn
* Upwork
* Fiverr
* Email
* Phone

Each card

* Platform icon
* Contact information
* Quick action button

---

# 9. Office Information

Include

* Company Name
* Office Address
* Postal Code
* Country
* Google Maps link
* Phone Numbers
* Email Addresses

Support multiple office locations.

---

# 10. Business Hours

Display

* Sunday–Thursday
* Friday
* Saturday (optional)
* Emergency Support
* Time Zone

Use modern schedule cards.

---

# 11. FAQ

Bootstrap accordion.

Questions

* How quickly do you respond?
* Do you provide free consultation?
* What information is needed before starting a project?
* Can you work with international clients?
* Do you sign NDA agreements?
* What payment methods do you accept?
* Do you provide maintenance and support?
* How long does development usually take?

---

# 12. Call To Action

Premium CTA section.

Heading

```text
Let's Build Your Next Digital Solution Together
```

Buttons

* Start Your Project
* Book a Free Consultation

Background

Dark branded gradient with abstract shapes.

---

# 13. Footer

Include

Company

Quick Links

Services

Portfolio

Resources

Newsletter

Contact Information

Social Media

Privacy Policy

Terms & Conditions

Cookie Policy

Copyright

---

# Form Components

Create reusable components for

* Text Input
* Email Input
* Phone Input
* Select Dropdown
* Textarea
* File Upload
* Checkbox
* Radio Button
* Validation Message
* Submit Button
* Success Alert
* Error Alert

---

# Reusable Blade Components

Create reusable components for

* Hero Banner
* Breadcrumb
* Contact Card
* Contact Form
* Office Card
* Business Hours Card
* Social Contact Card
* FAQ Accordion
* CTA Banner
* Google Map Section
* Section Header
* Button Components

---

# Future Admin CMS Integration

Design every section to be dynamically managed through an admin panel.

Prepare database-ready structures for

* Contact information
* Office locations
* Business hours
* Contact form settings
* Google Maps coordinates
* Social media links
* FAQ
* CTA content
* Hero section
* SEO metadata

---

# SEO Optimization

Include

* Dynamic page title
* Meta description
* Canonical URL
* Open Graph tags
* Twitter Card tags
* Breadcrumb schema
* Organization schema
* ContactPage schema (JSON-LD)
* Proper heading hierarchy
* Alt text placeholders

---

# Performance Optimization

* Lazy-load map
* Responsive images
* Deferred JavaScript
* Font preloading
* Optimized DOM
* Minification-ready assets
* CLS-safe layout
* Lighthouse optimization
* Cross-browser compatibility

---

# Security Preparation

Prepare the layout for future implementation of

* CSRF protection
* Spam prevention
* Honeypot field
* Google reCAPTCHA
* Rate limiting
* Secure file uploads
* Input validation
* Email notification system

---

# Mobile Responsiveness

Optimize for

* Mobile
* Tablet
* Laptop
* Desktop
* Large monitors

Ensure

* Touch-friendly buttons
* Responsive contact cards
* Responsive map
* Responsive form
* Mobile navigation
* Optimized spacing
* Accessible tap targets

---

# Deliverables

Generate a complete **Contact Us Page** including

* Enterprise-quality Bootstrap 5 design
* Responsive Blade templates
* Modular SCSS architecture
* Reusable Blade components
* Professional contact form
* Google Maps placeholder
* Office information
* Business hours
* Social and freelance profile links
* FAQ section
* Premium CTA
* SEO-ready HTML
* Accessibility support
* Performance optimization
* Future-ready MySQL and Admin CMS integration

The Contact Us page must visually and structurally match the Homepage, Services, Portfolio, Portfolio Details, and About Us pages, providing a seamless, premium user experience across the entire CodexeHub website.
