# School Management System

A modern, multi-role PHP & MySQL school management system for students, parents, teachers, admins, finance managers, admission managers, and headmaster/headmistress. Fully customizable layout and colors via admin settings.

## Features
- Student, Parent, Teacher, Admin, Finance, Admission, Headmaster/Headmistress dashboards
- Secure login for all roles (admin must create special roles)
- Student registration, assignments, results, report cards, promotion/repeat
- Parent access to child grades, assignments, fees, report cards
- Teacher assignment upload and result upload for assigned classes/subjects
- Admin management of users, classes, subjects, school info, layout/colors
- Finance manager for school fees, invoices, receipts
- Admission manager for student applications
- Profile picture upload for all users
- PDF generation for report cards and receipts
- Customizable colors and fonts for all pages

## Setup Instructions
1. **Clone the repository**
   ```bash
   git clone <repo-url>
   cd One-love/school-management
   ```
2. **Create the database**
   - Import `schema.sql` into your MySQL server.
   - Example:
     ```bash
     mysql -u <user> -p <database> < schema.sql
     ```
3. **Configure database connection**
   - Edit `db.php` with your MySQL credentials.

4. **Set up file permissions**
   - Ensure the web server can write to the folders for profile pictures and PDF receipts/report cards.

5. **Access the app**
   - Open in your browser: `http://localhost/One-love/school-management/`
   - Register the first admin via `register_admin.php`.
   - Admin creates headmaster/mistress, finance manager, and admission manager accounts.
   - All other users (students, parents, teachers) are managed by admin or via admission process.

## Customization
- Go to `admin_settings.php` to set school name, logo, headmaster, grade point, and customize colors/layout for all pages.

## Technologies
- PHP 7+
- MySQL
- mPDF (for PDF generation)
- HTML/CSS (customizable via settings)

## File Structure
- `school-management/`
  - All PHP pages, schema, and assets
- `style.css`: Main stylesheet (customizable)
- `schema.sql`: Database schema

## Security
- All sensitive actions require login and proper role
- Special roles (admin, headmaster/mistress, finance manager, admission manager) must be created by admin
- No public signup for special roles

## Support
For issues or feature requests, open an issue in this repository.