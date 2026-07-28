# CodexeHub "Get Free Consultation" Modal Development Prompt

## Objective

Create a **premium, enterprise-grade multi-step consultation modal** for the **"Get Free Consultation"** button.

The modal should be designed to convert website visitors into qualified leads while maintaining the same premium UI/UX as the CodexeHub website.

The modal must be reusable, mobile-friendly, accessible, SEO-conscious, and ready for future integration with the Laravel backend and Admin CRM.

---

# Technology Stack

Develop using

* Latest Laravel
* Blade Components
* Bootstrap 5.x Modal
* HTML5
* SCSS
* Vanilla JavaScript (ES6)
* Bootstrap Icons
* MySQL (future integration)

---

# Requirements

* Bootstrap Modal
* Mobile Responsive
* Multi-step Form Wizard
* Modern SaaS Design
* Reusable Blade Component
* Clean HTML Structure
* Semantic HTML5
* No Inline CSS
* No Inline JavaScript
* WCAG Accessibility
* Future API Ready
* Admin Panel Ready

---

# Modal Design

Width

Desktop

```text
850px
```

Tablet

```text
95%
```

Mobile

```text
100%
```

Border Radius

```text
20px
```

Background

White

Soft Shadow

Glass Effect Header

---

# Modal Header

Left

Company Logo

Right

Close Button

Title

```text
Get Your Free Consultation
```

Subtitle

```text
Let's discuss your project and recommend the best solution for your business.
```

Progress Indicator

```text
Step 1 of 3
```

Animated Progress Bar

---

# Step 1

## Personal Information

Fields

Full Name *

Company Name

Email Address *

Phone / WhatsApp *

Country *

City

Preferred Language

Dropdown

```text
English

Bangla

Other
```

---

# Step 2

## Project Information

Service Required *

Dropdown

```text
Custom Software Development

Website Development

E-Commerce Development

ERP Solution

CRM Solution

Mobile App Development

UI/UX Design

AI Automation

API Development

Cloud & DevOps

SEO & Digital Marketing

Maintenance & Support

Other
```

Project Type

Dropdown

```text
Startup

Small Business

Enterprise

Government

NGO

Personal
```

Project Description *

Textarea

Budget Range

Dropdown

```text
Under $1,000

$1,000–$5,000

$5,000–$10,000

$10,000–$25,000

Above $25,000

Let's Discuss
```

Expected Timeline

Dropdown

```text
Immediately

Within 1 Month

1–3 Months

3–6 Months

Flexible
```

---

# Step 3

## Meeting Preferences

Preferred Contact Method

* Email
* Phone Call
* WhatsApp
* Google Meet
* Zoom

Preferred Meeting Date

Date Picker

Preferred Meeting Time

Time Picker

Time Zone

Dropdown

File Upload

Support

* PDF
* DOCX
* XLSX
* PPTX
* PNG
* JPG
* ZIP

Maximum Size

```text
25 MB
```

Agreement

Checkbox

```text
I agree to the Privacy Policy and Terms of Service.
```

---

# Footer Buttons

Step 1

```text
Next →
```

Step 2

```text
← Previous

Next →
```

Step 3

```text
← Previous

Submit Consultation Request
```

Loading State

Show spinner

Disable buttons

---

# Success Screen

Show after successful submission.

Icon

Success Illustration

Heading

```text
Thank You!
```

Message

```text
Your consultation request has been submitted successfully.

Our team will contact you within one business day.
```

Reference Number

```text
CH-000001
```

Buttons

```text
Return Home

Close
```

---

# Validation

Validate

* Required Fields
* Email Format
* Phone Number
* File Size
* File Type
* Character Limits

Display inline validation messages.

---

# Admin Database Structure

Prepare for

```text
consultations

id

reference_no

full_name

company_name

email

phone

country

city

language

service

project_type

budget

timeline

description

contact_method

meeting_date

meeting_time

timezone

attachment

status

priority

assigned_to

notes

source

ip_address

browser

created_at

updated_at
```

---

# Consultation Status

* New
* Contacted
* Meeting Scheduled
* Proposal Sent
* Negotiation
* Won
* Lost
* Archived

---

# Email Notifications

Automatically prepare for

Customer Confirmation Email

Admin Notification Email

Sales Team Notification

---

# Future Integrations

Design so it can later integrate with

* Admin Dashboard
* CRM
* Google Calendar
* Outlook Calendar
* Google Meet
* Zoom
* WhatsApp Business API
* Email Queue
* SMS Gateway
* Slack Notifications

---

# UI Components

Create reusable Blade components for

* Modal
* Step Indicator
* Progress Bar
* Input
* Select
* Textarea
* Date Picker
* Time Picker
* File Upload
* Checkbox
* Validation Message
* Success Screen
* Loading Spinner
* Primary Button

---

# Mobile Responsiveness

Optimize for

* Mobile
* Tablet
* Desktop

Features

* Touch-friendly controls
* Responsive spacing
* Sticky footer buttons
* Keyboard-friendly navigation

---

# Accessibility

Include

* Keyboard navigation
* Focus trapping inside modal
* ARIA labels
* Screen-reader support
* Visible focus states
* High color contrast

---

# Performance

* Lazy-load modal assets
* Optimized JavaScript
* Modular SCSS
* Deferred initialization
* Minification-ready code
* Clean DOM structure

---

# Deliverables

Generate a complete **Free Consultation Modal** with

* Bootstrap 5 modal layout
* Multi-step wizard
* Responsive design
* Reusable Blade components
* SCSS architecture
* Clean JavaScript
* Validation states
* Success screen
* Future-ready MySQL integration
* Admin CRM compatibility
* Production-ready code following Laravel best practices.

The modal should match the premium CodexeHub design system and provide a smooth, professional experience that maximizes consultation request conversions.
