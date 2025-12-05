# LocalTechFix - PC & Laptop Repair Services

A full-featured **PHP/MySQL ticket management system** for local computer repair services. Includes customer portal, admin panel, and complete authentication system.

![PHP](https://img.shields.io/badge/PHP-7.4%2B-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)
![License](https://img.shields.io/badge/License-MIT-green)

## 🚀 Features

### 🔐 Authentication System
- User registration with password hashing (bcrypt)
- Secure login with session management
- Role-based access control (Customer/Admin)
- CSRF protection on all forms
- Auto-login after registration

### 👤 Customer Portal
- **Dashboard** - View ticket statistics and recent activity
- **Create Tickets** - Submit repair requests with file uploads
- **Ticket Management** - View, filter, and track all tickets
- **Messaging** - Real-time communication with support team
- **File Uploads** - Attach images, PDFs, logs (up to 5MB)

### 🛡️ Admin Panel
- **Dashboard** - Overview statistics and analytics
- **Ticket Management** - View, filter, and manage all tickets
- **Status Updates** - Change ticket status and priority
- **Customer Support** - Reply to customer messages
- **Customer Management** - View all customers and their ticket history

### 📝 Knowledge Base
- **Article Management** - Create, edit, and categorize help articles
- **Public Help Center** - Searchable repository of guides and tutorials
- **Rich Text Editor** - WYSIWYG editing for article content

### 💰 Invoicing System
- **Invoice Generation** - Create professional invoices for repairs
- **PDF Export** - Print-ready views for billing
- **Customer Access** - Clients can view their invoice history

### 🖥️ Homepage CMS
- **Content Management** - Edit all homepage text and sections from admin
- **Hero & Services** - Customize hero banner and service offerings
- **Pricing & Testimonials** - Manage pricing plans and client reviews
- **Dynamic Updates** - Real-time changes without touching code

### 🎨 Frontend Features
- Modern, responsive design
- Dark mode toggle with localStorage persistence
- Smooth animations and transitions
- Mobile-friendly navigation
- Interactive FAQ accordion
- Pricing tables and testimonials

## 📁 Project Structure

```
Proj2/
├── config/              # Database and app configuration
├── includes/            # Authentication and helper functions
│   └── public_footer.php # Site-wide footer component
├── public/              # Public-facing pages and assets
│   ├── assets/         # CSS, JS, and uploaded files
│   ├── login.php
│   ├── register.php
│   └── index.html
├── customer/            # Customer portal pages
│   ├── dashboard.php
│   ├── tickets.php
│   ├── ticket-detail.php
│   └── create-ticket.php
├── admin/               # Admin panel pages
│   ├── dashboard.php
│   ├── tickets.php
│   ├── ticket-detail.php
│   ├── customers.php
│   ├── invoices.php    # Billing & Invoicing
│   ├── settings.php    # Site settings
│   └── cms/           # Homepage CMS Editors
├── database_schema.sql  # Database setup script
├── .htaccess           # Security & Error Handling
├── 404.php             # Custom 404 Error Page
├── 403.php             # Custom 403 Error Page
└── 500.php             # Custom 500 Error Page
```

## 🛠️ Technologies Used

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQL 5.7+** - Database
- **PDO** - Database abstraction layer (SQL injection prevention)
- **Sessions** - User authentication

### Frontend
- **HTML5** - Structure
- **CSS3** - Styling (Custom Properties, Flexbox, Grid)
- **Vanilla JavaScript (ES6+)** - Interactivity
- **Font Awesome** - Icons
- **Google Fonts (Inter)** - Typography

### Security
- Password hashing with `password_hash()`
- Prepared statements (PDO) for SQL injection prevention
- CSRF token validation
- XSS protection with `htmlspecialchars()`
- File upload validation
- Session security settings
- Custom Error Pages (404, 403, 500)

## 📦 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache with mod_rewrite (or Nginx)
- XAMPP/WAMP (for local development)

### Setup Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/buttlerkid/pc-fixer.git
   cd pc-fixer
   ```

2. **Set up database**
   - Create a MySQL database named `localtechfix`
   - Import the schema:
     ```bash
     mysql -u root -p localtechfix < database_schema.sql
     ```

3. **Configure database connection**
   - Edit `config/database.php`
   - Update credentials if needed (default: root with no password)

4. **Set up web server**
   - **XAMPP**: Copy project to `C:\xampp\htdocs\localtechfix`
   - **Production**: Upload via FTP/SFTP to your hosting

5. **Set permissions**
   - Ensure `public/assets/uploads/` is writable
   ```bash
   chmod 755 public/assets/uploads/
   ```

6. **Access the application**
   - Local: `http://localhost/localtechfix/public/login.php`
   - Production: `https://yourdomain.com/public/login.php`

## 👥 Demo Accounts

### Admin Account
- **Email**: `admin@localtechfix.com`
- **Password**: `admin123`
- **Access**: Full admin panel with ticket management

### Customer Account
- **Email**: `customer@test.com`
- **Password**: `customer123`
- **Access**: Customer portal with ticket creation

## 🗄️ Database Schema

### Tables
- **users** - User accounts (customers and admins)
- **tickets** - Repair tickets
- **messages** - Ticket messages/communication
- **files** - Uploaded file attachments
- **articles** - Knowledge base articles
- **invoices** - Billing records
- **invoice_items** - Line items for invoices
- **cms_*** - Various tables for homepage content (hero, services, etc.)

### Ticket Statuses
- `pending` - Awaiting review
- `in_progress` - Currently being worked on
- `waiting_parts` - Waiting for parts to arrive
- `completed` - Repair completed
- `cancelled` - Ticket cancelled

### Priority Levels
- `low`, `medium`, `high`, `urgent`

## 🎯 Usage

### For Customers
1. Register an account or login
2. Create a new ticket describing the issue
3. Upload relevant files (screenshots, error logs)
4. Track ticket status and communicate with support
5. Receive notifications when status changes

### For Admins
1. Login with admin credentials
2. View all tickets in the admin dashboard
3. Update ticket status and priority
4. Reply to customer messages
5. Manage customer accounts

## 🔒 Security Features

- ✅ Password hashing with bcrypt
- ✅ SQL injection prevention (PDO prepared statements)
- ✅ XSS protection (output escaping)
- ✅ CSRF token validation
- ✅ File upload validation (type, size)
- ✅ Session security (httponly, secure flags)
- ✅ .htaccess protection for sensitive directories

## 📱 Browser Support

Works on all modern browsers:
- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## 🚀 Deployment

### Shared Hosting
1. Upload files via FTP/SFTP
2. Create MySQL database via cPanel
3. Import `database_schema.sql` via phpMyAdmin
4. Update `config/database.php` with production credentials
5. Update `config/config.php` with production URL

### VPS/Dedicated Server
1. Set up LAMP stack (Linux, Apache, MySQL, PHP)
2. Clone repository to web root
3. Create database and import schema
4. Configure Apache virtual host
5. Enable SSL/HTTPS (recommended)

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

© 2023 - 2026 LocalTechFix. All rights reserved.

## 📞 Support

For issues or questions, please create an issue in the GitHub repository.

---

**Built with ❤️ using PHP and MySQL**
