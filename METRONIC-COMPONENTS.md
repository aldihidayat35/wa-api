# METRONIC 8 COMPONENTS DOCUMENTATION
## WhatsApp Multi-Session Dashboard Implementation

### 📋 KOMPONEN METRONIC 8 YANG DIGUNAKAN

---

## 1. LAYOUT COMPONENTS

### kt_app_root
- **Class**: `app-root`
- **Deskripsi**: Root container untuk seluruh aplikasi
- **Location**: Wrapper terluar `<div class="d-flex flex-column flex-root app-root" id="kt_app_root">`

### kt_app_page  
- **Class**: `app-page`
- **Deskripsi**: Main page container
- **Attributes**: `flex-column`, `flex-column-fluid`

### kt_app_header
- **Class**: `app-header`
- **Deskripsi**: Header/Navbar di bagian atas
- **Features**: 
  - Mobile sidebar toggle
  - Mobile logo
  - Header menu
  - User navbar
- **Container**: `app-container container-fluid`

### kt_app_sidebar
- **Class**: `app-sidebar flex-column`
- **Deskripsi**: Sidebar vertikal (kiri)
- **Attributes Drawer**:
  - `data-kt-drawer="true"`
  - `data-kt-drawer-name="app-sidebar"`
  - `data-kt-drawer-activate="{default: true, lg: false}"`
  - `data-kt-drawer-overlay="true"`
  - `data-kt-drawer-width="225px"`
- **Child Components**:
  - `kt_app_sidebar_logo` - Logo section
  - `kt_app_sidebar_toggle` - Minimize/expand toggle
  - `kt_app_sidebar_menu` - Menu container
  - `kt_app_sidebar_menu_wrapper` - Scrollable wrapper

### kt_app_wrapper
- **Class**: `app-wrapper flex-column flex-row-fluid`
- **Deskripsi**: Wrapper untuk sidebar + main content

### kt_app_main
- **Class**: `app-main flex-column flex-row-fluid`
- **Deskripsi**: Main content area

### kt_app_toolbar
- **Class**: `app-toolbar py-3 py-lg-6`
- **Deskripsi**: Toolbar dengan breadcrumb dan page title
- **Child**: `kt_app_toolbar_container`

### kt_app_content
- **Class**: `app-content flex-column-fluid`
- **Deskripsi**: Content area untuk cards dan widgets
- **Container**: `kt_app_content_container` dengan class `app-container container-xxl`

### kt_app_footer
- **Class**: `app-footer`
- **Deskripsi**: Footer section
- **Container**: `app-container container-fluid`

---

## 2. CARD COMPONENTS

### Card Structure (Metronic 8)
```html
<div class="card card-flush h-xl-100">
    <div class="card-header pt-7">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-dark">Title</span>
            <span class="text-muted mt-1 fw-semibold fs-7">Subtitle</span>
        </h3>
    </div>
    <div class="card-body pt-6">
        <!-- Content -->
    </div>
</div>
```

**Classes Used**:
- `card` - Base card component
- `card-flush` - Remove default padding
- `h-xl-100` - Full height on XL screens
- `card-header` - Header section
- `card-title` - Title wrapper
- `card-label` - Main title text
- `card-body` - Body content

---

## 3. FORM COMPONENTS

### Form Control (Bootstrap 5 / Metronic 8)
```html
<div class="mb-10">
    <label class="form-label fw-semibold text-dark fs-6">Label</label>
    <input type="text" class="form-control form-control-lg form-control-solid" />
    <div class="form-text">Help text</div>
</div>
```

**Classes Used**:
- `form-label` - Label styling
- `form-control` - Input base class
- `form-control-lg` - Large input
- `form-control-solid` - Solid background style
- `form-select` - Select dropdown
- `form-select-solid` - Solid select style
- `form-text` - Help/hint text
- `mb-10` - Margin bottom spacing

### Input Group
```html
<div class="input-group">
    <input type="text" class="form-control" />
    <button class="btn btn-primary">Button</button>
</div>
```

---

## 4. BUTTON COMPONENTS

### Button Classes
- `btn` - Base button
- `btn-primary` - Primary color (blue)
- `btn-success` - Success color (green)
- `btn-danger` - Danger color (red)
- `btn-light-danger` - Light danger variant
- `btn-sm` - Small button
- `w-100` - Full width

**SVG Icon Integration**:
```html
<button class="btn btn-primary">
    <span class="svg-icon svg-icon-2">
        <svg>...</svg>
    </span>
    Button Text
</button>
```

---

## 5. TABS COMPONENT (Bootstrap 5)

### Nav Tabs
```html
<ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab1">Tab 1</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab2">Tab 2</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade show active" id="tab1">Content 1</div>
    <div class="tab-pane fade" id="tab2">Content 2</div>
</div>
```

**Classes Used**:
- `nav nav-tabs nav-line-tabs` - Line style tabs
- `nav-item` - Tab item wrapper
- `nav-link` - Tab link
- `active` - Active tab
- `data-bs-toggle="tab"` - Bootstrap 5 tab toggle
- `tab-content` - Tab content wrapper
- `tab-pane fade show active` - Active pane
- `tab-pane fade` - Inactive pane

---

## 6. ALERT/STATUS COMPONENTS

### Alert Component
```html
<div class="alert alert-danger d-flex align-items-center p-5">
    <span class="status-dot-disconnected me-3"></span>
    <div class="d-flex flex-column">
        <h4 class="mb-1 text-dark">Title</h4>
        <span>Description</span>
    </div>
</div>
```

**Alert Variants**:
- `alert-danger` - Red/error state
- `alert-success` - Green/success state
- `alert-warning` - Yellow/warning state
- `alert-info` - Blue/info state

**Custom Status Dots** (Custom CSS):
- `status-dot-connected` - Green animated dot
- `status-dot-disconnected` - Red static dot

---

## 7. SIDEBAR MENU COMPONENT

### Menu Structure
```html
<div class="menu menu-column menu-rounded menu-sub-indention px-3" 
     id="kt_app_sidebar_menu" 
     data-kt-menu="true">
    
    <!-- Menu Item -->
    <div class="menu-item">
        <a class="menu-link active" href="#">
            <span class="menu-icon">
                <span class="svg-icon svg-icon-2">
                    <svg>...</svg>
                </span>
            </span>
            <span class="menu-title">Menu Title</span>
        </a>
    </div>
    
    <!-- Menu Section Heading -->
    <div class="menu-item pt-5">
        <div class="menu-content">
            <span class="menu-heading fw-bold text-uppercase fs-7">SECTION</span>
        </div>
    </div>
</div>
```

**Classes Used**:
- `menu` - Base menu component
- `menu-column` - Vertical layout
- `menu-rounded` - Rounded corners
- `menu-sub-indention` - Submenu indentation
- `menu-item` - Menu item wrapper
- `menu-link` - Link element
- `menu-icon` - Icon wrapper
- `menu-title` - Title text
- `menu-content` - Content wrapper
- `menu-heading` - Section heading
- `active` - Active menu state

---

## 8. GRID SYSTEM (Bootstrap 5)

### Row and Columns
```html
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    <div class="col-xl-6">
        <!-- Content -->
    </div>
    <div class="col-xl-6">
        <!-- Content -->
    </div>
</div>
```

**Classes Used**:
- `row` - Row container
- `g-5` - Gap 5 between columns
- `g-xl-10` - Gap 10 on XL screens
- `mb-5` - Margin bottom 5
- `mb-xl-10` - Margin bottom 10 on XL
- `col-xl-6` - 6 columns width on XL (50%)
- `col-xl-12` - 12 columns width (100%)

---

## 9. SVG ICONS (Metronic Duotune)

### Icon Wrapper
```html
<span class="svg-icon svg-icon-2">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <!-- SVG paths -->
    </svg>
</span>
```

**Size Classes**:
- `svg-icon-1` - Smallest
- `svg-icon-2` - Small
- `svg-icon-3` - Medium (default)
- `svg-icon-4` - Large
- `svg-icon-5` - Extra large
- `svg-icon-5tx` - 5x extra large

**Color Classes**:
- `svg-icon-muted` - Muted color
- `svg-icon-primary` - Primary color
- `svg-icon-gray-500` - Gray 500

---

## 10. UTILITY CLASSES (Bootstrap 5 + Metronic)

### Display & Flex
- `d-flex` - Display flex
- `d-none` - Display none
- `flex-column` - Column direction
- `flex-row` - Row direction
- `flex-grow-1` - Flex grow
- `flex-shrink-0` - No shrink
- `align-items-center` - Align center
- `align-items-start` - Align start
- `justify-content-between` - Space between
- `justify-content-center` - Center justify

### Spacing (Bootstrap 5)
- `m-*` - Margin (m-1, m-2, m-5, m-10)
- `p-*` - Padding (p-3, p-5, p-10)
- `mb-*` - Margin bottom
- `mt-*` - Margin top
- `me-*` - Margin end (right in LTR)
- `ms-*` - Margin start (left in LTR)
- `pt-*` - Padding top
- `pb-*` - Padding bottom
- `px-*` - Padding horizontal
- `py-*` - Padding vertical

### Text
- `fw-bold` - Font weight bold
- `fw-semibold` - Font weight semibold
- `fs-1` to `fs-7` - Font sizes
- `text-dark` - Dark text
- `text-muted` - Muted text
- `text-center` - Center align
- `text-uppercase` - Uppercase text

### Width & Height
- `w-100` - Width 100%
- `h-100` - Height 100%
- `h-xl-100` - Height 100% on XL
- `w-35px` - Width 35px
- `h-35px` - Height 35px

### Colors
- `bg-light` - Light background
- `bg-light-primary` - Light primary bg
- `bg-light-success` - Light success bg
- `bg-light-danger` - Light danger bg
- `text-hover-primary` - Primary on hover

---

## 11. SCROLL COMPONENT (Metronic KT Scroll)

### Scrollable Container
```html
<div data-kt-scroll="true" 
     data-kt-scroll-activate="true" 
     data-kt-scroll-height="auto" 
     data-kt-scroll-dependencies="#kt_app_sidebar_logo" 
     data-kt-scroll-wrappers="#kt_app_sidebar_menu" 
     data-kt-scroll-offset="5px">
    <!-- Scrollable content -->
</div>
```

**Attributes**:
- `data-kt-scroll="true"` - Enable scroll
- `data-kt-scroll-activate="true"` - Auto activate
- `data-kt-scroll-height="auto"` - Auto height
- `data-kt-scroll-dependencies` - Elements affecting height
- `data-kt-scroll-wrappers` - Wrapper selectors
- `data-kt-scroll-offset` - Offset value

---

## 12. DRAWER COMPONENT (Metronic KT Drawer)

### Drawer Attributes (Sidebar)
```html
<div data-kt-drawer="true"
     data-kt-drawer-name="app-sidebar"
     data-kt-drawer-activate="{default: true, lg: false}"
     data-kt-drawer-overlay="true"
     data-kt-drawer-width="225px"
     data-kt-drawer-direction="start"
     data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">
</div>
```

**Attributes**:
- `data-kt-drawer="true"` - Enable drawer
- `data-kt-drawer-name` - Unique name
- `data-kt-drawer-activate` - Responsive activation
- `data-kt-drawer-overlay` - Show overlay
- `data-kt-drawer-width` - Drawer width
- `data-kt-drawer-direction` - start/end
- `data-kt-drawer-toggle` - Toggle button selector

---

## 13. TOGGLE COMPONENT (Metronic KT Toggle)

### Sidebar Toggle Button
```html
<div data-kt-toggle="true"
     data-kt-toggle-state="active"
     data-kt-toggle-target="body"
     data-kt-toggle-name="app-sidebar-minimize">
</div>
```

**Attributes**:
- `data-kt-toggle="true"` - Enable toggle
- `data-kt-toggle-state` - Initial state
- `data-kt-toggle-target` - Target element
- `data-kt-toggle-name` - Toggle name/class to add

---

## 14. SYMBOL COMPONENT (Metronic)

### Symbol/Avatar
```html
<div class="symbol symbol-50px">
    <span class="symbol-label bg-light-success">
        <span class="fw-bold text-success fs-2x">A</span>
    </span>
</div>

<div class="symbol symbol-35px">
    <img src="user.jpg" alt="user" />
</div>
```

**Classes**:
- `symbol` - Base symbol
- `symbol-50px` - 50px size
- `symbol-35px` - 35px size
- `symbol-circle` - Circle shape
- `symbol-label` - Label wrapper
- `symbol-badge` - Badge indicator

---

## 15. TIMELINE COMPONENT (Metronic)

### Timeline Structure
```html
<div class="timeline">
    <div class="timeline-item">
        <div class="timeline-line w-40px"></div>
        <div class="timeline-icon symbol symbol-circle symbol-40px">
            <div class="symbol-label bg-light">
                <!-- Icon -->
            </div>
        </div>
        <div class="timeline-content">
            <div class="fs-5 fw-semibold mb-2">Event Title</div>
            <div class="text-muted fs-7">Timestamp</div>
        </div>
    </div>
</div>
```

**Classes**:
- `timeline` - Timeline container
- `timeline-item` - Single item
- `timeline-line` - Vertical line
- `timeline-icon` - Icon wrapper
- `timeline-content` - Content area

---

## 16. BREADCRUMB COMPONENT (Bootstrap 5)

### Breadcrumb
```html
<ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7">
    <li class="breadcrumb-item text-muted">
        <a href="#" class="text-muted text-hover-primary">Home</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">Dashboard</li>
</ul>
```

**Classes**:
- `breadcrumb` - Breadcrumb list
- `breadcrumb-separatorless` - No separator
- `breadcrumb-item` - Item wrapper
- `bullet` - Bullet separator

---

## JAVASCRIPT INITIALIZATION

### Required Scripts Order
```html
<!-- 1. Metronic Plugins Bundle -->
<script src="assets/plugins/global/plugins.bundle.js"></script>

<!-- 2. Metronic Scripts Bundle -->
<script src="assets/js/scripts.bundle.js"></script>

<!-- 3. Custom App Scripts -->
<script src="app.js"></script>
```

### Initialize Bootstrap Components
```javascript
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Bootstrap 5 tab handling (automatic with data-bs-toggle="tab")
    // No manual initialization needed
});
```

---

## RESPONSIVE BREAKPOINTS (Bootstrap 5)

- `xs` - Extra small (< 576px)
- `sm` - Small (≥ 576px)
- `md` - Medium (≥ 768px)
- `lg` - Large (≥ 992px)
- `xl` - Extra large (≥ 1200px)
- `xxl` - Extra extra large (≥ 1400px)

**Usage in classes**: `col-lg-6`, `d-lg-none`, `mb-lg-10`, etc.

---

## CUSTOM CSS VARIABLES (Metronic Theme)

```css
:root {
    --bs-primary: #009ef7;
    --bs-success: #50cd89;
    --bs-danger: #f1416c;
    --bs-warning: #ffc700;
    --bs-info: #7239ea;
    
    /* And many more... */
}
```

---

## SUMMARY - MAIN COMPONENTS USED

1. **kt_app_root** - App root container
2. **kt_app_page** - Page wrapper
3. **kt_app_header** - Header/navbar
4. **kt_app_sidebar** - Vertical sidebar
5. **kt_app_sidebar_menu** - Sidebar menu
6. **kt_app_wrapper** - Main wrapper
7. **kt_app_main** - Main content area
8. **kt_app_toolbar** - Breadcrumb toolbar
9. **kt_app_content** - Content container
10. **kt_app_footer** - Footer section

**Bootstrap 5 Components**:
- Cards (`card`, `card-header`, `card-body`)
- Forms (`form-control`, `form-select`, `form-label`)
- Buttons (`btn`, `btn-primary`, etc.)
- Grid (`row`, `col-*`)
- Tabs (`nav-tabs`, `tab-content`, `tab-pane`)
- Alerts (`alert`, `alert-*`)

**Metronic Custom Components**:
- Sidebar with drawer
- Scrollable containers
- Toggle components
- Symbol/Avatar
- Timeline
- SVG Icons (Duotune)

---

Powered by **Metronic 8** Theme + **Bootstrap 5**
