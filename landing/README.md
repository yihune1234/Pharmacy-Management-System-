# PHARMACIA Landing Page & Demo System

## 📁 Folder Structure

```
landing/
├── index.php              # Home page with hero section
├── about.php              # About PHARMACIA
├── features.php           # Detailed features list
├── contact.php            # Contact form
├── login.php              # Employee login
├── register.php           # Demo access registration
├── guest-dashboard.php    # Demo dashboard with role switching
├── logout.php             # Session cleanup
└── README.md              # This file
```

## 🎯 Pages Overview

### 1. **index.php** - Home Page
- Hero section with call-to-action
- Feature highlights (6 main features)
- Statistics section
- CTA section
- Responsive design

### 2. **about.php** - About Page
- Mission and vision
- Core values
- Key features list
- Technology stack
- Journey/timeline
- Why choose PHARMACIA
- Statistics

### 3. **features.php** - Features Page
- Inventory Management (6 features)
- Sales & POS (6 features)
- Pharmacy Features (4 features)
- Customer Management (4 features)
- Reporting & Analytics (4 features)
- Security & Performance (6 features)

### 4. **contact.php** - Contact Page
- Contact information
- Contact form
- Business hours
- Support information

### 5. **login.php** - Employee Login
- Username/password authentication
- Role-based redirect
- Demo credentials display
- Error handling

### 6. **register.php** - Demo Registration
- Simple name and email registration
- Creates guest session with all roles
- Redirects to guest dashboard

### 7. **guest-dashboard.php** - Demo Dashboard
- Role switcher (Admin, Pharmacist, Cashier)
- Dashboard statistics
- Role-specific features display
- Common features section
- Exit demo option

### 8. **logout.php** - Session Cleanup
- Destroys session
- Redirects to home

## 🎨 Design Features

### Consistent Design System
- **Color Scheme**: Purple gradient (#667eea to #764ba2)
- **Typography**: Segoe UI, clean and modern
- **Spacing**: Consistent padding and margins
- **Shadows**: Subtle box shadows for depth
- **Borders**: Rounded corners (8-20px)

### Responsive Design
- Mobile-first approach
- Flexbox and CSS Grid layouts
- Media queries for tablets and phones
- Touch-friendly buttons and inputs

### Navigation
- Sticky navbar on all pages
- Consistent navigation links
- Logo linking to home
- Auth buttons in top right

## 🔐 Security Features

- Session-based authentication
- Password hashing (Argon2ID)
- Prepared statements for database queries
- Input sanitization with htmlspecialchars()
- CSRF protection ready

## 🚀 How to Use

### For Employees
1. Click "Login" button
2. Enter username and password
3. Redirected to role-based dashboard

### For Demo Users
1. Click "Try Demo" button
2. Enter name and email
3. Access demo dashboard with all roles
4. Switch between Admin, Pharmacist, Cashier
5. Explore all features
6. Click "Exit Demo" to logout

## 📱 Responsive Breakpoints

- **Desktop**: 1200px+
- **Tablet**: 768px - 1199px
- **Mobile**: Below 768px

## 🎯 Key Features

### Home Page
- ✅ Hero section with gradient background
- ✅ Feature cards with hover effects
- ✅ Statistics section
- ✅ CTA section
- ✅ Responsive grid layout

### About Page
- ✅ Mission and vision statements
- ✅ Core values list
- ✅ Technology stack
- ✅ Timeline of development
- ✅ Team/features cards
- ✅ Statistics

### Features Page
- ✅ 6 feature categories
- ✅ 30+ individual features
- ✅ Icons for each feature
- ✅ Detailed descriptions
- ✅ Organized grid layout

### Contact Page
- ✅ Contact information display
- ✅ Working contact form
- ✅ Business hours
- ✅ Support information
- ✅ Form validation

### Login Page
- ✅ Clean login form
- ✅ Error messages
- ✅ Demo credentials display
- ✅ Link to demo registration
- ✅ Back to home link

### Demo System
- ✅ Guest registration
- ✅ Role switching
- ✅ Dashboard statistics
- ✅ Feature showcase
- ✅ Session management

## 🔧 Configuration

### Database Connection
The login page connects to the main database using:
```php
require_once __DIR__ . '/../config/config.php';
```

### Session Management
- Guest sessions stored in `$_SESSION['guest_mode']`
- Guest roles: admin, pharmacist, cashier
- Current role: `$_SESSION['current_role']`

## 📊 Statistics Displayed

### Dashboard Stats
- 1,250 Medicines in Stock
- 342 Sales Today
- 5,680 Total Customers
- Rs. 45,230 Today's Revenue

## 🎓 Demo Features by Role

### Admin
- Inventory Management
- Employee Management
- Supplier Management
- Advanced Reports
- Security Settings
- System Configuration
- Alert Management
- Audit Logs

### Pharmacist
- POS System
- Drug Interactions
- Prescriptions
- Customer Profiles
- Inventory Search
- Refund Processing
- Receipt Generation
- Loyalty Points

### Cashier
- Process Sales
- Payment Methods
- Generate Receipts
- Register Customers
- Customer Lookup
- Process Refunds
- Transaction History
- Daily Summary

## 🌐 Navigation Flow

```
index.php (Home)
├── about.php
├── features.php
├── contact.php
├── login.php → (Employee Dashboard)
└── register.php → guest-dashboard.php
    ├── Switch to Admin
    ├── Switch to Pharmacist
    └── Switch to Cashier
        └── logout.php → index.php
```

## 📝 Notes

- All pages use consistent styling
- Responsive design works on all devices
- Demo mode allows exploring all features
- Real login requires valid employee credentials
- Guest sessions are temporary (session-based)

## 🚀 Deployment

1. Place `landing/` folder in web root
2. Ensure `config/config.php` is accessible
3. Database must have `employee` and `roles` tables
4. Test login with demo credentials
5. Test demo registration and role switching

## 📞 Support

For issues or questions about the landing page:
- Check contact.php for support information
- Review error messages in browser console
- Verify database connection in config.php
