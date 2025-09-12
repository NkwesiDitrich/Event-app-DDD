<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
    <title>Event Management Author</title>

    <link rel="icon" type="image/x-icon" href="{{asset('/favicon.ico')}}" />
    <link href="{{asset('backend/css/bootstrap.css')}}" rel="stylesheet" />
    <link href="{{asset('backend/css/animate.min.css')}}" rel="stylesheet" />
    <link href="{{asset('backend/css/fontawesome.css')}}" rel="stylesheet" />
    <link href="{{asset('backend/css/style.css')}}" rel="stylesheet" />
    <link href="{{asset('backend/css/toastify.min.css')}}" rel="stylesheet" />

    <link href="{{asset('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css')}}" rel="stylesheet" />

    <link href="{{asset('backend/css/jquery.dataTables.min.css')}}" rel="stylesheet" />
    <script src="{{asset('backend/js/jquery-3.7.0.min.js')}}"></script>
    <script src="{{asset('backend/js/jquery.dataTables.min.js')}}"></script>

    <script src="{{asset('backend/js/toastify-js.js')}}"></script>
    <script src="{{asset('backend/js/axios.min.js')}}"></script>
    <script src="{{asset('backend/js/config.js')}}"></script>
    <script src="{{asset('backend/js/bootstrap.bundle.js')}}"></script>

    <style>
        /* Enhanced dropdown styles for User Management */
        .side-bar-dropdown {
            position: relative;
        }
        
        .side-bar-dropdown-content {
            display: none;
            background-color: #f8f9fa;
            border-left: 3px solid #007bff;
            margin-left: 20px;
            padding-left: 10px;
            margin-top: 5px;
        }
        
        .side-bar-dropdown.active .side-bar-dropdown-content {
            display: block;
        }
        
        .side-bar-dropdown-item {
            display: block;
            padding: 8px 15px;
            color: #6c757d;
            text-decoration: none;
            font-size: 14px;
            border-radius: 4px;
            margin: 2px 0;
            transition: all 0.3s ease;
        }
        
        .side-bar-dropdown-item:hover {
            background-color: #e9ecef;
            color: #007bff;
            text-decoration: none;
        }
        
        .side-bar-dropdown-item.active {
            background-color: #007bff;
            color: white;
        }
        
        .side-bar-item.dropdown-toggle::after {
            content: "▼";
            float: right;
            font-size: 12px;
            transition: transform 0.3s ease;
        }
        
        .side-bar-item.dropdown-toggle.active::after {
            transform: rotate(180deg);
        }
        
        .side-bar-item {
            cursor: pointer;
        }
    </style>
</head>

<body>

<div id="loader" class="LoadingOverlay d-none">
    <div class="Line-Progress">
        <div class="indeterminate"></div>
    </div>
</div>

<nav class="navbar fixed-top px-0 shadow-sm bg-white">
    <div class="container-fluid">

        <a class="navbar-brand" href="#">
            <span class="icon-nav m-0 h5" onclick="MenuBarClickHandler()">
                <img class="nav-logo-sm mx-2"  src="{{asset('backend/images/menu.svg')}}" alt="logo"/>
            </span>
            <img class="nav-logo  mx-2"  src="{{asset('backend/images/logo.png')}}" alt="logo"/>
        </a>

        <div class="float-right h-auto d-flex">
            <div class="user-dropdown">
                <img class="icon-nav-img" src="{{asset('backend/images/user.webp')}}" alt=""/>
                <div class="user-dropdown-content ">
                    <div class="mt-4 text-center">
                        <img class="icon-nav-img" src="{{asset('backend/images/user.webp')}}" alt=""/>
                        <h6>{{ auth()->user()->firstName }}</h6>
                        <hr class="user-dropdown-divider  p-0"/>
                    </div>
                    <a href="{{url('/userProfile')}}" class="side-bar-item">
                        <span class="side-bar-item-caption">Profile</span>
                    </a>
                    <a onclick="return confirm('Are You Sure??')" href="{{url("/logout")}}" class="side-bar-item">
                        <span class="side-bar-item-caption">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<div id="sideNavRef" class="side-nav-open">

    <a href="{{url("/dashboard")}}" class="side-bar-item">
        <i class="bi bi-graph-up"></i>
        <span class="side-bar-item-caption">Dashboard</span>
    </a>
    
    <a href="{{url("/categoryPage")}}" class="side-bar-item">
        <i class="bi bi-list-nested"></i>
        <span class="side-bar-item-caption">Category</span>
    </a>
    
    <a href="{{url("/eventPage")}}" class="side-bar-item">
        <i class="bi bi-calendar2-event"></i>
        <span class="side-bar-item-caption">Event</span>
    </a>
    
    <!-- Enhanced User Management with Dropdown -->
    <div class="side-bar-dropdown" id="userManagementDropdown">
        <a href="javascript:void(0)" class="side-bar-item dropdown-toggle" onclick="toggleUserManagementDropdown()">
            <i class="bi bi-people"></i>
            <span class="side-bar-item-caption">User Management</span>
        </a>
        <div class="side-bar-dropdown-content">
            <a href="{{url('/user-management/dashboard')}}" class="side-bar-dropdown-item">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="{{url('/user-management/event-participants')}}" class="side-bar-dropdown-item">
                <i class="bi bi-person-check"></i> Event Participants
            </a>
            <a href="{{url('/user-management/events-created')}}" class="side-bar-dropdown-item">
                <i class="bi bi-calendar-plus"></i> Events Created
            </a>
        </div>
    </div>
    
    <a href="{{ url("/reportPage") }}" class="side-bar-item">
        <i class="bi bi-file-earmark-bar-graph"></i>
        <span class="side-bar-item-caption">Report</span>
    </a>

</div>

<div id="contentRef" class="content">
    @yield('content')
</div>

<script>
    function MenuBarClickHandler() {
        let sideNav = document.getElementById('sideNavRef');
        let content = document.getElementById('contentRef');
        if (sideNav.classList.contains("side-nav-open")) {
            sideNav.classList.add("side-nav-close");
            sideNav.classList.remove("side-nav-open");
            content.classList.add("content-expand");
            content.classList.remove("content");
        } else {
            sideNav.classList.remove("side-nav-close");
            sideNav.classList.add("side-nav-open");
            content.classList.remove("content-expand");
            content.classList.add("content");
        }
    }

    function toggleUserManagementDropdown() {
        const dropdown = document.getElementById('userManagementDropdown');
        const toggleButton = dropdown.querySelector('.dropdown-toggle');
        
        if (dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
            toggleButton.classList.remove('active');
        } else {
            dropdown.classList.add('active');
            toggleButton.classList.add('active');
        }
    }

    // Auto-expand User Management dropdown if on a user management page
    document.addEventListener('DOMContentLoaded', function() {
        const currentPath = window.location.pathname;
        if (currentPath.includes('/user-management/')) {
            const dropdown = document.getElementById('userManagementDropdown');
            const toggleButton = dropdown.querySelector('.dropdown-toggle');
            dropdown.classList.add('active');
            toggleButton.classList.add('active');
            
            // Highlight active dropdown item
            const dropdownItems = dropdown.querySelectorAll('.side-bar-dropdown-item');
            dropdownItems.forEach(item => {
                if (item.getAttribute('href') === currentPath) {
                    item.classList.add('active');
                }
            });
        }
    });
</script>

</body>
</html>
