# CodexeHub Services Page Development Prompt

## Project Overview

Create a **modern, premium, enterprise-grade Services page** for **CodexeHub**, a software development company.

The page must follow the same design language, branding, spacing, typography, components, and color system used throughout the CodexeHub website.

The final result should be **production-ready**, **highly reusable**, **responsive**, **SEO-friendly**, and easy to convert into a complete CMS/Admin system later.

---

# Technology Stack

Build using:

* Latest Laravel
* Blade Template Structure
* Bootstrap 5.x
* HTML5
* SCSS
* Vanilla JavaScript (ES6)
* MySQL (future-ready structure only)
* Bootstrap Icons
* Swiper.js
* AOS Animation
* GLightbox (optional)

---

# Important Requirements

* Use Laravel project structure.
* **Create Blade files** with reusable layouts and partials.
* Keep business logic out of views.
* Use clean Bootstrap components.
* Mobile-first responsive design.
* No inline CSS.
* No inline JavaScript.
* Modular SCSS architecture.
* SEO-friendly HTML.
* Semantic HTML5.
* Accessibility (WCAG basics).
* Production-ready code.
* Clean indentation.
* Reusable components.
* Optimized asset loading.
* Future CMS/Admin integration ready.

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
    │   └── services.blade.php
    │
    ├── partials/
    │   ├── hero.blade.php
    │   ├── service-card.blade.php
    │   ├── process.blade.php
    │   ├── pricing.blade.php
    │   ├── testimonials.blade.php
    │   ├── faq.blade.php
    │   ├── technologies.blade.php
    │   ├── industries.blade.php
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
    _hero.scss
    _sections.scss

components/
    _buttons.scss
    _cards.scss
    _icons.scss
    _forms.scss
    _accordion.scss
    _pricing.scss
    _slider.scss

pages/
    _services.scss

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

Text

```text
#334155
```

Border

```text
#E5E7EB
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

Shadow

Soft modern shadows only.

---

# Services Page Layout

```text
Top Announcement Bar

↓

Sticky Header

↓

Hero Banner

↓

Quick Service Highlights

↓

Core Services

↓

Detailed Service Sections

↓

Why Choose CodexeHub

↓

Technology Stack

↓

Development Process

↓

Industries We Serve

↓

Project Workflow

↓

Pricing / Engagement Models

↓

Frequently Asked Questions

↓

Testimonials

↓

Call To Action

↓

Footer
```

---

# 1. Top Announcement Bar

Display

* Company tagline
* Phone
* Email
* LinkedIn
* Upwork
* Fiverr

---

# 2. Sticky Header

Include

Logo

Navigation

* Home
* Services (Active)
* Projects
* Portfolio
* About Us
* Contact

CTA Button

```text
Get Free Consultation
```

Features

* Sticky navigation
* Mobile menu
* Dropdown ready
* Mega menu ready
* Active page indicator

---

# 3. Hero Banner

Premium full-width hero.

Left Column

* Small badge
* H1 heading
* Description
* Primary CTA
* Secondary CTA
* Trust indicators

Right Column

Professional illustration showing

* Laptop
* Dashboard
* Mobile App
* Analytics
* AI
* Cloud
* Floating UI cards

Include animated decorative background.

---

# 4. Quick Service Highlights

Horizontal feature cards.

Include

* Expert Team
* Scalable Solutions
* Secure Development
* Agile Delivery
* Transparent Process
* 24/7 Support

Hover animations.

---

# 5. Core Services

Display 8–12 premium service cards.

Examples

* Custom Web Development
* Enterprise Software
* Mobile App Development
* UI/UX Design
* E-Commerce Development
* ERP & CRM Solutions
* AI & Automation
* API Development
* Cloud & DevOps
* Cybersecurity
* SEO & Digital Marketing
* Maintenance & Support

Each card includes

* SVG icon
* Title
* Description
* Key technologies
* Learn More button
* Hover animation

---

# 6. Detailed Service Sections

Each service has its own content block.

Include

* Service overview
* Benefits
* Key features
* Technologies used
* Ideal clients
* Deliverables
* Related services
* CTA button

Alternate image/text layout.

---

# 7. Why Choose CodexeHub

Two-column section.

Include

* Industry experience
* Modern technologies
* Agile methodology
* Transparent communication
* Dedicated project manager
* Long-term support
* Secure coding practices
* Performance optimization

---

# 8. Technology Stack

Display modern technology cards.

Backend

* Laravel
* PHP
* Node.js

Frontend

* Bootstrap
* React
* Vue
* Next.js

Mobile

* Flutter

Database

* MySQL
* PostgreSQL
* Redis

Cloud

* AWS
* Docker
* Linux

AI

* OpenAI
* LangChain
* MCP

---

# 9. Development Process

Interactive timeline.

1. Discovery
2. Planning
3. UI/UX Design
4. Development
5. Testing
6. Deployment
7. Maintenance

Include icons and connecting lines.

---

# 10. Industries We Serve

Cards

* Government
* Healthcare
* Education
* Finance
* Retail
* Manufacturing
* Logistics
* Real Estate
* NGO
* Hospitality
* Automotive
* Travel

---

# 11. Project Workflow

Visual workflow diagram.

Requirement

↓

Research

↓

Planning

↓

Design

↓

Development

↓

Testing

↓

Deployment

↓

Support

---

# 12. Engagement Models

Professional pricing/engagement cards.

* Fixed Price Project
* Dedicated Team
* Staff Augmentation
* Monthly Maintenance

Each card includes

* Best for
* Benefits
* Timeline
* CTA

---

# 13. Client Testimonials

Modern carousel.

Include

* Client photo
* Company
* Rating
* Project type
* Review

---

# 14. FAQ

Bootstrap accordion.

Questions such as

* How long does development take?
* What technologies do you use?
* Do you sign NDAs?
* Do you provide maintenance?
* Can you redesign existing software?
* What is your development process?

---

# 15. Call To Action

Large premium CTA.

Heading

```text
Let's Build Your Next Digital Product
```

Buttons

* Start Your Project
* Schedule a Free Consultation

Background

Dark gradient with abstract shapes.

---

# 16. Footer

Include

Company Information

Quick Links

Services

Resources

Portfolio

Newsletter

Contact Information

Social Media

Copyright

Privacy Policy

Terms & Conditions

Cookie Policy

---

# Animations

Implement

* AOS Scroll Animation
* Card Hover Lift
* Fade Up
* Zoom In
* Counter Animation
* Button Ripple
* Smooth Scroll
* Sticky Header Animation
* Logo Marquee
* Image Hover Zoom

Animations should remain subtle and performant.

---

# SEO Optimization

Include

* Dynamic page title
* Meta description
* Canonical URL
* Open Graph tags
* Twitter Cards
* JSON-LD Organization schema
* Breadcrumb schema
* Proper H1–H6 hierarchy
* Alt text placeholders
* Clean URL structure

---

# Performance Optimization

* Lazy-load images
* Responsive image containers
* Font preloading
* Minification-ready assets
* Deferred JavaScript
* Optimized DOM structure
* Minimal dependencies
* CLS-safe layouts
* Lighthouse optimization
* Cross-browser compatibility

---

# Reusable Components

Create reusable Blade components for

* Buttons
* Cards
* Service Card
* Hero Banner
* Feature Card
* Statistics Card
* Technology Badge
* Timeline
* FAQ Accordion
* Testimonial Card
* CTA Banner
* Breadcrumb
* Section Header
* Pricing Card

---

# Deliverables

Generate a complete **Services Page** that includes:

* Premium SaaS/enterprise UI
* Bootstrap 5 responsive layout
* Modular Blade templates
* Reusable components
* SCSS architecture
* Clean JavaScript modules
* Placeholder images and content
* SEO-ready markup
* Performance optimization
* Accessibility support
* Future-ready structure for MySQL-powered dynamic content and Admin CMS integration.

The final output should match the quality of a professional software agency website and remain consistent with the CodexeHub homepage, portfolio, about, and contact pages.
