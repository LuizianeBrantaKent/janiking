# **JaniKing Franchise Portal Documentation**

This document provides a comprehensive overview and technical documentation for the JaniKing Franchise Portal, including deployment instructions, feature descriptions, and usage of each major PHP file. Use this guide to set up, deploy, and maintain your JaniKing franchisemanagement platform.

## **Deployment & Environment Setup**

The JaniKing Franchise Portal is a PHP/MySQL-based web application designed to manage franchise operations, including bookings, users, inventory, documents, training, messaging, and more.

### **1\. Dependencies**

To run this application, ensure the following:

* PHP 7.4+ (with PDO MySQL extension)  
* MySQL or MariaDB  
* Web server: Apache, Nginx, or compatible  
* Composer (optional for PHP dependency management)  
* Front-end:  
  * [Bootstrap 5](https://getbootstrap.com/)  
  * [Font Awesome 6](https://fontawesome.com/)  
  * Javascript/ES6 support

* Sample Credentials:

| Role | Email | Password |
| :---- | :---- | :---- |
| Admin | admin@janiking.com | Admin@123 |
| Staff | staff@janiking.com | Staff@123 |
| Franchisee | franchisee@gmail.com | Franchisee@123 |

### **3\. Environment Setup Steps** 

**a. Database** 

1\. **Create Database** 

Access phpMyAdmin or MySQL CLI and create a new database: 

| `CREATE DATABASE janiking;`  |
| :---- |

2\. **Import Schema** 

Import the provided janiking.sql file into your database, which includes tables such as `franchisees , users , products , bookings , documents , training , messages` , etc. 

* In **phpMyAdmin**:   
  * Select `janikin`g database   
  * Click `Import`   
  * Choose `janiking.sq`l and upload 

**b. Configuration** 

**1\. Database Credentials** 

Edit `/db/config.php` to set your database credentials: 

**2\. Security** 

* Ensure `/db/config.php` is **not** inside the web root.   
* Use strong passwords for production.   
* Enable HTTPS for secure cookies. 

**c. Running the Application** 

* Copy the project folder to htdocs/janiking in your XAMPP installation.   
* Start Apache and MySQL via the XAMPP control panel.   
* Access the portal at: 

`http://localhost//index.php` 

---

## **Feature Overview by File**

Below is a breakdown of all major PHP files, explaining their purpose and how they interconnect.

---

### **index.php – Landing Page**

Presents the public homepage for JaniKing. Contains:

* Hero section with call-to-action  
* Franchise benefits/features  
* Franchisee testimonials  
* Final calls to action for joining or booking a consultation

Purpose:  
Attracts new franchisees and provides access to booking and information.

---

### **about\_us.php – About JaniKing**

Displays company history, mission, values, global footprint, franchise support, awards, and final CTA for joining.

Purpose:  
Showcases credibility and support for potential franchisees.

---

### **book\_appointment.php – Book Franchise Consultation**

Implements a booking form for prospective franchisees:

* Validates input fields  
* Checks time slot availability  
* Stores bookings in the database  
* Displays confirmation/errors

Purpose:  
Streamlines franchisee onboarding by allowing prospects to schedule consultations.

---

### **join\_us.php – Join Franchise CTA Page**

Explains why and how to join as a JaniKing franchisee, details the application process, and answers FAQs.

Purpose:  
Guides and converts interested visitors into franchise applicants.

---

### **login.php – User Login**

Multi-role login panel for Admin, Staff, and Franchisee. Implements:

* Secure session management (CSRF, cookie parameters)  
* Role-based authentication  
* Redirects to the appropriate dashboard

Purpose:  
Secure, role-based access to internal portal features.

---

### **admin\_dash.php – Admin Dashboard**

Main dashboard for Admins, showing:

* Key analytics (bookings, products, users, franchisees)  
* Urgent notifications (pending bookings, low/out-of-stock products)  
* Recent activities (users, bookings, franchisees, announcements)  
* Quick access links to core features

Purpose:  
Centralized control and monitoring of all franchise operations.

---

.

**admin\_announcements.php – Admin Communication Center**

Allows Admins to:

* Post announcements to the network  
* Send/broadcast direct messages  
* Manage threads (reply, delete)  
* View inbox/sent messages

Purpose:  
Enables efficient communication with all users and franchisees.

---

### **admin\_documents.php – Admin Document Management**

* Upload documents for specific franchisees (PDF/DOC/TXT)  
* List, view, edit, and delete uploaded documents  
* Assign visibility to individual franchisees

Purpose:  
Distribute business-critical documents to franchisees.

---

### **admin\_manage\_appointments.php – Manage Appointments**

Admin can:

* View all bookings  
* Approve, edit, reschedule, or delete appointments  
* See detailed guest/franchisee info

Purpose:  
Centralized management of all franchise-related appointments.

---

### **admin\_manage\_franchisee.php – Manage Franchisees**

Features:

* Search and list all franchisees  
* Add, edit, activate/deactivate, or delete franchisees  
* View franchisee stats (active/inactive)

Purpose:  
Maintain franchisee records and status.

---

### **admin\_manage\_inventory.php – Manage Inventory**

* List all products and stock levels  
* Filter by stock status (In Stock, Low Stock, Out of Stock)  
* Add, edit, view, or delete inventory items

Purpose:  
Inventory oversight for products supplied to franchisees.

---

### **admin\_manage\_users.php – Manage Users**

* List/search users by role, email, or name  
* Add, edit, activate/deactivate, or remove users  
* Visualize user role distribution

Purpose:  
User account administration for staff and admin roles.

---

### **admin\_profile.php – Admin Profile Settings**

Allows admins to update their:

* Name, email, phone, and role  
* Password

Purpose:  
Personal account management for administrators.

---

### **admin\_reports.php – Reports Generator**

* Select report type (Inventory, Users, Franchisees, Bookings)  
* Filter by date range  
* Submit to generate/export reports (Excel)

Purpose:  
Business analytics and compliance reporting.

---

### **admin\_trainings.php – Upload Training Files**

* Upload and manage franchisee training materials  
* List downloadable/viewable training content

Purpose:  
Training resource management.

---

### **admin\_uploads.php – Admin Upload Center**

* General upload interface for documents/training files

Purpose:  
Central hub for file/resource management.

---

### **franchisee\_dash.php – Franchisee Dashboard**

For logged-in franchisees, features:

* Quick links to all main features  
* Analytics: documents, unread messages, training, products  
* Recent notifications

Purpose:  
Home base for all franchise operations.

---

### **franchisee\_documents.php – Franchisee Document Access**

* List, view, and download documents shared by admin/staff  
* Secure document access

Purpose:  
Self-service portal for franchisee documentation.

---

### **franchisee\_messaging.php – Franchisee Messaging**

* Inbox/sent messages  
* View announcements  
* Compose direct messages to staff/admin

Purpose:  
Streamlined communication with headquarters.

---

### **franchisee\_products.php – Product Ordering**

* Browse, filter, and search product catalogue  
* Add to cart and checkout via PayPal  
* Pagination and stock status badges

Purpose:  
E-commerce platform for franchisees (ordering supplies, uniforms, etc.)

### **franchisee\_profile.php – Franchisee Profile Management**

* View/edit business profile (contact, ABN, address, etc.)  
* Change password (with validation)  
* CSRF protection

Purpose:  
Allows each franchisee to keep their business details current.

---

### **franchisee\_training.php – Training Materials**

* Browse and view all published training files  
* Download or open in browser

Purpose:  
On-demand access to training resources.

---

### **staff\_dash.php – Staff Dashboard**

* KPIs: franchisee count, pending bookings, unread messages  
* Recent network announcements  
* Quick access to all staff features

Purpose:  
Starts staff session and directs attention to pressing matters.

---

### **staff\_announcements.php – Staff Communication**

* Staff can view/send announcements/messages  
* Inbox, sent, and reply features  
* Threaded messaging interface

Purpose:  
Communications platform for staff-to-network and internal messages.

---

### **staff\_booking.php – Manage Appointments (Staff)**

* Create and reschedule bookings  
* View appointment list with status and details

Purpose:  
Appointment management for staff support teams.

---

### **staff\_communication.php – Staff Messaging UI (Alt)**

* Full-featured messaging/announcement system  
* Compose, direct message, and inbox

Purpose:  
Alternative or legacy staff communication system.

---

### **staff\_documents.php – Document Uploads (Staff)**

* Upload, list, edit, and delete franchisee documents  
* Assign documents to specific franchisees

Purpose:  
Document distribution by staff roles.

---

### **staff\_manage\_training.php – Manage Training Files (Staff)**

* Create, list, and delete training resources  
* Display acknowledgement stats

Purpose:  
Staff-led training content management.

---

### **staff\_profile.php – Staff Profile Settings**

* View/edit personal details  
* Change password  
* View account status and history

Purpose:  
Staff can maintain their own account.

---

### **staff\_reports.php – Staff Reports**

* Generate/download CSV reports (bookings, inventory, franchisees)  
* Date range filtering

Purpose:  
Staff-level analytics and compliance reporting.

---

### **staff\_training.php – Staff Training Uploads**

* Upload and list training files for franchisees

Purpose:  
Training resource management for staff.

---

## **Security and Best Practices**

* All sensitive files (e.g. `/db/config.php`) are stored outside the web root.  
* Session cookies are set to `HttpOnly, Secure`, and use `SameSite=Strict` where possible.  
* CSRF tokens are enforced on all forms handling sensitive data.  
* SQL queries use prepared statements to prevent injection.  
* Password hashing (uses PHP’s `password_hash)`.

---

## **Accessing the System**

* ## URL: [http://localhost/index.php](http://localhost/janiking/index.php)

* ## Login: Use the sample credentials above, or create your own users via the admin panel.

 **Conclusion**

## The JaniKing Franchise Portal is a robust, modular solution for franchise administration, communication, inventory, and training. Follow the setup instructions carefully, maintain security best practices, and use the documentation above to extend or customize the platform to your needs.

## **License**

## This project is for educational purposes under the Kent Institute Capstone project. Not intended for commercial deployment without prior approval.

