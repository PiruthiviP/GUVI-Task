$(document).ready(function () {

  // If already logged in, go to profile
  if (localStorage.getItem('userToken')) {
    window.location.href = 'profile.html';
  }

  $('#loginForm').on('submit', function (e) {
    e.preventDefault();
    hideMessages();

    var email    = $.trim($('#email').val());
    var password = $('#password').val();

    // Client-side validation 
    if (!email || !password) {
      showError('Please enter your email and password.');
      return;
    }

    if (!isValidEmail(email)) {
      showError('Please enter a valid email address.');
      return;
    }

    // Submit via AJAX
    setLoading(true);

    $.ajax({
      url: 'php/login.php',
      type: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ email: email, password: password }),
      dataType: 'json',
      success: function (res) {
        setLoading(false);
        if (res.success) {
          // Store session info in localStorage (no PHP session used)
          localStorage.setItem('userToken',    res.token);
          localStorage.setItem('userId',       res.user_id);
          localStorage.setItem('userEmail',    res.email);
          localStorage.setItem('userUsername', res.username);

          showSuccess('Login successful! Redirecting...');
          setTimeout(function () {
            window.location.href = 'profile.html';
          }, 800);
        } else {
          showError(res.message || 'Invalid credentials.');
        }
      },
      error: function (xhr) {
        setLoading(false);
        var res = safeParseJSON(xhr.responseText);
        showError(res.message || 'Server error. Please try again.');
      }
    });
  });

  function showError(msg) {
    $('#msgErrorText').text(msg);
    $('#msgError').addClass('show');
  }

  function showSuccess(msg) {
    $('#msgSuccessText').text(msg);
    $('#msgSuccess').addClass('show');
  }

  function hideMessages() {
    $('#msgError').removeClass('show');
    $('#msgSuccess').removeClass('show');
  }

  function setLoading(state) {
    var btn = $('#loginBtn');
    if (state) {
      btn.prop('disabled', true).html('<span class="spinner"></span>Signing in...');
    } else {
      btn.prop('disabled', false).text('Sign in');
    }
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
  }

  function safeParseJSON(str) {
    try { return JSON.parse(str); } catch (e) { return {}; }
  }

});