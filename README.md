<<<<<<< HEAD
# DunzoQuick
DunzoQuick is a hyperlocal quick commerce web application inspired by Dunzo, enabling fast delivery of groceries, pharmacy, and daily essentials with real-time location, category-based products, cart, and order management.
=======
# DUNZO

## Project Overview
**DUNZO** is a comprehensive PHP-based web application designed to handle secure payment processing, dynamic document generation, and user authentication. This project demonstrates a modular architecture suitable for e-commerce platforms or enterprise resource planning (ERP) modules.

## Key Features

### 💳 Payment Processing
Integrated with **Razorpay** (v2.9.2) to handle secure online transactions. The system supports payment capture, verification, and logging, making it ready for e-commerce workflows.

### 📄 Advanced PDF Generation
The application employs a dual-strategy for PDF creation to handle various complexity levels:
*   **Dompdf (v2.0.8):** Converts HTML/CSS templates directly to PDF, ideal for invoices and receipts.
*   **FPDF:** Used for programmatic PDF generation requiring precise layout control.

### 🔐 User Authentication
Includes a secure user management system with:
*   Login and Registration flows.
*   Password Reset functionality (`reset_password.php`).
*   Secure token handling.

### 🛠 Development Utilities
*   **Local Email Logging:** Outgoing emails (like password resets) are captured in `email_logs/` during development to prevent spamming real addresses.

## Technology Stack

*   **Language:** PHP
*   **Dependency Manager:** Composer
*   **Libraries:**
    *   `razorpay/razorpay`: Payment Gateway.
    *   `dompdf/dompdf`: HTML to PDF rendering.
    *   `rmccue/requests`: HTTP Client.
    *   `masterminds/html5`: HTML5 parsing.
    *   `phenx/php-font-lib`: Font management.

## Directory Structure

| Directory | Description |
| :--- | :--- |
| `doc/` | Documentation for the FPDF library (Output, AddFont, Image handling). |
| `email_logs/` | Stores local logs of outgoing emails for debugging purposes. |
| `vendor/` | Third-party dependencies managed by Composer. |
| `src/` | (Implied) Application source code. |

## Installation

### Prerequisites
*   PHP 7.4 or higher
*   Composer
*   Web Server (Apache/Nginx or XAMPP/WAMP)

### Setup Steps

1.  **Clone the Repository**
    Navigate to your web server's root directory (e.g., `c:\xampp\htdocs`).
    ```bash
    cd c:\xampp\htdocs
    git clone <repository-url> DUNZO
    ```

2.  **Install Dependencies**
    Install the required PHP packages using Composer:
    ```bash
    cd DUNZO
    composer install
    ```

3.  **Configuration**
    *   Configure your database settings in the application config file (if applicable).
    *   Set up Razorpay API keys in your environment variables or config.

## Usage

*   **Generating Reports:** Navigate to the reporting module to export data as PDF.
*   **Testing Emails:** Check the `email_logs/` folder to view the content of emails sent by the system while in development mode.
*   **API Documentation:** Refer to `doc/` for specific implementation details regarding the FPDF library.

## Customer Interface

The customer-facing landing page provides a seamless shopping experience:

![Customer Landing Page](image-1.png)

*   **Hero Section:** Highlights the core value proposition ("Delivering in 10 Minutes") with key service statistics.
*   **Quick Actions:** Prominent buttons for "Start Shopping", "My Orders", and "My Profile" allow users to navigate quickly to essential features.
*   **Navigation Bar:** Includes search functionality and direct links to Cart, Orders, Products, Wishlist, and Profile management.

## Admin Dashboard

The application features a comprehensive Admin Dashboard designed for efficient store management:

![Admin Dashboard Interface](image.png)

*   **Real-time Analytics:** Visual cards displaying Total Revenue, Monthly/Daily Revenue, New Orders, Customer counts, and Total Products.
*   **Order Tracking:** A "Recent Orders" section allows admins to monitor transactions, viewing details like Order ID, Customer Name, Amount, and Status (e.g., Pending, Confirmed).
*   **Module Navigation:** A dedicated sidebar provides quick access to key management areas: Orders, Products, Coupons, Customers, Analytics, and Settings.
>>>>>>> 99c5cce (`Updated README.md and index.css files with minor changes`)
