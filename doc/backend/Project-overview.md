Admin Dashboard Design & Boilerplate Template Prompt

Project Requirement:

Create a modern, scalable, and reusable Admin Dashboard Template that follows the same design pattern, UI style, theme colors, and brand identity used in the existing homepage design.

The admin panel should feel like a natural extension of the main website, maintaining consistent branding, typography, spacing, components, and visual language.

1. Design System & Branding
Analyze the existing homepage design and reuse:
Primary brand color
Secondary colors
Button styles
Typography system
Border radius
Card styles
Shadows
Spacing system
Icon style
Component patterns
Create a unified design system:
Color variables
Typography variables
Component utility classes
Reusable UI patterns
Admin dashboard should look professional, clean, modern, and suitable for SaaS/business applications.
2. Admin Dashboard Layout Structure

Create a complete admin dashboard layout including:

Main Layout
Fixed sidebar navigation
Top navigation/header
Main content area
Footer section
Breadcrumb navigation
Notification area
User profile dropdown
Sidebar Menu Design

Create reusable sidebar component with:

Dashboard
User Management
Role & Permission Management
Content Management
Service Management
Portfolio Management
Blog Management
Settings
Reports
Logout

Features:

Active menu highlighting
Collapsible sidebar
Mobile drawer sidebar
Icon-based navigation
Smooth transition animation
3. Dashboard Homepage

Create dashboard overview page with:

Statistics Cards

Reusable card component:

Examples:

Total Users
Total Projects
Total Services
Revenue
Orders
Website Visits

Each card should support:

Icon
Title
Value
Percentage growth
Status indicator
Trend information
Data Visualization

Create reusable chart components:

Line chart
Bar chart
Pie chart
Area chart

Examples:

Monthly sales overview
User registration analytics
Website traffic
Project statistics
Data Tables

Create reusable table component with:

Features:

Search
Filter
Sorting
Pagination
Status badge
Action buttons

Actions:

View
Edit
Delete
Change status
4. Admin Authentication System

Create complete admin authentication UI.

Login Page

Design:

Brand logo
Email input
Password input
Remember me checkbox
Forgot password link
Login button
Social login placeholder (optional)

Requirements:

Modern centered login card
Same homepage branding style
Responsive layout
Authentication Pages

Create:

Login
/admin/login
Forgot Password
/admin/password/reset
Reset Password
/admin/password/reset/{token}
Profile
/admin/profile
Authentication Features

Include:

Secure login flow
Session management
Remember login option
Logout functionality
Role-based access structure
Permission-ready architecture
5. Reusable Boilerplate Architecture

Create this as a reusable admin template.

Structure:

admin/

├── layouts/
│   ├── admin-layout
│   ├── sidebar
│   ├── header
│   └── footer

├── components/
│   ├── cards
│   ├── tables
│   ├── forms
│   ├── buttons
│   ├── modals
│   ├── alerts
│   └── charts

├── pages/
│   ├── dashboard
│   ├── users
│   ├── settings
│   └── reports

├── assets/
│   ├── css
│   ├── scss
│   ├── js
│   └── images
6. Laravel Blade Boilerplate Requirement

Create using:

Latest Laravel version
Blade Template Engine
Bootstrap 5
SCSS architecture
MySQL compatible

Rules:

Create only static HTML structure
Keep components reusable
Prepare for future Laravel integration

Example:

resources/
 ├── views/
 │    └── admin/
 │         ├── layouts/
 │         ├── components/
 │         └── pages/

 ├── sass/
 │    └── admin/
 │         ├── variables.scss
 │         ├── layout.scss
 │         ├── components.scss
 │         └── responsive.scss
7. Responsive Design Requirements

Must support:

Desktop
Large screen dashboard
Fixed sidebar
Multi-column cards
Full data tables
Tablet
Collapsible navigation
Responsive cards
Optimized spacing
Mobile
Mobile sidebar drawer
Hamburger menu
Single-column layout
Touch-friendly buttons
Responsive tables

Test for:

320px
375px
768px
1024px
1440px
8. Security & Code Quality

Follow:

Clean code structure
Component-based approach
DRY principles
Optimized assets
Proper naming conventions
Maintainable SCSS architecture
Accessibility best practices
SEO-friendly HTML structure
9. Final Output Requirement

Deliver:

Complete Admin Dashboard UI Design
Reusable Blade Boilerplate Structure
Authentication UI Pages
Responsive Layout
SCSS Design System
Reusable Components
Clean Production-Ready Folder Structure

The final result should be a professional SaaS-level Laravel Admin Dashboard Template that can be reused for multiple projects while keeping the same Codexehub brand identity and homepage design language.
