# 🎯 PHARMACIA Landing Page - Quick Start Guide

## 📍 Location
All landing page files are in the `landing/` folder at the root of the project.

## 🚀 Quick Access

### URLs
- **Home**: `http://localhost/pharmacy/landing/index.php`
- **About**: `http://localhost/pharmacy/landing/about.php`
- **Features**: `http://localhost/pharmacy/landing/features.php`
- **Contact**: `http://localhost/pharmacy/landing/contact.php`
- **Login**: `http://localhost/pharmacy/landing/login.php`
- **Try Demo**: `http://localhost/pharmacy/landing/register.php`

## 📁 Files Created

```
landing/
├── index.php              (12 KB) - Home page with hero section
├── about.php              (15 KB) - About PHARMACIA
├── features.php           (25 KB) - Detailed features showcase
├── contact.php            (13 KB) - Contact form and info
├── login.php              (9.5 KB) - Employee login
├── register.php           (9.5 KB) - Demo registration
├── guest-dashboard.php    (19 KB) - Demo dashboard with role switching
├── logout.php             (84 B) - Session cleanup
└── README.md              (6 KB) - Detailed documentation
```

**Total Size**: ~108 KB

## 🎨 Design Features

### Consistent Design System
✅ **Color Scheme**: Purple gradient (#667eea → #764ba2)
✅ **Typography**: Segoe UI, clean and modern
✅ **Spacing**: Consistent padding and margins
✅ **Shadows**: Subtle depth effects
✅ **Borders**: Rounded corners (8-20px)
✅ **Responsive**: Mobile, tablet, desktop

### Navigation
- Sticky navbar on all pages
- Consistent branding
- Quick access buttons
- Responsive menu

## 👥 User Flows

### Flow 1: Employee Login
```
Login Page → Enter Credentials → Validate → Role-Based Redirect
                                              ├── Admin Dashboard
                                              ├── Pharmacist Dashboard
                                              └── Cashier Dashboard
```

### Flow 2: Demo Access (All Roles)
```
Home → Try Demo → Register (Name + Email) → Guest Dashboard
                                              ├── Switch to Admin
                                              ├── Switch to Pharmacist
                                              └── Switch to Cashier
                                                  └── Exit Demo
```

## 🎯 Page Features

### 1. Home Page (index.php)
- Hero section with gradient background
- 6 feature cards with hover effects
- Statistics section (4 metrics)
- CTA section
- Responsive grid layout
- **Visitors**: First impression of PHARMACIA

### 2. About Page (about.php)
- Mission and vision statements
- Core values (6 values)
- Key features list (10 features)
- Technology stack
- Development timeline (5 phases)
- Why choose PHARMACIA (6 reasons)
- Statistics (7 metrics)
- **Visitors**: Learn about the company

### 3. Features Page (features.php)
- 6 feature categories
- 30+ individual features
- Icons for each feature
- Detailed descriptions
- Organized grid layout
- **Visitors**: Explore all capabilities

### 4. Contact Page (contact.php)
- Contact information (5 sections)
- Working contact form
- Business hours
- Support information
- Form validation
- **Visitors**: Get in touch

### 5. Login Page (login.php)
- Employee authentication
- Database validation
- Role-based redirect
- Demo credentials display
- Error handling
- **Users**: Employees with accounts

### 6. Demo Registration (register.php)
- Simple registration (Name + Email)
- Creates guest session
- Grants all roles access
- Redirects to dashboard
- **Users**: Anyone wanting to try

### 7. Guest Dashboard (guest-dashboard.php)
- Role switcher (Admin, Pharmacist, Cashier)
- Dashboard statistics (4 metrics)
- Role-specific features (8 features each)
- Common features (8 features)
- Exit demo option
- **Users**: Demo users exploring features

## 🔐 Security

✅ Session-based authentication
✅ Password hashing (Argon2ID)
✅ Prepared statements
✅ Input sanitization
✅ CSRF protection ready
✅ Role-based access control

## 📱 Responsive Design

### Breakpoints
- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: Below 768px

### Features
- Flexible grid layouts
- Touch-friendly buttons
- Readable font sizes
- Optimized images
- Mobile navigation

## 🎓 Demo System

### What Guests Can See

**Admin Role**
- Inventory Management
- Employee Management
- Supplier Management
- Advanced Reports
- Security Settings
- System Configuration
- Alert Management
- Audit Logs

**Pharmacist Role**
- POS System
- Drug Interactions
- Prescriptions
- Customer Profiles
- Inventory Search
- Refund Processing
- Receipt Generation
- Loyalty Points

**Cashier Role**
- Process Sales
- Payment Methods
- Generate Receipts
- Register Customers
- Customer Lookup
- Process Refunds
- Transaction History
- Daily Summary

**All Roles**
- 2FA Security
- High Performance
- Data Encryption
- Smart Alerts
- Analytics
- Export Reports
- Responsive Design
- 24/7 Support

## 🔧 Configuration

### Database Connection
```php
require_once __DIR__ . '/../config/config.php';
```

### Session Variables (Guest)
```php
$_SESSION['guest_mode'] = true;
$_SESSION['guest_name'] = 'User Name';
$_SESSION['guest_email'] = 'user@email.com';
$_SESSION['guest_roles'] = ['admin', 'pharmacist', 'cashier'];
$_SESSION['current_role'] = 'admin';
```

### Session Variables (Employee)
```php
$_SESSION['user'] = $employee_id;
$_SESSION['username'] = $username;
$_SESSION['name'] = $first_name;
$_SESSION['role'] = $role_name;
$_SESSION['last_activity'] = time();
```

## 📊 Statistics Displayed

### Home Page
- 10,000+ Lines of Code
- 33 Features
- 80% Faster Performance
- 24/7 Support Ready

### Dashboard
- 1,250 Medicines in Stock
- 342 Sales Today
- 5,680 Total Customers
- Rs. 45,230 Today's Revenue

## 🌐 Navigation Map

```
index.php (Home)
├── Navigation Links
│   ├── Home
│   ├── About
│   ├── Features
│   └── Contact
├── Auth Buttons
│   ├── Login → login.php
│   └── Try Demo → register.php
└── CTA Buttons
    ├── Get Started → register.php
    └── Learn More → about.php

about.php (About)
├── Same Navigation
└── Links to Features & Contact

features.php (Features)
├── Same Navigation
└── Links to About & Contact

contact.php (Contact)
├── Same Navigation
└── Contact Form

login.php (Login)
├── Back to Home
├── Register Link → register.php
└── Demo Credentials Display

register.php (Demo Registration)
├── Back to Home
├── Login Link → login.php
└── Features List

guest-dashboard.php (Demo Dashboard)
├── Role Switcher
│   ├── Admin
│   ├── Pharmacist
│   └── Cashier
├── Dashboard Stats
├── Role-Specific Features
├── Common Features
└── Exit Demo → logout.php
```

## 🚀 Deployment Steps

1. **Verify Files**
   ```bash
   ls -la landing/
   ```

2. **Check Permissions**
   ```bash
   chmod 755 landing/
   chmod 644 landing/*.php
   ```

3. **Test URLs**
   - http://localhost/pharmacy/landing/index.php
   - http://localhost/pharmacy/landing/login.php
   - http://localhost/pharmacy/landing/register.php

4. **Test Login**
   - Username: admin
   - Password: admin123

5. **Test Demo**
   - Name: Test User
   - Email: test@example.com
   - Try switching roles

## 🐛 Troubleshooting

### Issue: Login not working
- **Check**: Database connection in config.php
- **Check**: Employee table exists
- **Check**: Demo credentials are correct

### Issue: Demo not loading
- **Check**: Session is enabled
- **Check**: No headers sent before session_start()
- **Check**: Browser cookies enabled

### Issue: Styling not loading
- **Check**: Tailwind CDN is accessible
- **Check**: Font Awesome CDN is accessible
- **Check**: No CSS conflicts

### Issue: Database errors
- **Check**: config.php path is correct
- **Check**: Database credentials are valid
- **Check**: Tables exist (employee, roles)

## 📞 Support

For issues:
1. Check browser console for errors
2. Check PHP error logs
3. Verify database connection
4. Test with demo credentials
5. Review README.md in landing folder

## ✅ Checklist

- [x] Home page created
- [x] About page created
- [x] Features page created
- [x] Contact page created
- [x] Login page created
- [x] Demo registration created
- [x] Guest dashboard created
- [x] Logout page created
- [x] Consistent design applied
- [x] Responsive design implemented
- [x] Navigation working
- [x] Database integration ready
- [x] Session management ready
- [x] Documentation complete

## 🎉 Summary

✅ **8 Pages Created** with consistent design
✅ **Responsive Design** for all devices
✅ **Demo System** with role switching
✅ **Employee Login** with database integration
✅ **Professional UI** with modern styling
✅ **Complete Documentation** included

**Ready for Production!**
