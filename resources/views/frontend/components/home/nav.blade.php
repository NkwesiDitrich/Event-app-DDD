<!-- Begin Nav
================================================== -->
<nav class="navbar navbar-toggleable-md navbar-light bg-white fixed-top mediumnavigation">
    <button class="navbar-toggler navbar-toggler-right" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
    </button>
    <div class="container">
        <!-- Begin Logo -->
        <a class="navbar-brand" href="{{ url('/') }}">
        <img src="{{ asset('frontend/img/logo.png') }}" alt="logo">
        </a>
        <!-- End Logo -->
        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <!-- Begin Menu -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active">
                <a class="nav-link" href="{{ url('/') }}">Stories <span class="sr-only">(current)</span></a>
                </li>
                {{-- <li class="nav-item">
                <a class="nav-link" href="post.html">Post</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="author.html">Author</a>
                </li> --}}
                <li class="nav-item">
                    <a class="nav-link" href="#" onclick="showPasswordModal()">Admin</a>
                </li> 
            </ul>
            <!-- End Menu -->
            <!-- Begin Search -->
            <form class="form-inline my-2 my-lg-0">
                <input class="form-control mr-sm-2" type="text" placeholder="Search">
                <span class="search-icon"><svg class="svgIcon-use" width="25" height="25" viewbox="0 0 25 25"><path d="M20.067 18.933l-4.157-4.157a6 6 0 1 0-.884.884l4.157 4.157a.624.624 0 1 0 .884-.884zM6.5 11c0-2.62 2.13-4.75 4.75-4.75S16 8.38 16 11s-2.13 4.75-4.75 4.75S6.5 13.62 6.5 11z"></path></svg></span>
            </form>
            <!-- End Search -->
        </div>
    </div>
    </nav>
    <!-- End Nav
    ================================================== -->

<!-- Password Modal -->
<div id="passwordModal" class="modal" style="display: none; position: fixed; z-index: 1050; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5);">
    <div class="modal-dialog" style="position: relative; top: 50%; transform: translateY(-50%); margin: 0 auto; max-width: 400px;">
        <div class="modal-content" style="background-color: white; border-radius: 5px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <div class="modal-header" style="border-bottom: 1px solid #dee2e6; padding-bottom: 15px; margin-bottom: 15px;">
                <h5 class="modal-title" style="margin: 0; font-weight: bold;">Admin Access</h5>
                <button type="button" class="close" onclick="hidePasswordModal()" style="background: none; border: none; font-size: 24px; float: right; cursor: pointer;">&times;</button>
            </div>
            <div class="modal-body">
                <p style="margin-bottom: 15px;">Please enter the admin password to continue:</p>
                <input type="password" id="adminPassword" class="form-control" placeholder="Enter password" style="width: 100%; padding: 8px 12px; border: 1px solid #ced4da; border-radius: 4px;">
                <div id="errorMessage" style="color: red; margin-top: 10px; display: none;">Sorry password incorrect, retry please</div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 15px; text-align: right;">
                <button type="button" class="btn btn-secondary" onclick="hidePasswordModal()" style="margin-right: 10px; padding: 6px 12px; border: 1px solid #6c757d; background-color: #6c757d; color: white; border-radius: 4px; cursor: pointer;">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="checkPassword()" style="padding: 6px 12px; border: 1px solid #007bff; background-color: #007bff; color: white; border-radius: 4px; cursor: pointer;">Submit</button>
            </div>
        </div>
    </div>
</div>

<script>
function showPasswordModal() {
    document.getElementById('passwordModal').style.display = 'block';
    document.getElementById('adminPassword').focus();
}

function hidePasswordModal() {
    document.getElementById('passwordModal').style.display = 'none';
    document.getElementById('adminPassword').value = '';
    document.getElementById('errorMessage').style.display = 'none';
}

function checkPassword() {
    const password = document.getElementById('adminPassword').value;
    const correctPassword = 'MavouConsulting123';
    
    if (password === correctPassword) {
        // Redirect to login page
        window.location.href = '{{ route("login") }}';
    } else {
        // Show error message
        document.getElementById('errorMessage').style.display = 'block';
        document.getElementById('adminPassword').value = '';
        document.getElementById('adminPassword').focus();
    }
}

// Allow Enter key to submit password
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('adminPassword').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            checkPassword();
        }
    });
});

// Close modal when clicking outside
document.getElementById('passwordModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hidePasswordModal();
    }
});
</script>

