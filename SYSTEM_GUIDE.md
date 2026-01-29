# PHARMACIA Pharmacy Management System

## 🚀 Complete System Implementation

### 📋 System Overview
PHARMACIA is a comprehensive web-based Pharmacy Management System built with PHP and MySQL, featuring complete inventory management, sales tracking, employee management, customer relations, supplier management, and advanced reporting capabilities.

### 🔧 Quick Start Guide

#### 1. Database Setup
```bash
# Import database schema
mysql -u root -p pharmacy_db < database/pharmacy.sql

# Or run installation script
php database/install.php
```

#### 2. Populate Sample Data
```bash
# Run sample data population
php database/populate_sample_data.php
```

#### 3. Access the System
- **URL**: `http://localhost/Pharmacy-Management-System/`
- **Admin Login**: username: `admin`, password: `admin`
- **Employee Logins**: 
  - johnsmith / password123 (Pharmacist)
  - sarahjohnson / password123 (Manager)
  - michaelbrown / password123 (Cashier)
  - emilydavis / password123 (Pharmacist)
  - davidwilson / password123 (Staff)

### 🏗️ System Architecture

#### Directory Structure
```
Pharmacy-Management-System-/
├── config/
│   ├── config.php              # Database configuration
│   └── security.php             # Security settings & functions
├── includes/
│   ├── alerts.php              # Flash message system
│   ├── session_check.php        # Authentication middleware
│   └── activity_logger.php     # Activity logging functions
├── database/
│   ├── pharmacy.sql            # Database schema
│   ├── install.php             # Installation script
│   └── populate_sample_data.php # Sample data generator
├── modules/
│   ├── auth/                   # Authentication system
│   ├── admin/                  # Admin panel (full access)
│   │   ├── customers/          # Customer management
│   │   ├── employees/          # Employee management
│   │   ├── inventory/          # Medicine inventory
│   │   ├── purchases/          # Purchase management
│   │   ├── reports/            # Reports & analytics
│   │   ├── sales/              # Sales & POS
│   │   ├── suppliers/          # Supplier management
│   │   └── alerts/             # Alert system
│   └── pharmacist/            # Pharmacist panel (limited access)
├── assets/                     # Static assets
└── index.php                  # Entry point
```

### 🎯 Core Features

#### 1. **Authentication & Security**
- Role-based access control (Admin, Pharmacist, Manager, Cashier, Staff)
- Secure session management with timeout
- Password hashing with Argon2ID
- CSRF protection
- Rate limiting
- SQL injection prevention
- HTTPS enforcement

#### 2. **Inventory Management**
- Complete CRUD operations for medicines
- Real-time stock tracking
- Low stock alerts
- Expiry date monitoring
- Category and location management
- Automatic stock updates via triggers

#### 3. **Sales & POS System**
- Multi-step sales process
- Customer selection and management
- Real-time stock validation
- Invoice generation
- Sales recording with employee tracking
- Refund processing

#### 4. **Purchase Management**
- Supplier management
- Purchase order creation
- Payment tracking
- Credit limit management
- Purchase history

#### 5. **Customer Management**
- Customer registration and management
- Loyalty program with points and tiers
- Purchase history tracking
- Invoice management
- Customer analytics

#### 6. **Employee Management**
- Employee registration and management
- Role assignment
- Activity tracking
- Salary management
- Performance analytics

#### 7. **Supplier Management**
- Supplier registration and management
- Balance tracking
- Performance rating
- Purchase history
- Credit management

#### 8. **Reporting & Analytics**
- Daily sales reports
- Monthly revenue tracking
- Inventory reports
- Employee performance metrics
- Customer analytics
- Export to PDF/Excel
- Interactive charts and visualizations

#### 9. **Alert System**
- Low stock warnings
- Expiry alerts
- System health monitoring
- Sound notifications
- Email alerts for critical issues
- Persistent alert logging

### 🔒 Security Features

#### Multi-Layer Security
1. **Application Layer**: Input validation, CSRF protection
2. **Session Layer**: Secure session management, timeout protection
3. **Database Layer**: Prepared statements, parameterized queries
4. **Network Layer**: HTTPS enforcement, security headers
5. **Monitoring Layer**: Activity logging, audit trails

### 📊 Database Schema

#### Core Tables
- **meds**: Medicine inventory
- **customer**: Customer information
- **employee**: Employee details
- **suppliers**: Supplier information
- **sales**: Sales transactions
- **sales_items**: Individual sale items
- **purchase**: Purchase orders
- **activity_logs**: System activity tracking
- **audit_log**: Change tracking
- **roles**: User roles
- **refunds**: Refund records

### 🎨 User Interface

#### Design Features
- Modern, responsive design with Tailwind CSS
- Card-based layout
- Interactive charts with Chart.js
- Real-time notifications
- Mobile-friendly interface
- Accessibility features

### 📈 System Flows

#### A. Stock Entry Flow
```
Supplier → Purchase → Inventory Increase → Stock Update → Alert System
```

#### B. Stock Sale Flow
```
Customer → POS → Sales → Inventory Decrease → Stock Update → Alert System
```

#### C. Monitoring Flow
```
Reports → Analytics → Alerts → Management Decisions → Action Items
```

### 🔧 Configuration

#### Database Configuration
```php
// config/config.php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'pharmacy_db');
```

### 🚀 Deployment

#### Requirements
- PHP 7.4+ or PHP 8.0+
- MySQL 5.7+ or MariaDB 10.2+
- Apache/Nginx web server
- SSL certificate (for production)

#### Installation Steps
1. Clone/download the repository
2. Configure database settings in `config/config.php`
3. Import database schema
4. Run sample data population
5. Configure web server
6. Set up SSL certificate
7. Test the system

---

**PHARMACIA Pharmacy Management System** - Complete, Secure, and Feature-Rich Solution for Modern Pharmacy Operations.
