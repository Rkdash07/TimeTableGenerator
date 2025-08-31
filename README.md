# Automatic Time Table Genarator


An intelligent **Automatic Time Table Generator** system built with PHP and MySQL that uses **Genetic Algorithm** to automatically generate optimal class schedules for educational institutions.

![Languages](https://img.shields.io/badge/Languages-PHP%20%7C%20JavaScript%20%7C%20HTML%20%7C%20CSS-blue)
![Database](https://img.shields.io/badge/Database-MySQL-orange)
![Algorithm](https://img.shields.io/badge/Algorithm-Genetic%20Algorithm-green)
![Framework](https://img.shields.io/badge/Frontend-Bootstrap-purple)

## 📋 Table of Contents

- [About The Project](#about-the-project)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Algorithm](#algorithm)
- [Installation](#installation)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Screenshots](#screenshots)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)

## 🎯 About The Project

The **TimeTableGenerator** is a comprehensive web-based solution designed to automate the complex process of creating academic timetables for schools, colleges, and universities. Traditional timetable generation is a time-consuming manual process prone to conflicts and errors. This system leverages the power of **Genetic Algorithms** to generate optimized timetables that satisfy various constraints and requirements.

### Why Genetic Algorithm?

Genetic Algorithms are particularly well-suited for timetabling problems because they:
- Handle complex multi-constraint optimization problems efficiently
- Can find near-optimal solutions for NP-hard problems
- Provide flexibility in defining fitness functions
- Offer robust performance with large datasets

## ✨ Features

### 🔐 Authentication System
- **Secure Admin Login** with session management
- **Password validation** with encryption
- **Session timeout** protection

### 👨‍🏫 Faculty Management
- **Add, edit, and delete** faculty records
- **Faculty information** including name, designation, email, and phone
- **Faculty-subject allocation** system

### 📚 Subject Management
- **Comprehensive subject database** with subject codes
- **Credit hours configuration** for each subject
- **Subject-faculty mapping** with section-wise allocation

### 🧬 Genetic Algorithm Engine
- **Population-based optimization** for timetable generation
- **Fitness function** that minimizes scheduling conflicts
- **Crossover and mutation operations** for solution evolution
- **Constraint satisfaction** including:
  - No faculty time conflicts
  - Proper subject hour allocation
  - Section-wise scheduling

### 📊 Timetable Generation
- **Automated timetable creation** for multiple sections
- **5-day weekly schedule** with 6 periods per day
- **Real-time conflict resolution**
- **HTML table output** with clean formatting

### 💾 Data Management
- **MySQL database** for persistent data storage
- **CRUD operations** for all entities
- **Data integrity** and relationship management

## 🛠️ Technology Stack

### **Frontend Technologies**
- **[HTML5](https://html.spec.whatwg.org/)** - Structure and semantic markup
- **[CSS3](https://www.w3.org/Style/CSS/)** - Styling and responsive design
- **[SCSS](https://sass-lang.com/)** - CSS preprocessor for maintainable styles
- **[JavaScript](https://developer.mozilla.org/en-US/docs/Web/JavaScript)** - Interactive functionality
- **[Bootstrap](https://getbootstrap.com/)** - Responsive UI framework
- **[jQuery](https://jquery.com/)** - DOM manipulation and AJAX

### **Backend Technologies**
- **[PHP](https://www.php.net/)** - Server-side scripting language
- **[MySQL](https://www.mysql.com/)** - Relational database management system
- **[XAMPP](https://www.apachefriends.org/)** - Development environment

### **Development Tools**
- **[Apache](https://httpd.apache.org/)** - Web server
- **[phpMyAdmin](https://www.phpmyadmin.net/)** - Database administration tool

### **Algorithm Implementation**
- **Custom Genetic Algorithm** written in PHP
- **Population-based optimization**
- **Multi-objective fitness evaluation**

## 🧬 Algorithm

### Genetic Algorithm Implementation

The system implements a sophisticated genetic algorithm with the following components:

#### **1. Population Initialization**
