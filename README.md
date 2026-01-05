# DunzoQuick - E-Commerce Delivery System

DunzoQuick is a complete web application designed for a delivery or e-commerce business. It features a customer-facing storefront for placing orders and a powerful admin panel for managing the business operations.
### User Dashboard
<img width="1892" height="998" alt="User" src="https://github.com/user-attachments/assets/c6694fe6-af20-4d59-8ffd-40f44e065e10" />


The **User Dashboard** provides a clean and intuitive interface for customers to manage their account. Key features include:
- **Order Tracking**: View real-time status updates for active orders.
- **Purchase History**: Access a complete log of past transactions.
- **Profile Management**: Update personal details and shipping addresses securely.

### Admin Dashboard
<img width="1891" height="1000" alt="adminDb" src="https://github.com/user-attachments/assets/cb35b890-a4e4-4bc6-91cb-ced405f1fcb8" />


The **Admin Dashboard** serves as the central command center for store operations. It offers:
- **At-a-Glance Statistics**: Instant visibility into daily revenue, new orders, and total user counts.
- **Recent Activity**: A quick view of the latest orders requiring attention.
- **Navigation**: Easy access to product management, user lists, and report generation tools.

## Features

### 🖥️ Admin Side
The control panel for administrators to manage the platform.

- **Dashboard Statistics**:
  - Real-time overview of **Total Revenue**, **Monthly Revenue**, and **Daily Revenue**.
  - Counters for **New Orders**, **Total Users**, and **Total Products**.
- **Order Management**:
  - View recent orders with details (Customer Name, Amount, Date).
  - Update order statuses (Pending, Delivered, Cancelled) with color-coded badges.
  - Generate and print PDF invoices.
- **Customer Insights**:
  - View registered users.
  - "Top Customers" panel highlighting high-value clients.
- **Inventory Control**: Manage products and stock levels.

### 🛒 User Side
The frontend interface for customers to browse and purchase items.

- **Account Management**: Secure Registration and Login.
- **Shopping Experience**:
  - Browse products by category.
  - Add items to the shopping cart.
- **Checkout & Payments**:
  - Secure online payments integrated via **Razorpay**.
  - Order summary review before purchase.
- **Order Tracking**: View order history and current delivery status.

### 🛠 Technical Highlights
- **Backend**: PHP with MySQLi for database interactions.
- **Frontend**: HTML5, CSS3 (Responsive Grid/Flexbox), JavaScript.
- **Styling**: Custom CSS with variables for easy theming, using 'Poppins' font and FontAwesome icons.
- **PDF Generation**: Uses `dompdf` and `FPDF` for generating reports and invoices.
- **Payments**: Integrated `razorpay/razorpay` library for handling transactions.
