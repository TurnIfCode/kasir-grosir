# TODO: Make Laravel Cashier App Fully Responsive for Mobile and Tablet

## Plan Overview
- Enhance responsiveness for iPad (768-1024px), Android smartphones, and iPhones.
- Improve sidebar handling for tablets and mobile with overlay/slide-in.
- Add touch-friendly design: larger buttons, fonts, padding.
- Ensure all views (dashboard, forms, tables, modals) are responsive.
- Make DataTables responsive.
- Add proper breakpoints for tablets.

## Steps
- [x] Update resources/views/layout/header.blade.php: Add tablet breakpoints (768-1024px), improve sidebar toggle with overlay for mobile/tablet, increase font sizes and button sizes for touch.
- [x] Update resources/views/dashboard.blade.php: Add col-sm-* classes for better small screen layout, ensure cards stack properly.
- [x] Update resources/views/barang/index.blade.php: Make DataTable responsive, adjust modal size for mobile.
- [x] Update resources/views/penjualan/create.blade.php: Add col-sm-* classes for form fields.
- [ ] Review and update other view files (e.g., other forms, tables) for responsive classes.
- [ ] Test responsiveness using browser dev tools or local server.
- [ ] Ensure viewport meta tag is present (already in header.blade.php).
