Admin Portfolio Management System Prompt
Project Requirement

Create a complete Portfolio Management Module inside the Admin Dashboard to dynamically control the Homepage Portfolio Section.

The admin portfolio system must follow the existing Homepage Portfolio Design Reference:

Same design pattern
Same brand colors
Same typography
Same spacing system
Same card style
Same image presentation style
Same responsive behavior

The goal is that the admin can manage all homepage portfolio content without changing code.

1. Portfolio Management Features

Create a complete CRUD-based Portfolio Management System.

Admin should be able to:

Create portfolio
Edit portfolio
Update portfolio
Delete portfolio
Change portfolio status
Control homepage visibility
Manage portfolio ordering
Upload project images
Manage project details
2. Admin Portfolio List Page

Create an admin portfolio listing interface.

Route:

/admin/portfolio

Design:

Follow admin dashboard theme
Reusable table component
Responsive layout

Table columns:

Field	Description
Image	Portfolio thumbnail
Project Name	Portfolio title
Category	Project category
Client Name	Client information
Featured Status	Show in homepage
Status	Active/Inactive
Sort Order	Display position
Created Date	Creation date
Action	Edit/Delete/View
Table Features

Implement:

Search portfolio
Filter by category
Filter by status
Sorting
Pagination
Bulk actions

Actions:

View
Edit
Duplicate
Delete
Change Status
3. Create Portfolio Form

Create a reusable admin form.

Route:

/admin/portfolio/create

Fields:

Basic Information
Project Title

Example:

E-Commerce Platform
Slug

Auto generate:

ecommerce-platform
Short Description

Used for homepage portfolio card.

Example:

Modern Laravel based ecommerce solution.
Full Description

Used for portfolio details page.

4. Portfolio Category Management

Create category selection.

Example:

Web Application
Mobile Application
E-Commerce
ERP System
SaaS Platform
API Development

Admin should manage categories.

Category CRUD:

/admin/portfolio/categories

Features:

Add category
Edit category
Delete category
Status management
5. Project Technology Management

Allow admin to add technologies used.

Example:

Laravel
React
Bootstrap
MySQL
Docker
AWS

UI:

Tag based input.

Example:

[ Laravel × ] 
[ React × ]
[ MySQL × ]
6. Portfolio Image Management

Create professional image management.

Features:

Upload multiple images
Main thumbnail selection
Image preview
Delete image
Drag & drop ordering

Image types:

Thumbnail Image
Gallery Images
Banner Image

Image optimization:

Compress uploaded images
Generate thumbnails
Maintain aspect ratio
Lazy loading support
7. Homepage Portfolio Control

The admin should directly control the homepage portfolio section.

Create settings:

Homepage Portfolio Settings

Fields:

Section Visibility
ON / OFF
Section Title

Example:

Our Recent Work
Section Subtitle

Example:

Explore our latest successful projects
Number of Projects Display

Example:

6 Projects
9 Projects
12 Projects
Portfolio Ordering

Options:

Newest First
Oldest First
Manual Ordering
Featured First
8. Featured Portfolio Management

Admin can select projects displayed on homepage.

Example:

Portfolio List:

☑ E-commerce Platform
☑ Banking Application
☐ Hospital Management
☑ CRM System

Only selected projects appear on homepage.

9. Portfolio Frontend Integration

Create frontend portfolio section dynamically.

Homepage:

/

Replace static portfolio design with dynamic data.

Frontend structure:

Portfolio Section

├── Section Header
├── Portfolio Filter
├── Portfolio Cards
└── View All Button
10. Portfolio Card Component

Create reusable portfolio card.

Follow existing homepage design.

Card contains:

Project Image

Project Category

Project Title

Short Description

Technology Tags

View Project Button

Example:

--------------------------------

[ Project Image ]

WEB APPLICATION

Bank Management System

Laravel | React | MySQL


VIEW PROJECT →

--------------------------------
11. Portfolio Details Page

Create frontend details page.

Route:

/portfolio/{slug}

Sections:

Hero Section

Contains:

Project image
Project title
Category
Technologies
Project Overview

Display:

Description
Features
Challenges
Solutions
Technology Stack

Example:

Backend:
Laravel

Frontend:
React

Database:
MySQL
Gallery

Display project screenshots.

Related Projects

Show similar portfolio items.

12. Database Design

Create optimized database structure.

portfolios table
id

title

slug

short_description

description

category_id

client_name

project_url

github_url

featured

status

sort_order

created_at

updated_at
portfolio_images table
id

portfolio_id

image

type

sort_order

created_at

updated_at
portfolio_categories table
id

name

slug

status

created_at

updated_at
portfolio_technologies table
id

portfolio_id

technology_name

created_at

updated_at
13. Laravel Backend Requirement

Build using:

Latest Laravel
MySQL
Blade Admin Template
Bootstrap 5
SCSS

Architecture:

Admin

Controller
    |
Service Layer
    |
Repository Layer
    |
Model
    |
Database
14. Code Quality Requirements

Follow:

SOLID principles
Clean architecture
Reusable components
Form validation
Image storage best practices
Security best practices
Optimized queries
Proper relationships
Maintainable code structure
15. Future Ready Features

Prepare architecture for:

API support
React frontend migration
Portfolio analytics
Client testimonials
Project approval workflow
Multi-language support
Final Output

Create a complete:

✅ Admin Portfolio Management UI
✅ Portfolio CRUD Backend
✅ Database Structure
✅ Image Management System
✅ Homepage Dynamic Portfolio Integration
✅ Portfolio Details Page
✅ Responsive Frontend Components
✅ Reusable Laravel Boilerplate Module

The final system should allow a non-technical admin user to completely manage the homepage portfolio section from the dashboard without modifying code.
